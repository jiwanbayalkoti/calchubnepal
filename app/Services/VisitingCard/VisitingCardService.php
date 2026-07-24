<?php

namespace App\Services\VisitingCard;

use App\DTOs\VisitingCard\VisitingCardData;
use App\Enums\VisitingCard\CardTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VisitingCardService
{
    public function __construct(protected VisitingCardRenderer $renderer)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function preview(array $data, ?UploadedFile $logo = null): array
    {
        $card = $this->makeCard($data, $logo);
        $png = $this->renderer->renderPng($card);

        return [
            'image' => 'data:image/png;base64,'.base64_encode($png),
            'template' => $card->template->value,
            'width' => VisitingCardRenderer::WIDTH,
            'height' => VisitingCardRenderer::HEIGHT,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function downloadPng(array $data, ?UploadedFile $logo = null): string
    {
        return $this->renderer->renderPng($this->makeCard($data, $logo));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function downloadPdf(array $data, ?UploadedFile $logo = null): string
    {
        $png = $this->downloadPng($data, $logo);

        $html = view('visiting-card.pdf', [
            'imageData' => 'data:image/png;base64,'.base64_encode($png),
        ])->render();

        // 90×50 mm business card in points (1 mm ≈ 2.83465 pt)
        return Pdf::loadHTML($html)->setPaper([0, 0, 255.12, 141.73])->output();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function makeCard(array $data, ?UploadedFile $logo = null): VisitingCardData
    {
        if ($logo instanceof UploadedFile) {
            $token = (string) Str::uuid();
            $ext = strtolower($logo->getClientOriginalExtension() ?: 'png');
            if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
                $ext = 'png';
            }
            Storage::disk('local')->putFileAs('visiting-card-logos', $logo, $token.'.'.$ext);
            $data['logo_path'] = Storage::disk('local')->path('visiting-card-logos/'.$token.'.'.$ext);
        } elseif (! empty($data['logo_token']) && is_string($data['logo_token'])) {
            $resolved = $this->resolveLogoToken($data['logo_token']);
            if ($resolved) {
                $data['logo_path'] = $resolved;
            }
        }

        return VisitingCardData::fromArray($data);
    }

    public function storeLogo(UploadedFile $logo): string
    {
        $token = (string) Str::uuid();
        $ext = strtolower($logo->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            $ext = 'png';
        }
        Storage::disk('local')->putFileAs('visiting-card-logos', $logo, $token.'.'.$ext);

        return $token;
    }

    public function resolveLogoToken(string $token): ?string
    {
        if (! preg_match('/^[a-f0-9\-]{36}$/i', $token)) {
            return null;
        }
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
            $relative = "visiting-card-logos/{$token}.{$ext}";
            if (Storage::disk('local')->exists($relative)) {
                return Storage::disk('local')->path($relative);
            }
        }

        return null;
    }

    /**
     * @return list<array{value: string, label: string, category: string, colors: array{primary: string, secondary: string, text: string, background: string}}>
     */
    public function templates(): array
    {
        return array_map(
            static fn (CardTemplate $t) => $t->toOption(),
            CardTemplate::all()
        );
    }
}

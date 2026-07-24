<?php

namespace App\Services\Qr;

use App\Models\QrBrandTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BrandTemplateService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, ?UploadedFile $logo = null): QrBrandTemplate
    {
        $style = [
            'size' => (int) ($data['size'] ?? 256),
            'foreground' => (string) ($data['foreground'] ?? '#0B6E4F'),
            'background' => (string) ($data['background'] ?? '#FFFFFF'),
            'margin' => (int) ($data['margin'] ?? 10),
            'error_correction' => (string) ($data['error_correction'] ?? 'M'),
            'module_style' => (string) ($data['module_style'] ?? 'square'),
            'eye_style' => (string) ($data['eye_style'] ?? 'square'),
            'frame_style' => (string) ($data['frame_style'] ?? 'none'),
            'frame_label' => (string) ($data['frame_label'] ?? ''),
            'logo_size' => (int) ($data['logo_size'] ?? 64),
        ];

        $logoPath = null;
        if ($logo) {
            $logoPath = $logo->store('qr-brand-logos', 'public');
        }

        if (! empty($data['is_default'])) {
            QrBrandTemplate::query()->where('user_id', $user->id)->update(['is_default' => false]);
        }

        return QrBrandTemplate::query()->create([
            'user_id' => $user->id,
            'workspace_id' => $data['workspace_id'] ?? null,
            'name' => (string) $data['name'],
            'style_json' => $style,
            'logo_path' => $logoPath,
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(QrBrandTemplate $template, User $user, array $data, ?UploadedFile $logo = null): QrBrandTemplate
    {
        if ((int) $template->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Not allowed.');
        }

        $style = array_merge($template->style_json ?? [], array_filter([
            'size' => isset($data['size']) ? (int) $data['size'] : null,
            'foreground' => $data['foreground'] ?? null,
            'background' => $data['background'] ?? null,
            'margin' => isset($data['margin']) ? (int) $data['margin'] : null,
            'error_correction' => $data['error_correction'] ?? null,
            'module_style' => $data['module_style'] ?? null,
            'eye_style' => $data['eye_style'] ?? null,
            'frame_style' => $data['frame_style'] ?? null,
            'frame_label' => $data['frame_label'] ?? null,
            'logo_size' => isset($data['logo_size']) ? (int) $data['logo_size'] : null,
        ], static fn ($v) => $v !== null));

        $updates = [
            'name' => $data['name'] ?? $template->name,
            'style_json' => $style,
            'workspace_id' => $data['workspace_id'] ?? $template->workspace_id,
        ];

        if ($logo) {
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }
            $updates['logo_path'] = $logo->store('qr-brand-logos', 'public');
        }

        if (! empty($data['is_default'])) {
            QrBrandTemplate::query()->where('user_id', $user->id)->update(['is_default' => false]);
            $updates['is_default'] = true;
        }

        $template->update($updates);

        return $template->refresh();
    }

    public function delete(QrBrandTemplate $template, User $user): void
    {
        if ((int) $template->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Not allowed.');
        }
        if ($template->logo_path) {
            Storage::disk('public')->delete($template->logo_path);
        }
        $template->delete();
    }

    /**
     * Merge template styles into generate payload.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function applyToPayload(array $data, ?QrBrandTemplate $template): array
    {
        if (! $template) {
            return $data;
        }
        foreach ($template->style_json ?? [] as $key => $value) {
            if (! array_key_exists($key, $data) || blank($data[$key])) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

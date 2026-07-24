<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\VisitingCardRequest;
use App\Services\Seo\SeoService;
use App\Services\VisitingCard\VisitingCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class VisitingCardController extends Controller
{
    public function __construct(
        protected VisitingCardService $cards,
        protected SeoService $seo,
    ) {
    }

    public function show(): View
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Visiting Card Designer', 'url' => route('visiting-card-designer')],
        ];

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Free Visiting Card Designer — Templates, Logo & QR | CalchubNepal',
            'description' => 'Design print-ready visiting cards online. 32 templates in Professional, Minimal, Bold, Premium, Creative and Corporate styles — add logo and QR, download PNG or PDF.',
            'keywords' => 'visiting card designer, business card maker, Nepal visiting card, free business card template, creative visiting card',
            'canonical' => route('visiting-card-designer'),
        ]);

        $webAppSchema = $this->seo->calculatorSchema(
            'Visiting Card Designer',
            'Free online visiting card designer with 32 templates across 6 style categories, logo upload, optional QR code, and PNG/PDF download.',
            route('visiting-card-designer'),
        );

        $faqSchema = $this->seo->faqSchema([
            [
                'question' => 'What size is the visiting card?',
                'answer' => 'Cards are rendered at 1050×600 pixels (about 3.5×2 inches at 300 DPI), suitable for standard business card printing.',
            ],
            [
                'question' => 'Can I add a QR code on the card?',
                'answer' => 'Yes. Optionally embed a QR that opens your website, vCard contact, email, or phone number.',
            ],
            [
                'question' => 'Which formats can I download?',
                'answer' => 'Download a high-resolution PNG for print shops or a PDF sized to 90×50 mm.',
            ],
        ]);

        return view('visiting-card.show', [
            'meta' => $meta,
            'breadcrumbs' => $breadcrumbs,
            'breadcrumbSchema' => $this->seo->breadcrumbSchema($breadcrumbs),
            'webAppSchema' => $webAppSchema,
            'faqSchema' => $faqSchema,
            'templates' => $this->cards->templates(),
            'categories' => \App\Enums\VisitingCard\CardTemplate::categories(),
        ]);
    }

    public function preview(VisitingCardRequest $request): JsonResponse
    {
        try {
            $data = $this->cards->preview($request->cardPayload(), $request->file('logo'));

            return response()->json(['success' => true, 'data' => $data]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Unable to render visiting card.'], 500);
        }
    }

    public function download(VisitingCardRequest $request): SymfonyResponse
    {
        $format = (string) $request->input('format', 'png');

        try {
            if ($format === 'pdf') {
                $binary = $this->cards->downloadPdf($request->cardPayload(), $request->file('logo'));

                return response($binary, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="visiting-card.pdf"',
                ]);
            }

            $binary = $this->cards->downloadPng($request->cardPayload(), $request->file('logo'));

            return response($binary, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="visiting-card.png"',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Unable to download visiting card.'], 500);
        }
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'image', 'max:2048', 'mimes:png,jpg,jpeg,webp,gif'],
        ]);

        try {
            $token = $this->cards->storeLogo($request->file('logo'));

            return response()->json(['success' => true, 'data' => ['logo_token' => $token]]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Unable to upload logo.'], 500);
        }
    }
}

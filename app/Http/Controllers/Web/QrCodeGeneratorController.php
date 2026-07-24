<?php

namespace App\Http\Controllers\Web;

use App\Enums\Qr\QrErrorCorrection;
use App\Enums\Qr\QrEyeStyle;
use App\Enums\Qr\QrFrameStyle;
use App\Enums\Qr\QrModuleStyle;
use App\Enums\Qr\QrOutputFormat;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\DynamicQrStoreRequest;
use App\Http\Requests\Web\QrGenerateRequest;
use App\Models\BlogPost;
use App\Models\QrCode;
use App\Services\Qr\DynamicQrService;
use App\Services\Qr\QrGeneratorService;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class QrCodeGeneratorController extends Controller
{
    public function __construct(
        protected QrGeneratorService $qr,
        protected DynamicQrService $dynamic,
        protected SeoService $seo,
    ) {
    }

    public function show(): View
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'QR Code Generator', 'url' => route('qr-code-generator')],
        ];

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Free QR Code Generator — WiFi, vCard, Maps, Logo & More | CalchubNepal',
            'description' => 'Generate free QR codes for URL, WiFi, Google Maps, vCard, events, social profiles and more. Add logo, frames, rounded styles, then download PNG, SVG, JPG, WebP or PDF.',
            'keywords' => 'QR code generator, WiFi QR, vCard QR, Google Maps QR, logo QR, Nepal QR generator',
            'canonical' => route('qr-code-generator'),
        ]);

        $webAppSchema = $this->seo->calculatorSchema(
            'QR Code Generator',
            'Advanced free QR code generator with maps, images, payments (eSewa, Khalti, UPI, Nepal QR), Telegram, WiFi, app downloads, meetings and more.',
            route('qr-code-generator'),
        );

        $faqSchema = $this->seo->faqSchema([
            [
                'question' => 'What QR types are supported?',
                'answer' => 'Website, text, email, phone, SMS, WhatsApp, Telegram, Viber, Messenger, WiFi, maps (Google/Apple/Waze), geo, vCard, MeCard, event, calendar, social, bank details, eSewa, Khalti, UPI, Nepal QR, crypto, app download, PDF, image, meeting, music, review, coupon and multi-URL.',
            ],
            [
                'question' => 'Can I add a logo and custom styles?',
                'answer' => 'Yes. Upload a logo, choose rounded/dot modules, eye styles and frames, then download PNG, SVG, JPG, WebP or PDF.',
            ],
            [
                'question' => 'Is history saved?',
                'answer' => 'Recent QR codes are kept for your browser session. Logged-in users can also save favorites to their account.',
            ],
            [
                'question' => 'What is a Dynamic QR?',
                'answer' => 'Signed-in users can create Dynamic QR codes with a short URL. You can change the destination later, add a password, set an expiry date, and track scans by country, device, browser and OS.',
            ],
        ]);

        $recent = $this->qr->repository()->recentFor(Auth::id(), session()->getId(), 8);

        $qrGuideBlogs = BlogPost::query()
            ->published()
            ->whereNotNull('related_qr_type')
            ->orderBy('published_at')
            ->get(['id', 'title', 'slug', 'excerpt', 'content', 'related_qr_type', 'reading_time_minutes', 'published_at', 'meta_keywords'])
            ->mapWithKeys(static function (BlogPost $post) {
                return [
                    $post->related_qr_type => [
                        'title' => $post->title,
                        'excerpt' => $post->excerpt,
                        'content' => $post->content,
                        'url' => route('blog.show', $post),
                        'reading_time' => (int) $post->reading_time_minutes,
                        'keywords' => $post->meta_keywords,
                        'published_at' => optional($post->published_at)->toDateString(),
                    ],
                ];
            })
            ->all();

        return view('qr-code-generator.show', [
            'meta' => $meta,
            'breadcrumbs' => $breadcrumbs,
            'breadcrumbSchema' => $this->seo->breadcrumbSchema($breadcrumbs),
            'webAppSchema' => $webAppSchema,
            'faqSchema' => $faqSchema,
            'types' => $this->qr->types()->options(),
            'sizes' => [128, 256, 512, 1024],
            'formats' => array_map(static fn (QrOutputFormat $f) => $f->value, QrOutputFormat::cases()),
            'moduleStyles' => array_map(static fn (QrModuleStyle $s) => ['value' => $s->value, 'label' => $s->label()], QrModuleStyle::cases()),
            'eyeStyles' => array_map(static fn (QrEyeStyle $s) => ['value' => $s->value, 'label' => $s->label()], QrEyeStyle::cases()),
            'frameStyles' => array_map(static fn (QrFrameStyle $s) => ['value' => $s->value, 'label' => $s->label()], QrFrameStyle::cases()),
            'errorLevels' => array_map(
                static fn (QrErrorCorrection $level) => [
                    'value' => $level->value,
                    'label' => $level->label(),
                ],
                QrErrorCorrection::cases()
            ),
            'recent' => $recent,
            'isAuthenticated' => Auth::check(),
            'qrGuideBlogs' => $qrGuideBlogs,
        ]);
    }

    public function createDynamic(DynamicQrStoreRequest $request): JsonResponse
    {
        try {
            $created = $this->dynamic->create(
                $request->payload(),
                $request->file('logo'),
                $request->user()?->id
            );

            /** @var QrCode $qr */
            $qr = $created['qr'];

            return response()->json([
                'success' => true,
                'data' => [
                    'uuid' => $qr->uuid,
                    'short_code' => $qr->short_code,
                    'short_url' => $created['short_url'],
                    'destination_url' => $qr->destination_url,
                    'title' => $qr->title,
                    'image' => $created['image'],
                    'manage_url' => route('account.qr-codes.show', $qr),
                    'expires_at' => $qr->expires_at?->toIso8601String(),
                    'password_protected' => $qr->isPasswordProtected(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Unable to create dynamic QR code.'], 500);
        }
    }

    public function preview(QrGenerateRequest $request): JsonResponse
    {
        try {
            $persist = (bool) $request->boolean('save_history');
            $result = $this->qr->preview(
                $request->qrPayload(),
                $request->file('logo'),
                persist: $persist
            );

            return response()->json([
                'success' => true,
                'data' => $result->toPreviewArray(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            $message = 'Unable to generate QR code. Please check your inputs.';
            if (config('app.debug')) {
                $message = $e->getMessage();
            } elseif (str_contains($e->getMessage(), 'GD') || str_contains($e->getMessage(), 'gd')) {
                $message = $e->getMessage();
            } elseif (! class_exists(\Endroid\QrCode\Builder\Builder::class)) {
                $message = 'QR library missing on server. Run composer install.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error_class' => class_basename($e),
            ], 500);
        }
    }

    public function download(QrGenerateRequest $request): SymfonyResponse
    {
        $format = QrOutputFormat::tryFrom((string) $request->input('format', 'png')) ?? QrOutputFormat::Png;

        try {
            $payload = $request->qrPayload();
            $payload['save_history'] = true;
            $result = $this->qr->download($payload, $format, $request->file('logo'));
            $filename = 'qr-'.$result->type->value.'-'.now()->format('Ymd-His').'.'.$result->format->extension();

            return response($result->binary, 200, [
                'Content-Type' => $result->mimeType,
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'no-store, private',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Unable to download QR code.'], 500);
        }
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048', 'mimes:png,jpg,jpeg,webp,gif'],
        ]);

        $token = $this->qr->storeLogo($request->file('logo'));

        return response()->json([
            'success' => true,
            'data' => ['logo_token' => $token],
        ]);
    }

    public function recent(): JsonResponse
    {
        $items = $this->qr->repository()->recentFor(Auth::id(), session()->getId(), 12);

        return response()->json([
            'success' => true,
            'data' => $items->map(fn (QrCode $qr) => $this->serializeQr($qr))->values(),
        ]);
    }

    public function saved(): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Login required to view saved QR codes.'], 401);
        }

        $items = $this->qr->repository()->savedFor((int) Auth::id(), 50);

        return response()->json([
            'success' => true,
            'data' => $items->map(fn (QrCode $qr) => $this->serializeQr($qr))->values(),
        ]);
    }

    public function save(string $uuid): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Login required to save QR codes.'], 401);
        }

        $qr = $this->qr->repository()->findByUuid($uuid);
        if (! $qr || (int) $qr->user_id !== (int) Auth::id()) {
            // Allow saving a session item by claiming it
            if ($qr && $qr->session_id === session()->getId() && ! $qr->user_id) {
                $qr->update(['user_id' => Auth::id()]);
            } else {
                return response()->json(['success' => false, 'message' => 'QR code not found.'], 404);
            }
        }

        $saved = $this->qr->repository()->markSaved($qr, true);

        return response()->json(['success' => true, 'data' => $this->serializeQr($saved)]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $qr = $this->qr->repository()->findByUuid($uuid);
        if (! $qr) {
            return response()->json(['success' => false, 'message' => 'QR code not found.'], 404);
        }

        $ok = $this->qr->repository()->deleteForOwner($qr, Auth::id(), session()->getId());
        if (! $ok) {
            return response()->json(['success' => false, 'message' => 'Not allowed.'], 403);
        }

        if ($qr->preview_path) {
            Storage::disk('public')->delete($qr->preview_path);
        }

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeQr(QrCode $qr): array
    {
        return [
            'uuid' => $qr->uuid,
            'type' => $qr->type,
            'title' => $qr->title,
            'payload' => $qr->payload,
            'is_saved' => (bool) $qr->is_saved,
            'preview_url' => $qr->preview_path ? Storage::disk('public')->url($qr->preview_path) : null,
            'created_at' => optional($qr->created_at)?->toDateTimeString(),
            'input' => $qr->input_json,
            'style' => $qr->style_json,
        ];
    }
}

<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\QrBrandTemplate;
use App\Services\Qr\BrandTemplateService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BrandTemplateController extends Controller
{
    public function __construct(
        protected BrandTemplateService $templates,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        return view('account.brand-templates.index', [
            'user' => $request->user(),
            'templates' => $request->user()->qrBrandTemplates()->latest()->get(),
            'workspaces' => $request->user()->qrWorkspaces()->get(),
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'Brand Templates — CalchubNepal',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'foreground' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'background' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'module_style' => ['nullable', 'string', 'max:32'],
            'eye_style' => ['nullable', 'string', 'max:32'],
            'frame_style' => ['nullable', 'string', 'max:32'],
            'frame_label' => ['nullable', 'string', 'max:40'],
            'workspace_id' => ['nullable', 'integer'],
            'is_default' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->templates->create($request->user(), $data, $request->file('logo'));

        return back()->with('status', 'template-created');
    }

    public function destroy(Request $request, QrBrandTemplate $brandTemplate): RedirectResponse
    {
        try {
            $this->templates->delete($brandTemplate, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'template-deleted');
    }
}

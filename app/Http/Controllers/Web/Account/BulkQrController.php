<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\QrBulkJob;
use App\Services\Qr\BulkQrService;
use App\Services\Qr\QrEntitlementService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkQrController extends Controller
{
    public function __construct(
        protected BulkQrService $bulk,
        protected QrEntitlementService $entitlements,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        return view('account.bulk-qr.index', [
            'user' => $request->user(),
            'jobs' => $request->user()->qrBulkJobs()->latest()->paginate(10),
            'templates' => $request->user()->qrBrandTemplates()->latest()->get(),
            'campaigns' => $request->user()->qrCampaigns()->latest()->get(),
            'workspaces' => $request->user()->qrWorkspaces()->get(),
            'maxRows' => $this->entitlements->maxBulkRows($request->user()),
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'Bulk QR Generation — CalchubNepal',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'workspace_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'brand_template_id' => ['nullable', 'integer'],
        ]);

        try {
            $job = $this->bulk->start($request->user(), $request->file('file'), $data);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('account.bulk-qr.index')
            ->with('status', 'bulk-completed')
            ->with('bulk_job_uuid', $job->uuid);
    }

    public function download(Request $request, QrBulkJob $bulkJob): StreamedResponse|RedirectResponse
    {
        abort_unless((int) $bulkJob->user_id === (int) $request->user()->id, 403);
        if (! $bulkJob->isReady()) {
            return back()->with('error', 'ZIP is not ready yet.');
        }

        $path = storage_path('app/public/'.$bulkJob->output_zip_path);
        if (! is_file($path)) {
            return back()->with('error', 'ZIP file missing.');
        }

        return response()->download($path, 'qr-bulk-'.$bulkJob->uuid.'.zip');
    }
}

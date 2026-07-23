<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertiser;
use App\Services\Ads\AdvertiserReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdReportController extends Controller
{
    public function __construct(protected AdvertiserReportService $reports)
    {
    }

    public function index(): View
    {
        abort_unless(
            request()->user()?->hasRole('super-admin') || request()->user()?->hasRole('admin'),
            403
        );

        return view('admin.ad-reports.index', [
            'advertisers' => Advertiser::query()->orderBy('company_name')->get(['id', 'company_name']),
            'positions' => config('calculator_hub.ads.positions', []),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->hasRole('super-admin') || $request->user()?->hasRole('admin'),
            403
        );

        [$range, $filters] = $this->parseRequest($request);
        $source = (string) $request->input('source', 'all'); // all | network | adsense

        $network = null;
        $adsense = null;

        if ($source !== 'adsense') {
            $network = $this->reports->adminOverview($range['from'], $range['to'], $filters);
        }
        if ($source !== 'network') {
            $adsense = $this->reports->adsenseOverview($range['from'], $range['to'], $filters);
        }

        return response()->json([
            'range' => [
                'label' => $range['label'],
                'from' => $range['from']->toDateString(),
                'to' => $range['to']->toDateString(),
            ],
            'filters' => $filters,
            'source' => $source,
            'summary' => $network['summary'] ?? null,
            'series' => $network['series'] ?? null,
            'by_company' => $network['by_company'] ?? [],
            'by_position' => $network['by_position'] ?? [],
            'table' => $network['table'] ?? [],
            'adsense' => $adsense,
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()?->hasRole('super-admin') || $request->user()?->hasRole('admin'),
            403
        );

        [$range, $filters] = $this->parseRequest($request);
        $source = (string) $request->input('source', 'all');
        $network = $source !== 'adsense'
            ? $this->reports->adminOverview($range['from'], $range['to'], $filters)
            : null;
        $adsense = $source !== 'network'
            ? $this->reports->adsenseOverview($range['from'], $range['to'], $filters)
            : null;

        $filename = 'ad-reports-'.$range['from']->format('Ymd').'-'.$range['to']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($network, $adsense) {
            $out = fopen('php://output', 'w');

            if ($network) {
                fputcsv($out, ['=== Company-wise (Network Ads) ===']);
                fputcsv($out, ['Company', 'Impressions', 'Clicks', 'CTR %']);
                foreach ($network['by_company'] as $row) {
                    fputcsv($out, [$row->company, $row->impressions, $row->clicks, $row->ctr]);
                }
                fputcsv($out, []);
                fputcsv($out, ['=== Position / Category (Network) ===']);
                fputcsv($out, ['Position', 'Impressions', 'Clicks', 'CTR %']);
                foreach ($network['by_position'] as $row) {
                    fputcsv($out, [$row->label, $row->impressions, $row->clicks, $row->ctr]);
                }
                fputcsv($out, []);
            }

            if ($adsense) {
                fputcsv($out, ['=== Google AdSense (site impressions) ===']);
                fputcsv($out, ['Total impressions', $adsense['summary']['impressions']]);
                fputcsv($out, ['Unit impressions', $adsense['summary']['unit_impressions']]);
                fputcsv($out, ['AdSense ad-row impressions', $adsense['summary']['advertisement_impressions']]);
                fputcsv($out, []);
                fputcsv($out, ['Position', 'Impressions']);
                foreach ($adsense['by_position'] as $row) {
                    fputcsv($out, [$row->label, $row->impressions]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Date', 'Position', 'Source', 'Impressions']);
                foreach ($adsense['table'] as $row) {
                    fputcsv($out, [$row->date, $row->position_label, $row->source, $row->impressions]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request): Response
    {
        abort_unless(
            $request->user()?->hasRole('super-admin') || $request->user()?->hasRole('admin'),
            403
        );

        [$range, $filters] = $this->parseRequest($request);
        $source = (string) $request->input('source', 'all');
        $network = $source !== 'adsense'
            ? $this->reports->adminOverview($range['from'], $range['to'], $filters)
            : null;
        $adsense = $source !== 'network'
            ? $this->reports->adsenseOverview($range['from'], $range['to'], $filters)
            : null;

        $companyName = null;
        if (! empty($filters['advertiser_id'])) {
            $companyName = Advertiser::query()->whereKey($filters['advertiser_id'])->value('company_name');
        }

        $pdf = Pdf::loadView('admin.ad-reports.pdf', [
            'range' => $range,
            'filters' => $filters,
            'source' => $source,
            'companyName' => $companyName,
            'summary' => $network['summary'] ?? null,
            'byCompany' => $network['by_company'] ?? collect(),
            'byPosition' => $network['by_position'] ?? collect(),
            'table' => $network['table'] ?? collect(),
            'adsense' => $adsense,
            'positions' => config('calculator_hub.ads.positions', []),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('ad-reports-'.$range['from']->format('Ymd').'.pdf');
    }

    /**
     * @return array{0: array{from: \Carbon\Carbon, to: \Carbon\Carbon, label: string}, 1: array{advertiser_id: ?int, position: ?string}}
     */
    protected function parseRequest(Request $request): array
    {
        $range = $this->reports->resolveRange(
            (string) $request->input('range', 'last_30'),
            $request->input('from'),
            $request->input('to'),
        );

        $filters = [
            'advertiser_id' => $request->filled('advertiser_id') ? (int) $request->input('advertiser_id') : null,
            'position' => $request->filled('position') ? (string) $request->input('position') : null,
        ];

        return [$range, $filters];
    }
}

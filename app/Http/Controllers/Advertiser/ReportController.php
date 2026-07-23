<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Services\Ads\AdvertiserReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected AdvertiserReportService $reports)
    {
    }

    public function index(Request $request): View
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        return view('advertiser.reports.index', compact('advertiser'));
    }

    public function data(Request $request): JsonResponse
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $preset = (string) $request->input('range', 'last_30');
        $range = $this->reports->resolveRange(
            $preset,
            $request->input('from'),
            $request->input('to'),
        );

        $summary = $this->reports->summary($advertiser, $range['from'], $range['to']);
        $series = $this->reports->dailySeries($advertiser, $range['from'], $range['to']);
        $table = $this->reports->dailyTable($advertiser, $range['from'], $range['to']);

        return response()->json([
            'range' => [
                'label' => $range['label'],
                'from' => $range['from']->toDateString(),
                'to' => $range['to']->toDateString(),
            ],
            'summary' => $summary,
            'series' => $series,
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $preset = (string) $request->input('range', 'last_30');
        $range = $this->reports->resolveRange($preset, $request->input('from'), $request->input('to'));
        $rows = $this->reports->dailyTable($advertiser, $range['from'], $range['to']);

        $filename = 'advertiser-report-'.$range['from']->format('Ymd').'-'.$range['to']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Advertisement', 'Impressions', 'Clicks', 'CTR %']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->date, $row->advertisement, $row->impressions, $row->clicks, $row->ctr]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $preset = (string) $request->input('range', 'last_30');
        $range = $this->reports->resolveRange($preset, $request->input('from'), $request->input('to'));
        $summary = $this->reports->summary($advertiser, $range['from'], $range['to']);
        $table = $this->reports->dailyTable($advertiser, $range['from'], $range['to']);

        $pdf = Pdf::loadView('advertiser.reports.pdf', [
            'advertiser' => $advertiser,
            'range' => $range,
            'summary' => $summary,
            'table' => $table,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('advertiser-report-'.$range['from']->format('Ymd').'.pdf');
    }
}

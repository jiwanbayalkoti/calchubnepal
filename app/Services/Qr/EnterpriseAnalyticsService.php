<?php

namespace App\Services\Qr;

use App\Models\QrCampaign;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\QrWorkspace;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnterpriseAnalyticsService
{
    public function __construct(protected QrScanAnalyticsService $scans)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function workspaceOverview(QrWorkspace $workspace): array
    {
        $qrIds = $workspace->qrCodes()->pluck('id');
        $totalScans = (int) QrScan::query()->whereIn('qr_code_id', $qrIds)->count();
        $today = (int) QrScan::query()->whereIn('qr_code_id', $qrIds)->whereDate('scanned_at', today())->count();

        return [
            'qr_count' => $qrIds->count(),
            'total_scans' => $totalScans,
            'today_scans' => $today,
            'heat_map' => $this->heatMapForQrIds($qrIds->all()),
            'top_campaigns' => $workspace->campaigns()
                ->withSum('qrCodes as scan_total', 'scan_count')
                ->orderByDesc('scan_total')
                ->limit(8)
                ->get()
                ->map(fn ($c) => [
                    'name' => $c->name,
                    'scans' => (int) ($c->scan_total ?? 0),
                    'uuid' => $c->uuid,
                ])
                ->all(),
            'devices' => $this->groupForQrIds($qrIds->all(), 'device'),
            'countries' => $this->groupForQrIds($qrIds->all(), 'country'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function campaignReport(QrCampaign $campaign): array
    {
        $qrIds = $campaign->qrCodes()->pluck('id')->all();
        $total = (int) QrScan::query()->whereIn('qr_code_id', $qrIds)->count();

        return [
            'qr_count' => count($qrIds),
            'total_scans' => $total,
            'heat_map' => $this->heatMapForQrIds($qrIds),
            'daily' => $this->dailyForQrIds($qrIds, 30),
            'devices' => $this->groupForQrIds($qrIds, 'device'),
            'browsers' => $this->groupForQrIds($qrIds, 'browser'),
            'countries' => $this->groupForQrIds($qrIds, 'country'),
            'operating_systems' => $this->groupForQrIds($qrIds, 'os'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function userEnterpriseDashboard(User $user): array
    {
        $qrIds = $user->dynamicQrCodes()->pluck('id');

        return [
            'dynamic_qr_count' => $qrIds->count(),
            'total_scans' => (int) QrScan::query()->whereIn('qr_code_id', $qrIds)->count(),
            'workspace_count' => $user->qrWorkspaces()->count(),
            'campaign_count' => $user->qrCampaigns()->count(),
            'heat_map' => $this->heatMapForQrIds($qrIds->all()),
            'countries' => $this->groupForQrIds($qrIds->all(), 'country'),
            'devices' => $this->groupForQrIds($qrIds->all(), 'device'),
        ];
    }

    /**
     * Country-centroid heat points for map rendering.
     *
     * @param  list<int>  $qrIds
     * @return list<array{country: string, scans: int, lat: float, lng: float}>
     */
    public function heatMapForQrIds(array $qrIds): array
    {
        if ($qrIds === []) {
            return [];
        }

        $rows = QrScan::query()
            ->select('country', DB::raw('COUNT(*) as scans'), DB::raw('AVG(latitude) as lat'), DB::raw('AVG(longitude) as lng'))
            ->whereIn('qr_code_id', $qrIds)
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('scans')
            ->limit(50)
            ->get();

        $centroids = $this->countryCentroids();

        return $rows->map(function ($row) use ($centroids) {
            $code = strtoupper((string) $row->country);
            $point = $centroids[$code] ?? null;

            return [
                'country' => $code,
                'scans' => (int) $row->scans,
                'lat' => $row->lat !== null ? (float) $row->lat : (float) ($point['lat'] ?? 0),
                'lng' => $row->lng !== null ? (float) $row->lng : (float) ($point['lng'] ?? 0),
            ];
        })->filter(fn ($r) => $r['lat'] != 0 || $r['lng'] != 0)->values()->all();
    }

    /**
     * @param  list<int>  $qrIds
     * @return list<array{date: string, scans: int}>
     */
    protected function dailyForQrIds(array $qrIds, int $days): array
    {
        if ($qrIds === []) {
            return [];
        }
        $since = now()->subDays($days - 1)->startOfDay();
        $rows = QrScan::query()
            ->select(DB::raw('DATE(scanned_at) as day'), DB::raw('COUNT(*) as scans'))
            ->whereIn('qr_code_id', $qrIds)
            ->where('scanned_at', '>=', $since)
            ->groupBy('day')
            ->pluck('scans', 'day');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $since->copy()->addDays($i)->toDateString();
            $series[] = ['date' => $day, 'scans' => (int) ($rows[$day] ?? 0)];
        }

        return $series;
    }

    /**
     * @param  list<int>  $qrIds
     * @return list<array{label: string, scans: int}>
     */
    protected function groupForQrIds(array $qrIds, string $column): array
    {
        if ($qrIds === []) {
            return [];
        }

        return QrScan::query()
            ->select($column, DB::raw('COUNT(*) as scans'))
            ->whereIn('qr_code_id', $qrIds)
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('scans')
            ->limit(15)
            ->get()
            ->map(fn ($r) => ['label' => (string) $r->{$column}, 'scans' => (int) $r->scans])
            ->all();
    }

    /**
     * Approximate ISO country centroids for heat maps.
     *
     * @return array<string, array{lat: float, lng: float}>
     */
    protected function countryCentroids(): array
    {
        return [
            'NP' => ['lat' => 28.3949, 'lng' => 84.1240],
            'IN' => ['lat' => 20.5937, 'lng' => 78.9629],
            'US' => ['lat' => 37.0902, 'lng' => -95.7129],
            'GB' => ['lat' => 55.3781, 'lng' => -3.4360],
            'AU' => ['lat' => -25.2744, 'lng' => 133.7751],
            'CA' => ['lat' => 56.1304, 'lng' => -106.3468],
            'DE' => ['lat' => 51.1657, 'lng' => 10.4515],
            'FR' => ['lat' => 46.2276, 'lng' => 2.2137],
            'JP' => ['lat' => 36.2048, 'lng' => 138.2529],
            'CN' => ['lat' => 35.8617, 'lng' => 104.1954],
            'AE' => ['lat' => 23.4241, 'lng' => 53.8478],
            'SG' => ['lat' => 1.3521, 'lng' => 103.8198],
            'PK' => ['lat' => 30.3753, 'lng' => 69.3451],
            'BD' => ['lat' => 23.6850, 'lng' => 90.3563],
        ];
    }
}

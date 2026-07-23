<?php

namespace App\Console\Commands;

use App\Models\PageView;
use App\Services\Analytics\GeoCountryResolver;
use Illuminate\Console\Command;

class BackfillPageViewCountries extends Command
{
    protected $signature = 'analytics:backfill-countries {--limit=200 : Max distinct truncated IPs to resolve}';

    protected $description = 'Fill missing page_views.country using truncated IP geolocation';

    public function handle(GeoCountryResolver $geo): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $ips = PageView::query()
            ->where(function ($q) {
                $q->whereNull('country')->orWhere('country', '');
            })
            ->whereNotNull('ip_truncated')
            ->where('ip_truncated', '!=', '')
            ->distinct()
            ->orderBy('ip_truncated')
            ->limit($limit)
            ->pluck('ip_truncated');

        if ($ips->isEmpty()) {
            $this->info('No missing-country IPs to backfill.');

            return self::SUCCESS;
        }

        $updated = 0;
        $resolved = 0;

        foreach ($ips as $truncated) {
            $code = $geo->fromTruncatedIp((string) $truncated);
            if (! $code) {
                $this->line("  skip {$truncated}");
                continue;
            }

            $resolved++;
            $count = PageView::query()
                ->where('ip_truncated', $truncated)
                ->where(function ($q) {
                    $q->whereNull('country')->orWhere('country', '');
                })
                ->update(['country' => $code]);

            $updated += $count;
            $this->line("  {$truncated} → {$code} ({$count} rows)");

            // Be gentle with free geo APIs.
            usleep(200_000);
        }

        $this->info("Resolved {$resolved} IPs, updated {$updated} page views.");

        return self::SUCCESS;
    }
}

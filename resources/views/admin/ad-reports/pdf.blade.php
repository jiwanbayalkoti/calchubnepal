<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ad Reports</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 14px 0 6px; }
        .muted { color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background: #f0f4fa; }
        .cards td { border: 0; padding: 3px 10px 3px 0; }
    </style>
</head>
<body>
    <h1>CalchubNepal — Advertisement Reports</h1>
    <div class="muted">
        {{ $range['label'] }}: {{ $range['from']->toDateString() }} → {{ $range['to']->toDateString() }}
        · Source: {{ $source ?? 'all' }}
        @if ($companyName) · Company: {{ $companyName }} @endif
        @if (!empty($filters['position'])) · Position: {{ $filters['position'] }} @endif
    </div>

    @if ($summary)
        <h2>Network Ads</h2>
        <table class="cards">
            <tr>
                <td><strong>Impressions:</strong> {{ number_format($summary['impressions']) }}</td>
                <td><strong>Clicks:</strong> {{ number_format($summary['clicks']) }}</td>
                <td><strong>CTR:</strong> {{ $summary['ctr'] }}%</td>
                <td><strong>Companies:</strong> {{ $summary['companies'] }}</td>
            </tr>
        </table>

        <h2>Company-wise</h2>
        <table>
            <thead><tr><th>Company</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr></thead>
            <tbody>
                @forelse ($byCompany as $row)
                    <tr>
                        <td>{{ $row->company }}</td>
                        <td>{{ $row->impressions }}</td>
                        <td>{{ $row->clicks }}</td>
                        <td>{{ $row->ctr }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Position / Category</h2>
        <table>
            <thead><tr><th>Position</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr></thead>
            <tbody>
                @forelse ($byPosition as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td>{{ $row->impressions }}</td>
                        <td>{{ $row->clicks }}</td>
                        <td>{{ $row->ctr }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if (!empty($adsense))
        <h2>Google AdSense (site impressions)</h2>
        <div class="muted">{{ $adsense['summary']['note'] ?? '' }}</div>
        <table class="cards">
            <tr>
                <td><strong>Total:</strong> {{ number_format($adsense['summary']['impressions']) }}</td>
                <td><strong>Site units:</strong> {{ number_format($adsense['summary']['unit_impressions']) }}</td>
                <td><strong>Ad rows:</strong> {{ number_format($adsense['summary']['advertisement_impressions']) }}</td>
            </tr>
        </table>
        <table>
            <thead><tr><th>Position</th><th>Impressions</th></tr></thead>
            <tbody>
                @forelse ($adsense['by_position'] as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td>{{ $row->impressions }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">No AdSense data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>

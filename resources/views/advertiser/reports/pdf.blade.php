<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Advertiser Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f0f4fa; }
        .cards td { border: 0; padding: 4px 8px 4px 0; }
    </style>
</head>
<body>
    <h1>{{ $advertiser->company_name }} — Ad Report</h1>
    <div class="muted">{{ $range['label'] }}: {{ $range['from']->toDateString() }} → {{ $range['to']->toDateString() }}</div>
    <table class="cards">
        <tr>
            <td><strong>Impressions:</strong> {{ number_format($summary['impressions']) }}</td>
            <td><strong>Clicks:</strong> {{ number_format($summary['clicks']) }}</td>
            <td><strong>CTR:</strong> {{ $summary['ctr'] }}%</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr><th>Date</th><th>Advertisement</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr>
        </thead>
        <tbody>
            @forelse ($table as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->advertisement }}</td>
                    <td>{{ $row->impressions }}</td>
                    <td>{{ $row->clicks }}</td>
                    <td>{{ $row->ctr }}%</td>
                </tr>
            @empty
                <tr><td colspan="5">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

@extends('layouts.advertiser')

@section('title', 'Reports')
@section('page-title', 'Reports')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Reports</li>
@endpush

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="form-row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label>Date Range</label>
                    <select id="rangePreset" class="form-control">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_7">Last 7 Days</option>
                        <option value="last_30" selected>Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2 custom-dates d-none">
                    <label>From</label>
                    <input type="date" id="rangeFrom" class="form-control">
                </div>
                <div class="col-md-2 form-group mb-2 custom-dates d-none">
                    <label>To</label>
                    <input type="date" id="rangeTo" class="form-control">
                </div>
                <div class="col-md-2 form-group mb-2">
                    <button type="button" id="btnLoadReport" class="btn btn-primary btn-block">Apply</button>
                </div>
                <div class="col-md-3 form-group mb-2 text-right">
                    <a href="#" id="btnExportExcel" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
                    <a href="#" id="btnExportPdf" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="summaryCards">
        <div class="col-md-2 col-6"><div class="small-box bg-primary"><div class="inner"><h3 id="sImp">0</h3><p>Impressions</p></div></div></div>
        <div class="col-md-2 col-6"><div class="small-box bg-success"><div class="inner"><h3 id="sClk">0</h3><p>Clicks</p></div></div></div>
        <div class="col-md-2 col-6"><div class="small-box bg-warning"><div class="inner"><h3 id="sCtr">0%</h3><p>CTR</p></div></div></div>
        <div class="col-md-3 col-6"><div class="small-box bg-info"><div class="inner"><h3 id="sAvgImp">0</h3><p>Avg Daily Impressions</p></div></div></div>
        <div class="col-md-3 col-6"><div class="small-box bg-secondary"><div class="inner"><h3 id="sAvgClk">0</h3><p>Avg Daily Clicks</p></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Daily Impressions</h3></div><div class="card-body"><canvas id="impChart"></canvas></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Daily Clicks</h3></div><div class="card-body"><canvas id="clkChart"></canvas></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">CTR Trend</h3></div><div class="card-body"><canvas id="ctrChart"></canvas></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Daily Breakdown</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0" id="reportTable">
                <thead><tr><th>Date</th><th>Advertisement</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let impChart, clkChart, ctrChart;

function qs() {
    const range = $('#rangePreset').val();
    let q = 'range=' + encodeURIComponent(range);
    if (range === 'custom') {
        q += '&from=' + encodeURIComponent($('#rangeFrom').val() || '');
        q += '&to=' + encodeURIComponent($('#rangeTo').val() || '');
    }
    return q;
}

function loadReport() {
    $.getJSON('{{ route('advertiser.reports.data') }}?' + qs(), function (res) {
        $('#sImp').text(res.summary.impressions.toLocaleString());
        $('#sClk').text(res.summary.clicks.toLocaleString());
        $('#sCtr').text(res.summary.ctr + '%');
        $('#sAvgImp').text(res.summary.avg_daily_impressions);
        $('#sAvgClk').text(res.summary.avg_daily_clicks);

        const labels = res.series.labels || [];
        const mk = (el, label, data, color) => new Chart(el, {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: color }] },
            options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        if (impChart) impChart.destroy();
        if (clkChart) clkChart.destroy();
        if (ctrChart) ctrChart.destroy();
        impChart = mk(document.getElementById('impChart'), 'Impressions', res.series.impressions, 'rgba(30,91,184,.75)');
        clkChart = mk(document.getElementById('clkChart'), 'Clicks', res.series.clicks, 'rgba(40,167,69,.75)');
        ctrChart = new Chart(document.getElementById('ctrChart'), {
            type: 'line',
            data: { labels, datasets: [{ label: 'CTR %', data: res.series.ctr, borderColor: '#ffc107', tension: .3 }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        const $tb = $('#reportTable tbody').empty();
        (res.table || []).forEach(r => {
            $tb.append(`<tr><td>${r.date}</td><td>${$('<div>').text(r.advertisement).html()}</td><td>${r.impressions}</td><td>${r.clicks}</td><td>${r.ctr}%</td></tr>`);
        });
        if (!(res.table || []).length) {
            $tb.append('<tr><td colspan="5" class="text-muted p-3">No data for this range.</td></tr>');
        }

        $('#btnExportExcel').attr('href', '{{ route('advertiser.reports.export.excel') }}?' + qs());
        $('#btnExportPdf').attr('href', '{{ route('advertiser.reports.export.pdf') }}?' + qs());
    });
}

$('#rangePreset').on('change', function () {
    $('.custom-dates').toggleClass('d-none', $(this).val() !== 'custom');
});
$('#btnLoadReport').on('click', loadReport);
loadReport();
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Ad Reports')
@section('page-title', 'Advertisement Reports')

@push('breadcrumbs')
    <li class="breadcrumb-item active">Ad Reports</li>
@endpush

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="form-row align-items-end">
                <div class="col-md-2 form-group mb-2">
                    <label>Source</label>
                    <select id="filterSource" class="form-control">
                        <option value="all" selected>All</option>
                        <option value="network">Network Ads</option>
                        <option value="adsense">Google AdSense</option>
                    </select>
                </div>
                <div class="col-md-3 form-group mb-2 filter-network">
                    <label>Company</label>
                    <select id="filterCompany" class="form-control select2">
                        <option value="">All companies</option>
                        @foreach ($advertisers as $adv)
                            <option value="{{ $adv->id }}">{{ $adv->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label>Position / Category</label>
                    <select id="filterPosition" class="form-control">
                        <option value="">All positions</option>
                        @foreach ($positions as $key => $meta)
                            <option value="{{ $key }}">{{ $meta['label'] ?? ucfirst($key) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
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
                <div class="col-md-1 form-group mb-2 custom-dates d-none">
                    <label>From</label>
                    <input type="date" id="rangeFrom" class="form-control">
                </div>
                <div class="col-md-1 form-group mb-2 custom-dates d-none">
                    <label>To</label>
                    <input type="date" id="rangeTo" class="form-control">
                </div>
                <div class="col-md-1 form-group mb-2">
                    <button type="button" id="btnResetFilters" class="btn btn-outline-secondary btn-block" title="Reset filters">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
            <div class="text-right">
                <a href="#" id="btnExportExcel" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
                <a href="#" id="btnExportPdf" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</a>
            </div>
        </div>
    </div>

    <div id="networkSection">
        <div class="row">
            <div class="col-lg-2 col-6"><div class="small-box bg-primary"><div class="inner"><h3 id="sImp">0</h3><p>Network Imp.</p></div></div></div>
            <div class="col-lg-2 col-6"><div class="small-box bg-success"><div class="inner"><h3 id="sClk">0</h3><p>Network Clicks</p></div></div></div>
            <div class="col-lg-2 col-6"><div class="small-box bg-warning"><div class="inner"><h3 id="sCtr">0%</h3><p>CTR</p></div></div></div>
            <div class="col-lg-2 col-6"><div class="small-box bg-info"><div class="inner"><h3 id="sCompanies">0</h3><p>Companies</p></div></div></div>
            <div class="col-lg-2 col-6"><div class="small-box bg-secondary"><div class="inner"><h3 id="sAvgImp">0</h3><p>Avg Daily Imp.</p></div></div></div>
            <div class="col-lg-2 col-6"><div class="small-box bg-dark"><div class="inner"><h3 id="sAvgClk">0</h3><p>Avg Daily Clicks</p></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">Network Ads · Daily Trend</h3></div>
                    <div class="card-body"><canvas id="trendChart" style="min-height:280px;"></canvas></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Network · By Position</h3></div>
                    <div class="card-body p-0" style="max-height:340px;overflow-y:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Position</th><th class="text-right">Imp.</th><th class="text-right">Clicks</th><th class="text-right">CTR</th></tr></thead>
                            <tbody id="byPositionBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card card-outline card-success">
                    <div class="card-header"><h3 class="card-title">Company-wise Performance</h3></div>
                    <div class="card-body p-0" style="max-height:420px;overflow-y:auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Company</th><th class="text-right">Imp.</th><th class="text-right">Clicks</th><th class="text-right">CTR</th></tr></thead>
                            <tbody id="byCompanyBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Network · Daily Detail</h3></div>
                    <div class="card-body p-0" style="max-height:420px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Company</th>
                                    <th>Ad</th>
                                    <th>Position</th>
                                    <th class="text-right">Imp.</th>
                                    <th class="text-right">Clicks</th>
                                    <th class="text-right">CTR</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="adsenseSection">
        <h4 class="mb-3 mt-2"><i class="fab fa-google mr-1"></i> Google AdSense</h4>
        <p class="text-muted small mb-3" id="adsenseNote">Site-side unit impressions. Clicks &amp; revenue stay in your Google AdSense account.</p>

        <div class="row">
            <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3 id="asImp">0</h3><p>AdSense Imp.</p></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-orange" style="background:#fd7e14!important;"><div class="inner"><h3 id="asUnit">0</h3><p>Site Unit Imp.</p></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-pink" style="background:#e83e8c!important;"><div class="inner"><h3 id="asAdRow">0</h3><p>AdSense Ad-row Imp.</p></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-lightblue"><div class="inner"><h3 id="asAvg">0</h3><p>Avg Daily Imp.</p></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="card card-outline card-danger">
                    <div class="card-header"><h3 class="card-title">AdSense · Daily Impressions</h3></div>
                    <div class="card-body"><canvas id="adsenseChart" style="min-height:260px;"></canvas></div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card card-outline card-warning">
                    <div class="card-header"><h3 class="card-title">AdSense · By Position</h3></div>
                    <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Position</th><th class="text-right">Impressions</th></tr></thead>
                            <tbody id="adsensePosBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">AdSense · Daily Detail</h3></div>
                    <div class="card-body p-0" style="max-height:360px;overflow-y:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Date</th><th>Position</th><th>Source</th><th class="text-right">Imp.</th></tr></thead>
                            <tbody id="adsenseDetailBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">AdSense Ads (from Advertisements)</h3></div>
                    <div class="card-body p-0" style="max-height:360px;overflow-y:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Name</th><th>Position</th><th class="text-right">Imp.</th><th class="text-right">Clicks*</th></tr></thead>
                            <tbody id="adsenseAdsBody"></tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted small">* Clicks only when the ad uses our tracked link (not pure AdSense code).</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let trendChart, adsenseChart;

function qs() {
    let q = 'range=' + encodeURIComponent($('#rangePreset').val());
    q += '&source=' + encodeURIComponent($('#filterSource').val() || 'all');
    const company = $('#filterCompany').val();
    const position = $('#filterPosition').val();
    if (company) q += '&advertiser_id=' + encodeURIComponent(company);
    if (position) q += '&position=' + encodeURIComponent(position);
    if ($('#rangePreset').val() === 'custom') {
        q += '&from=' + encodeURIComponent($('#rangeFrom').val() || '');
        q += '&to=' + encodeURIComponent($('#rangeTo').val() || '');
    }
    return q;
}

function esc(s) {
    return $('<div>').text(s == null ? '' : s).html();
}

function toggleSections() {
    const source = $('#filterSource').val();
    $('#networkSection').toggle(source !== 'adsense');
    $('#adsenseSection').toggle(source !== 'network');
    $('.filter-network').toggle(source !== 'adsense');
}

function loadReport() {
    toggleSections();
    $.getJSON('{{ route('admin.ad-reports.data') }}?' + qs(), function (res) {
        if (res.summary) {
            $('#sImp').text((res.summary.impressions || 0).toLocaleString());
            $('#sClk').text((res.summary.clicks || 0).toLocaleString());
            $('#sCtr').text((res.summary.ctr || 0) + '%');
            $('#sCompanies').text(res.summary.companies || 0);
            $('#sAvgImp').text(res.summary.avg_daily_impressions || 0);
            $('#sAvgClk').text(res.summary.avg_daily_clicks || 0);
        }

        if (res.series) {
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: res.series.labels || [],
                    datasets: [
                        { label: 'Impressions', data: res.series.impressions || [], borderColor: '#007bff', tension: .3 },
                        { label: 'Clicks', data: res.series.clicks || [], borderColor: '#28a745', tension: .3 },
                        { label: 'CTR %', data: res.series.ctr || [], borderColor: '#ffc107', tension: .3, yAxisID: 'y1' },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                    }
                }
            });
        }

        const $pos = $('#byPositionBody').empty();
        (res.by_position || []).forEach(r => {
            $pos.append(`<tr><td>${esc(r.label)}</td><td class="text-right">${r.impressions}</td><td class="text-right">${r.clicks}</td><td class="text-right">${r.ctr}%</td></tr>`);
        });
        if (!(res.by_position || []).length && res.source !== 'adsense') $pos.append('<tr><td colspan="4" class="text-muted p-3">No position data.</td></tr>');

        const $co = $('#byCompanyBody').empty();
        (res.by_company || []).forEach(r => {
            $co.append(`<tr><td><a href="#" class="js-filter-company" data-id="${r.advertiser_id}">${esc(r.company)}</a></td><td class="text-right">${r.impressions}</td><td class="text-right">${r.clicks}</td><td class="text-right">${r.ctr}%</td></tr>`);
        });
        if (!(res.by_company || []).length && res.source !== 'adsense') $co.append('<tr><td colspan="4" class="text-muted p-3">No company data.</td></tr>');

        const $det = $('#detailBody').empty();
        (res.table || []).forEach(r => {
            $det.append(`<tr>
                <td>${esc(r.date)}</td>
                <td>${esc(r.company)}</td>
                <td>${esc(r.advertisement)}</td>
                <td>${esc(r.position_label)}</td>
                <td class="text-right">${r.impressions}</td>
                <td class="text-right">${r.clicks}</td>
                <td class="text-right">${r.ctr}%</td>
            </tr>`);
        });
        if (!(res.table || []).length && res.source !== 'adsense') $det.append('<tr><td colspan="7" class="text-muted p-3">No daily rows.</td></tr>');

        // AdSense block
        if (res.adsense) {
            const a = res.adsense;
            $('#asImp').text((a.summary.impressions || 0).toLocaleString());
            $('#asUnit').text((a.summary.unit_impressions || 0).toLocaleString());
            $('#asAdRow').text((a.summary.advertisement_impressions || 0).toLocaleString());
            $('#asAvg').text(a.summary.avg_daily_impressions || 0);
            $('#adsenseNote').text(a.summary.note || '');

            if (adsenseChart) adsenseChart.destroy();
            adsenseChart = new Chart(document.getElementById('adsenseChart'), {
                type: 'bar',
                data: {
                    labels: a.series.labels || [],
                    datasets: [{ label: 'AdSense Impressions', data: a.series.impressions || [], backgroundColor: 'rgba(220,53,69,.75)' }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            const $ap = $('#adsensePosBody').empty();
            (a.by_position || []).forEach(r => {
                $ap.append(`<tr><td>${esc(r.label)}</td><td class="text-right">${r.impressions}</td></tr>`);
            });
            if (!(a.by_position || []).length) $ap.append('<tr><td colspan="2" class="text-muted p-3">No AdSense impressions yet.</td></tr>');

            const $ad = $('#adsenseDetailBody').empty();
            (a.table || []).forEach(r => {
                $ad.append(`<tr><td>${esc(r.date)}</td><td>${esc(r.position_label)}</td><td>${esc(r.source)}</td><td class="text-right">${r.impressions}</td></tr>`);
            });
            if (!(a.table || []).length) $ad.append('<tr><td colspan="4" class="text-muted p-3">No rows.</td></tr>');

            const $aa = $('#adsenseAdsBody').empty();
            (a.ads || []).forEach(r => {
                $aa.append(`<tr><td>${esc(r.name)}</td><td>${esc(r.position_label)}</td><td class="text-right">${r.impressions}</td><td class="text-right">${r.clicks}</td></tr>`);
            });
            if (!(a.ads || []).length) $aa.append('<tr><td colspan="4" class="text-muted p-3">No AdSense-type ads in catalog.</td></tr>');
        }

        $('#btnExportExcel').attr('href', '{{ route('admin.ad-reports.export.excel') }}?' + qs());
        $('#btnExportPdf').attr('href', '{{ route('admin.ad-reports.export.pdf') }}?' + qs());
    });
}

$('#rangePreset').on('change', function () {
    $('.custom-dates').toggleClass('d-none', $(this).val() !== 'custom');
    if ($(this).val() !== 'custom') loadReport();
});
$('#filterSource, #filterCompany, #filterPosition').on('change', loadReport);
$('#rangeFrom, #rangeTo').on('change', function () {
    if ($('#rangePreset').val() === 'custom') loadReport();
});
$('#btnResetFilters').on('click', function () {
    $('#filterSource').val('all');
    $('#filterCompany').val('').trigger('change.select2');
    $('#filterPosition').val('');
    $('#rangePreset').val('last_30');
    $('#rangeFrom, #rangeTo').val('');
    $('.custom-dates').addClass('d-none');
    loadReport();
});
$(document).on('click', '.js-filter-company', function (e) {
    e.preventDefault();
    $('#filterCompany').val($(this).data('id')).trigger('change');
});
loadReport();
</script>
@endpush

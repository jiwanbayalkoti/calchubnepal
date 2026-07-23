<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Advertiser Portal</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.13.8/b-2.4.2/r-2.5.0/datatables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <style>
        :root { --adv-blue: #1e5bb8; --adv-blue-dark: #154a96; }
        .main-sidebar { background: linear-gradient(180deg, #0d47a1 0%, #1565c0 100%) !important; }
        .brand-link { border-bottom: 1px solid rgba(255,255,255,.12) !important; }
        .nav-sidebar .nav-link.active { background: rgba(255,255,255,.18) !important; }
        .content-wrapper { background: #f4f7fb; }
        .small-box { border-radius: .5rem; }
        .card { border-radius: .5rem; border: 0; box-shadow: 0 1px 4px rgba(21,74,150,.08); }
        .btn-primary { background: var(--adv-blue); border-color: var(--adv-blue); }
        .btn-primary:hover { background: var(--adv-blue-dark); border-color: var(--adv-blue-dark); }
        .navbar-white { border-bottom: 1px solid #e6eef8; }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link text-muted">Advertiser Portal</span>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle"></i>
                    <span class="d-none d-md-inline ml-1">{{ auth()->user()?->advertiser?->company_name ?? auth()->user()?->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('advertiser.profile.edit') }}" class="dropdown-item"><i class="fas fa-user mr-2"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('advertiser.dashboard') }}" class="brand-link">
            <i class="fas fa-bullhorn brand-image ml-2" style="font-size:1.3rem;color:#fff;"></i>
            <span class="brand-text font-weight-light">Calchub Ads</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                @php($current = request()->route()?->getName())
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('advertiser.dashboard') }}" class="nav-link {{ str($current)->startsWith('advertiser.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('advertiser.advertisements.index') }}" class="nav-link {{ str($current)->startsWith('advertiser.advertisements') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-ad"></i><p>My Advertisements</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('advertiser.reports.index') }}" class="nav-link {{ str($current)->startsWith('advertiser.reports') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i><p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('advertiser.profile.edit') }}" class="nav-link {{ str($current)->startsWith('advertiser.profile') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i><p>Profile</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="nav-link btn btn-link text-left text-white w-100" style="border:0;">
                                <i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">@yield('page-title', 'Dashboard')</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('advertiser.dashboard') }}">Home</a></li>
                            @stack('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer text-sm">
        <strong>CalchubNepal Advertiser Portal</strong> — read-only performance monitoring.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/b-2.4.2/r-2.5.0/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<script>
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    });
    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000 };
});
</script>
@stack('scripts')
</body>
</html>

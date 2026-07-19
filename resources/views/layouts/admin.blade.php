<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }} Admin</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- AdminLTE 3 requires Bootstrap 4 (data-toggle / .modal() jQuery API). Do not load Bootstrap 5 here. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- DataTables (Bootstrap 4 theme) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs4/dt-1.13.8/b-2.4.2/r-2.5.0/datatables.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

    <style>
        .content-wrapper { min-height: calc(100vh - 106px); }
        .small-box .icon { opacity: .35; }
        .brand-link .brand-text { font-weight: 700; }
        .table-actions .btn { margin-right: .2rem; }
        .required-star { color: #dc3545; }
        .form-loading-overlay {
            position: absolute; inset: 0; background: rgba(255,255,255,.6);
            display: flex; align-items: center; justify-content: center; z-index: 5;
        }
        body.dark-mode .form-loading-overlay { background: rgba(0,0,0,.55); }
        /* Ensure modal backdrop/stacking works above AdminLTE wrappers */
        .modal-backdrop { z-index: 1040; }
        .modal { z-index: 1050; }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed{{ request()->cookie('admin_dark_mode') === '1' ? ' dark-mode' : '' }}">
<div class="wrapper">

    {{-- Top Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ url('/') }}" target="_blank" class="nav-link">View Site</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#" id="darkModeToggle" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle"></i>
                    <span class="d-none d-md-inline ml-1">{{ auth()->user()?->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user-cog mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <i class="fas fa-calculator brand-image ml-2" style="font-size:1.4rem;color:#fff;"></i>
            <span class="brand-text">{{ config('app.name') }}</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                @php($current = request()->route()?->getName())
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ str($current)->startsWith('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item {{ str($current)->startsWith('admin.calculators') || str($current)->startsWith('admin.categories') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ str($current)->startsWith('admin.calculators') || str($current)->startsWith('admin.categories') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-square-root-alt"></i>
                            <p>Calculators <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.calculators.index') }}" class="nav-link {{ str($current)->startsWith('admin.calculators') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i><p>All Calculators</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ str($current)->startsWith('admin.categories') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i><p>Categories</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item {{ str($current)->startsWith('admin.blog-posts') || str($current)->startsWith('admin.seo-pages') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ str($current)->startsWith('admin.blog-posts') || str($current)->startsWith('admin.seo-pages') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-newspaper"></i>
                            <p>Content <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.blog-posts.index') }}" class="nav-link {{ str($current)->startsWith('admin.blog-posts') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i><p>Blog Posts</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.seo-pages.index') }}" class="nav-link {{ str($current)->startsWith('admin.seo-pages') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i><p>SEO Pages</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.advertisements.index') }}" class="nav-link {{ str($current)->startsWith('admin.advertisements') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-ad"></i>
                            <p>Advertisements</p>
                        </a>
                    </li>

                    <li class="nav-header">USERS &amp; ACCESS</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ str($current)->startsWith('admin.users') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ str($current)->startsWith('admin.roles') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Roles &amp; Permissions</p>
                        </a>
                    </li>

                    <li class="nav-header">BILLING</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.subscription-plans.index') }}" class="nav-link {{ str($current)->startsWith('admin.subscription-plans') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p>Subscription Plans</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.api-keys.index') }}" class="nav-link {{ str($current)->startsWith('admin.api-keys') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>API Keys</p>
                        </a>
                    </li>

                    <li class="nav-header">SUPPORT</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.feedback.index') }}" class="nav-link {{ str($current)->startsWith('admin.feedback') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-comment-dots"></i>
                            <p>Feedback</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.contact-messages.index') }}" class="nav-link {{ str($current)->startsWith('admin.contact-messages') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-envelope"></i>
                            <p>Contact Messages</p>
                        </a>
                    </li>

                    <li class="nav-header">AI &amp; ANALYTICS</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.ai-prompts.index') }}" class="nav-link {{ str($current)->startsWith('admin.ai-prompts') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-robot"></i>
                            <p>AI Prompts</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ str($current)->startsWith('admin.analytics') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Analytics</p>
                        </a>
                    </li>

                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ str($current)->startsWith('admin.settings') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Content Wrapper --}}
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
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

    <footer class="main-footer">
        <strong>&copy; {{ date('Y') }} {{ config('app.name') }}.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Admin Panel</b>
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- Bootstrap 4 (required by AdminLTE 3 for modals, dropdowns, data-toggle) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/v/bs4/dt-1.13.8/b-2.4.2/r-2.5.0/datatables.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<!-- Reusable AJAX CRUD helper -->
<script src="{{ asset('js/admin-crud.js') }}"></script>

<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
        };

        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // Re-init Select2 inside modals so dropdowns render above the modal
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('select.select2').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
                $(this).select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $(this).closest('.modal') });
            });
        });

        $('#darkModeToggle').on('click', function (e) {
            e.preventDefault();
            const isDark = $('body').toggleClass('dark-mode').hasClass('dark-mode');
            document.cookie = 'admin_dark_mode=' + (isDark ? '1' : '0') + ';path=/;max-age=' + 60 * 60 * 24 * 365;
        });

        @if(session('success'))
            toastr.success(@json(session('success')));
        @endif
        @if(session('error'))
            toastr.error(@json(session('error')));
        @endif
    });
</script>

@stack('scripts')
</body>
</html>

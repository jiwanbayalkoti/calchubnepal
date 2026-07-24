@extends('layouts.public')

@push('schemas')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($webAppSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
<section class="section atmosphere pt-4 pb-5">
    <div class="container">
        <div class="section-heading mb-4">
            <span class="eyebrow"><i class="bi bi-person-vcard"></i> Free tool</span>
            <h1 class="h2 mb-2">{{ __('vc.title') }}</h1>
            <p class="text-muted-custom mb-0">{{ __('vc.subtitle') }}</p>
        </div>

        <div class="row g-4 align-items-start" id="visitingCardApp"
             data-preview-url="{{ route('visiting-card-designer.preview') }}"
             data-download-url="{{ route('visiting-card-designer.download') }}"
             data-logo-url="{{ route('visiting-card-designer.logo') }}"
             data-logo-uploading="{{ __('vc.logo_uploading') }}"
             data-logo-ready="{{ __('vc.logo_ready') }}">
            <div class="col-lg-7">
                <form id="visitingCardForm" class="vc-panel card-surface p-4" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="logo_token" id="vc_logo_token" value="">

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.template_heading') }} <span class="text-muted fw-normal">({{ count($templates) }})</span></h2>
                    <div class="vc-template-filters mb-2" id="vcTemplateFilters">
                        <button type="button" class="btn btn-sm btn-brand vc-filter-btn is-active" data-filter="all">{{ __('vc.filter_all') }}</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-sm btn-outline-brand vc-filter-btn" data-filter="{{ $category }}">
                                {{ __('vc.filter_'.strtolower($category)) }}
                            </button>
                        @endforeach
                    </div>
                    <div class="vc-template-grid mb-4" role="radiogroup" aria-label="{{ __('vc.template_heading') }}">
                        @foreach($templates as $template)
                            <label class="vc-template-card" data-category="{{ $template['category'] }}">
                                <input type="radio" name="template" value="{{ $template['value'] }}" class="visually-hidden js-vc-template"
                                       data-primary="{{ $template['colors']['primary'] }}"
                                       data-secondary="{{ $template['colors']['secondary'] }}"
                                       data-text="{{ $template['colors']['text'] }}"
                                       data-background="{{ $template['colors']['background'] }}"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <span class="vc-template-card__body">
                                    <span class="vc-template-thumb vc-thumb-{{ $template['value'] }}" aria-hidden="true"></span>
                                    <span class="vc-template-meta">
                                        <span class="vc-template-name">{{ $template['label'] }}</span>
                                        <span class="vc-template-cat">{{ $template['category'] }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.details_heading') }}</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="vc_full_name">{{ __('vc.field_name') }}</label>
                            <input type="text" id="vc_full_name" name="full_name" class="form-control" placeholder="Ram Bahadur Shrestha" autocomplete="name">
                            <div class="invalid-feedback" data-error-for="full_name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vc_job_title">{{ __('vc.field_title') }}</label>
                            <input type="text" id="vc_job_title" name="job_title" class="form-control" placeholder="Marketing Manager" autocomplete="organization-title">
                            <div class="invalid-feedback" data-error-for="job_title"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vc_company">{{ __('vc.field_company') }}</label>
                            <input type="text" id="vc_company" name="company" class="form-control" placeholder="CalchubNepal" autocomplete="organization">
                            <div class="invalid-feedback" data-error-for="company"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vc_phone">{{ __('vc.field_phone') }}</label>
                            <input type="tel" id="vc_phone" name="phone" class="form-control" placeholder="+97798XXXXXXXX" autocomplete="tel">
                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vc_email">{{ __('vc.field_email') }}</label>
                            <input type="email" id="vc_email" name="email" class="form-control" placeholder="hello@example.com" autocomplete="email">
                            <div class="invalid-feedback" data-error-for="email"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="vc_website">{{ __('vc.field_website') }}</label>
                            <input type="text" id="vc_website" name="website" class="form-control" placeholder="https://example.com" autocomplete="url">
                            <div class="invalid-feedback" data-error-for="website"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="vc_address">{{ __('vc.field_address') }}</label>
                            <input type="text" id="vc_address" name="address" class="form-control" placeholder="Kathmandu, Nepal" autocomplete="street-address">
                            <div class="invalid-feedback" data-error-for="address"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="vc_tagline">{{ __('vc.field_tagline') }}</label>
                            <input type="text" id="vc_tagline" name="tagline" class="form-control" placeholder="{{ __('vc.field_tagline_ph') }}">
                            <div class="invalid-feedback" data-error-for="tagline"></div>
                        </div>
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.style_heading') }}</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="vc_primary">{{ __('vc.primary') }}</label>
                            <input type="color" id="vc_primary" name="primary_color" class="form-control form-control-color w-100" value="#0B6E4F">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="vc_secondary">{{ __('vc.secondary') }}</label>
                            <input type="color" id="vc_secondary" name="secondary_color" class="form-control form-control-color w-100" value="#F4A259">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="vc_text">{{ __('vc.text_color') }}</label>
                            <input type="color" id="vc_text" name="text_color" class="form-control form-control-color w-100" value="#1A1A1A">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="vc_bg">{{ __('vc.background') }}</label>
                            <input type="color" id="vc_bg" name="background_color" class="form-control form-control-color w-100" value="#FFFFFF">
                        </div>
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.logo_heading') }}</h2>
                    <div class="vc-logo-box mb-4" id="vcLogoBox">
                        <input type="file" id="vc_logo" name="logo" class="visually-hidden" accept="image/png,image/jpeg,image/webp,image/gif">
                        <div class="vc-logo-drop" id="vcLogoDrop" role="button" tabindex="0" aria-controls="vc_logo">
                            <div class="vc-logo-preview d-none" id="vcLogoPreviewWrap">
                                <img src="" alt="{{ __('vc.logo') }}" id="vcLogoPreview" width="72" height="72">
                            </div>
                            <div class="vc-logo-placeholder" id="vcLogoPlaceholder">
                                <i class="bi bi-building" aria-hidden="true"></i>
                                <strong>{{ __('vc.logo_drop_title') }}</strong>
                                <span>{{ __('vc.logo_hint') }}</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-brand" id="vcLogoPickBtn">{{ __('vc.logo_choose') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="vcLogoRemoveBtn">{{ __('vc.logo_remove') }}</button>
                            <span class="small text-muted-custom" id="vcLogoStatus"></span>
                        </div>
                        <div class="invalid-feedback" data-error-for="logo"></div>
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.qr_heading') }}</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="vc_include_qr" name="include_qr">
                                <label class="form-check-label" for="vc_include_qr">{{ __('vc.include_qr') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="vcQrTargetWrap">
                            <label class="form-label" for="vc_qr_target">{{ __('vc.qr_target') }}</label>
                            <select id="vc_qr_target" name="qr_target" class="form-select">
                                <option value="website">{{ __('vc.qr_website') }}</option>
                                <option value="vcard">{{ __('vc.qr_vcard') }}</option>
                                <option value="email">{{ __('vc.qr_email') }}</option>
                                <option value="phone">{{ __('vc.qr_phone') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-brand" id="vcPreviewBtn">{{ __('vc.refresh') }}</button>
                        <button type="button" class="btn btn-outline-brand" id="vcResetBtn">{{ __('vc.reset') }}</button>
                    </div>
                </form>
            </div>

            <div class="col-lg-5">
                <div class="vc-preview-panel card-surface p-4 sticky-lg-top" style="top: 5.5rem;">
                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('vc.preview_heading') }}</h2>
                    <div class="vc-preview-frame mb-3">
                        <div class="vc-preview-placeholder" id="vcPreviewPlaceholder">
                            <i class="bi bi-person-vcard"></i>
                            <span>{{ __('vc.preview_empty') }}</span>
                        </div>
                        <img src="" alt="Visiting card preview" class="vc-preview-image d-none" id="vcPreviewImage" width="1050" height="600">
                        <div class="vc-preview-loading d-none" id="vcPreviewLoading">
                            <div class="spinner-border text-success" role="status" aria-hidden="true"></div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-brand" id="vcDownloadPng" disabled>{{ __('vc.download_png') }}</button>
                        <button type="button" class="btn btn-outline-brand" id="vcDownloadPdf" disabled>{{ __('vc.download_pdf') }}</button>
                    </div>
                    <p class="small text-muted-custom mt-3 mb-0">{{ __('vc.print_hint') }}</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <div class="card-surface p-4">
                    <h2 class="h5 mb-3">{{ __('vc.faq_heading') }}</h2>
                    <div class="accordion" id="vcFaq">
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#vcFaq1">{{ __('vc.faq1_q') }}</button>
                            </h3>
                            <div id="vcFaq1" class="accordion-collapse collapse show" data-bs-parent="#vcFaq">
                                <div class="accordion-body">{{ __('vc.faq1_a') }}</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vcFaq2">{{ __('vc.faq2_q') }}</button>
                            </h3>
                            <div id="vcFaq2" class="accordion-collapse collapse" data-bs-parent="#vcFaq">
                                <div class="accordion-body">{{ __('vc.faq2_a') }}</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vcFaq3">{{ __('vc.faq3_q') }}</button>
                            </h3>
                            <div id="vcFaq3" class="accordion-collapse collapse" data-bs-parent="#vcFaq">
                                <div class="accordion-body">{{ __('vc.faq3_a') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-surface p-4">
                    <h2 class="h6 mb-2">{{ __('vc.related') }}</h2>
                    <a href="{{ route('qr-code-generator') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                        <i class="bi bi-qr-code"></i>
                        <span>{{ __('nav.qr') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/visiting-card-designer.js') }}?v={{ @filemtime(public_path('js/visiting-card-designer.js')) ?: '1' }}" defer></script>
@endpush

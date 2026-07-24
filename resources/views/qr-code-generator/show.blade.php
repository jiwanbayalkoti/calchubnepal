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
            <span class="eyebrow"><i class="bi bi-qr-code"></i> Free tool</span>
            <h1 class="h2 mb-2">{{ __('qr.title') }}</h1>
            <p class="text-muted-custom mb-0">{{ __('qr.subtitle') }}</p>
        </div>

        <div class="row g-4 align-items-start" id="qrGeneratorApp"
             data-preview-url="{{ route('qr-code-generator.preview') }}"
             data-download-url="{{ route('qr-code-generator.download') }}"
             data-logo-url="{{ route('qr-code-generator.logo') }}"
             data-recent-url="{{ route('qr-code-generator.recent') }}"
             data-saved-url="{{ route('qr-code-generator.saved') }}"
             data-save-url="{{ url('/qr-code-generator') }}"
             data-dynamic-url="{{ $isAuthenticated ? route('qr-code-generator.dynamic') : '' }}"
             data-login-url="{{ route('login') }}"
             data-authenticated="{{ $isAuthenticated ? '1' : '0' }}">
            <div class="col-lg-7">
                <form id="qrGeneratorForm" class="qr-panel card-surface p-4 p-md-4" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="logo_token" id="qr_logo_token" value="">
                    <input type="hidden" name="save_history" id="qr_save_history" value="0">

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('qr.type_heading') }}</h2>
                    <div class="qr-type-grid mb-4" role="radiogroup" aria-label="{{ __('qr.type_heading') }}">
                        @foreach($types as $type)
                            <label class="qr-type-card">
                                <input type="radio" name="type" value="{{ $type['value'] }}" class="visually-hidden js-qr-type"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <span class="qr-type-card__body">
                                    <i class="bi {{ $type['icon'] }}"></i>
                                    <span>{{ $type['label'] }}</span>
                                    @if(($type['phase'] ?? 1) >= 3)
                                        <span class="qr-type-badge">{{ __('qr.phase3_badge') }}</span>
                                    @elseif(($type['phase'] ?? 1) >= 2)
                                        <span class="qr-type-badge">{{ __('qr.phase2_badge') }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('qr.input_heading') }}</h2>
                    <div class="qr-fields mb-4">
                        <div class="qr-field-group" data-types="url">
                            <label class="form-label" for="qr_url">{{ __('qr.field_url') }}</label>
                            <input type="text" id="qr_url" name="input[url]" class="form-control" placeholder="https://example.com" autocomplete="url">
                            <div class="invalid-feedback" data-error-for="input.url"></div>
                        </div>

                        <div class="qr-field-group d-none" data-types="text">
                            <label class="form-label" for="qr_text">{{ __('qr.field_text') }}</label>
                            <textarea id="qr_text" name="input[text]" class="form-control" rows="4" placeholder="{{ __('qr.field_text_ph') }}"></textarea>
                            <div class="invalid-feedback" data-error-for="input.text"></div>
                        </div>

                        <div class="qr-field-group d-none" data-types="email">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="qr_email">{{ __('qr.field_email') }}</label>
                                    <input type="email" id="qr_email" name="input[email]" class="form-control" placeholder="hello@example.com">
                                    <div class="invalid-feedback" data-error-for="input.email"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_email_subject">{{ __('qr.field_subject') }}</label>
                                    <input type="text" id="qr_email_subject" name="input[subject]" class="form-control">
                                    <div class="invalid-feedback" data-error-for="input.subject"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_email_body">{{ __('qr.field_body') }}</label>
                                    <input type="text" id="qr_email_body" name="input[body]" class="form-control">
                                    <div class="invalid-feedback" data-error-for="input.body"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="phone">
                            <label class="form-label" for="qr_phone">{{ __('qr.field_phone') }}</label>
                            <input type="tel" id="qr_phone" name="input[phone]" class="form-control" placeholder="+97798XXXXXXXX">
                            <div class="invalid-feedback" data-error-for="input.phone"></div>
                        </div>

                        <div class="qr-field-group d-none" data-types="sms">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label" for="qr_sms_phone">{{ __('qr.field_phone') }}</label>
                                    <input type="tel" id="qr_sms_phone" name="input[phone]" class="form-control" placeholder="+97798XXXXXXXX" disabled>
                                    <div class="invalid-feedback" data-error-for="input.phone"></div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label" for="qr_sms_message">{{ __('qr.field_sms_message') }}</label>
                                    <input type="text" id="qr_sms_message" name="input[message]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.message"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="whatsapp">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label" for="qr_wa_phone">{{ __('qr.field_wa_phone') }}</label>
                                    <input type="tel" id="qr_wa_phone" name="input[phone]" class="form-control" placeholder="97798XXXXXXXX" disabled>
                                    <div class="invalid-feedback" data-error-for="input.phone"></div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label" for="qr_wa_message">{{ __('qr.field_wa_message') }}</label>
                                    <input type="text" id="qr_wa_message" name="input[message]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.message"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="wifi">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_wifi_ssid">{{ __('qr.field_ssid') }}</label>
                                    <input type="text" id="qr_wifi_ssid" name="input[ssid]" class="form-control" autocomplete="off" disabled>
                                    <div class="invalid-feedback" data-error-for="input.ssid"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_wifi_password">{{ __('qr.field_password') }}</label>
                                    <input type="text" id="qr_wifi_password" name="input[password]" class="form-control" autocomplete="off" disabled>
                                    <div class="invalid-feedback" data-error-for="input.password"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_wifi_encryption">{{ __('qr.field_encryption') }}</label>
                                    <select id="qr_wifi_encryption" name="input[encryption]" class="form-select" disabled>
                                        <option value="WPA">WPA/WPA2</option>
                                        <option value="WEP">WEP</option>
                                        <option value="NOPASS">{{ __('qr.encryption_nopass') }}</option>
                                    </select>
                                    <div class="invalid-feedback" data-error-for="input.encryption"></div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="qr_wifi_hidden" name="input[hidden]" value="1" disabled>
                                        <label class="form-check-label" for="qr_wifi_hidden">{{ __('qr.field_hidden') }}</label>
                                    </div>
                                    <div class="invalid-feedback" data-error-for="input.hidden"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="maps">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_maps_provider">{{ __('qr.field_map_provider') }}</label>
                                    <select id="qr_maps_provider" name="input[provider]" class="form-select" disabled>
                                        <option value="google">Google Maps</option>
                                        <option value="apple">Apple Maps</option>
                                        <option value="waze">Waze</option>
                                        <option value="osm">OpenStreetMap</option>
                                        <option value="geo">Geo URI</option>
                                    </select>
                                    <div class="invalid-feedback" data-error-for="input.provider"></div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="qr_maps_query">{{ __('qr.field_maps_query') }}</label>
                                    <input type="text" id="qr_maps_query" name="input[query]" class="form-control" placeholder="{{ __('qr.field_maps_query_ph') }}" disabled>
                                    <div class="invalid-feedback" data-error-for="input.query"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_maps_lat">{{ __('qr.field_lat') }}</label>
                                    <input type="number" step="any" id="qr_maps_lat" name="input[lat]" class="form-control" placeholder="27.7172" disabled>
                                    <div class="invalid-feedback" data-error-for="input.lat"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_maps_lng">{{ __('qr.field_lng') }}</label>
                                    <input type="number" step="any" id="qr_maps_lng" name="input[lng]" class="form-control" placeholder="85.3240" disabled>
                                    <div class="invalid-feedback" data-error-for="input.lng"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="location">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_loc_lat">{{ __('qr.field_lat') }}</label>
                                    <input type="number" step="any" id="qr_loc_lat" name="input[lat]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.lat"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_loc_lng">{{ __('qr.field_lng') }}</label>
                                    <input type="number" step="any" id="qr_loc_lng" name="input[lng]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.lng"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_loc_alt">{{ __('qr.field_altitude') }}</label>
                                    <input type="number" step="any" id="qr_loc_alt" name="input[altitude]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.altitude"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="vcard">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_first">{{ __('qr.field_first_name') }}</label>
                                    <input type="text" id="qr_vcard_first" name="input[first_name]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.first_name"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_last">{{ __('qr.field_last_name') }}</label>
                                    <input type="text" id="qr_vcard_last" name="input[last_name]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.last_name"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_org">{{ __('qr.field_organization') }}</label>
                                    <input type="text" id="qr_vcard_org" name="input[organization]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.organization"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_title">{{ __('qr.field_job_title') }}</label>
                                    <input type="text" id="qr_vcard_title" name="input[title]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.title"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_phone">{{ __('qr.field_phone') }}</label>
                                    <input type="tel" id="qr_vcard_phone" name="input[phone]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.phone"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_email">{{ __('qr.field_email') }}</label>
                                    <input type="email" id="qr_vcard_email" name="input[email]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.email"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_url">{{ __('qr.field_website') }}</label>
                                    <input type="text" id="qr_vcard_url" name="input[url]" class="form-control" placeholder="https://" disabled>
                                    <div class="invalid-feedback" data-error-for="input.url"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_vcard_address">{{ __('qr.field_address') }}</label>
                                    <input type="text" id="qr_vcard_address" name="input[address]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.address"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="event">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="qr_event_title">{{ __('qr.field_event_title') }}</label>
                                    <input type="text" id="qr_event_title" name="input[title]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.title"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_event_start">{{ __('qr.field_event_start') }}</label>
                                    <input type="datetime-local" id="qr_event_start" name="input[start]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.start"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_event_end">{{ __('qr.field_event_end') }}</label>
                                    <input type="datetime-local" id="qr_event_end" name="input[end]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.end"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="qr_event_location">{{ __('qr.field_event_location') }}</label>
                                    <input type="text" id="qr_event_location" name="input[location]" class="form-control" disabled>
                                    <div class="invalid-feedback" data-error-for="input.location"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="qr_event_desc">{{ __('qr.field_event_description') }}</label>
                                    <textarea id="qr_event_desc" name="input[description]" class="form-control" rows="3" disabled></textarea>
                                    <div class="invalid-feedback" data-error-for="input.description"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="social">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_social_network">{{ __('qr.field_network') }}</label>
                                    <select id="qr_social_network" name="input[network]" class="form-select" disabled>
                                        <option value="facebook">Facebook</option>
                                        <option value="instagram" selected>Instagram</option>
                                        <option value="twitter">X / Twitter</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="tiktok">TikTok</option>
                                        <option value="github">GitHub</option>
                                    </select>
                                    <div class="invalid-feedback" data-error-for="input.network"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_social_username">{{ __('qr.field_username') }}</label>
                                    <input type="text" id="qr_social_username" name="input[username]" class="form-control" placeholder="@username" disabled>
                                    <div class="invalid-feedback" data-error-for="input.username"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_social_url">{{ __('qr.field_profile_url') }}</label>
                                    <input type="text" id="qr_social_url" name="input[url]" class="form-control" placeholder="https://" disabled>
                                    <div class="invalid-feedback" data-error-for="input.url"></div>
                                </div>
                            </div>
                        </div>

                        <div class="qr-field-group d-none" data-types="bank">
                            <p class="small text-muted-custom mb-3">{{ __('qr.bank_help') }}</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_bank_account_name">{{ __('qr.field_account_name') }}</label>
                                    <input type="text" id="qr_bank_account_name" name="input[account_name]" class="form-control" autocomplete="name" disabled>
                                    <div class="invalid-feedback" data-error-for="input.account_name"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_bank_name">{{ __('qr.field_bank_name') }}</label>
                                    <input type="text" id="qr_bank_name" name="input[bank_name]" class="form-control" list="qr_bank_name_list" placeholder="{{ __('qr.field_bank_name_ph') }}" disabled>
                                    <datalist id="qr_bank_name_list">
                                        <option value="Nabil Bank"></option>
                                        <option value="Nepal Investment Mega Bank"></option>
                                        <option value="Global IME Bank"></option>
                                        <option value="NIC Asia Bank"></option>
                                        <option value="Himalayan Bank"></option>
                                        <option value="Standard Chartered Bank Nepal"></option>
                                        <option value="Everest Bank"></option>
                                        <option value="NMB Bank"></option>
                                        <option value="Machhapuchchhre Bank"></option>
                                        <option value="Prabhu Bank"></option>
                                        <option value="Siddhartha Bank"></option>
                                        <option value="Kumari Bank"></option>
                                        <option value="Laxmi Sunrise Bank"></option>
                                        <option value="Citizens Bank International"></option>
                                        <option value="Sanima Bank"></option>
                                        <option value="Nepal Bank Limited"></option>
                                        <option value="Rastriya Banijya Bank"></option>
                                        <option value="Agriculture Development Bank"></option>
                                    </datalist>
                                    <div class="invalid-feedback" data-error-for="input.bank_name"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_bank_account_number">{{ __('qr.field_account_number') }}</label>
                                    <input type="text" id="qr_bank_account_number" name="input[account_number]" class="form-control" inputmode="numeric" autocomplete="off" disabled>
                                    <div class="invalid-feedback" data-error-for="input.account_number"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="qr_bank_branch">{{ __('qr.field_branch') }}</label>
                                    <input type="text" id="qr_bank_branch" name="input[branch]" class="form-control" placeholder="{{ __('qr.field_branch_ph') }}" disabled>
                                    <div class="invalid-feedback" data-error-for="input.branch"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_bank_account_type">{{ __('qr.field_account_type') }}</label>
                                    <select id="qr_bank_account_type" name="input[account_type]" class="form-select" disabled>
                                        <option value="">{{ __('qr.account_type_none') }}</option>
                                        <option value="Saving">{{ __('qr.account_type_saving') }}</option>
                                        <option value="Current">{{ __('qr.account_type_current') }}</option>
                                        <option value="Fixed Deposit">{{ __('qr.account_type_fd') }}</option>
                                        <option value="Other">{{ __('qr.account_type_other') }}</option>
                                    </select>
                                    <div class="invalid-feedback" data-error-for="input.account_type"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_bank_swift">{{ __('qr.field_swift') }}</label>
                                    <input type="text" id="qr_bank_swift" name="input[swift_code]" class="form-control" placeholder="e.g. NARBNPKA" maxlength="20" disabled>
                                    <div class="invalid-feedback" data-error-for="input.swift_code"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="qr_bank_remarks">{{ __('qr.field_remarks') }}</label>
                                    <input type="text" id="qr_bank_remarks" name="input[remarks]" class="form-control" placeholder="{{ __('qr.field_remarks_ph') }}" disabled>
                                    <div class="invalid-feedback" data-error-for="input.remarks"></div>
                                </div>
                            </div>
                        </div>

                        @include('qr-code-generator.partials.phase3-fields')
                    </div>

                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('qr.custom_heading') }}</h2>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="qr_size">{{ __('qr.size') }}</label>
                            <select id="qr_size" name="size" class="form-select">
                                @foreach($sizes as $size)
                                    <option value="{{ $size }}" @selected($size === 256)>{{ $size }}px</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="qr_error">{{ __('qr.error_correction') }}</label>
                            <select id="qr_error" name="error_correction" class="form-select">
                                @foreach($errorLevels as $level)
                                    <option value="{{ $level['value'] }}" @selected($level['value'] === 'M')>{{ $level['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_fg">{{ __('qr.foreground') }}</label>
                            <input type="color" id="qr_fg" name="foreground" class="form-control form-control-color w-100" value="#0B6E4F">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_bg">{{ __('qr.background') }}</label>
                            <input type="color" id="qr_bg" name="background" class="form-control form-control-color w-100" value="#FFFFFF">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_margin">{{ __('qr.margin') }}</label>
                            <input type="number" id="qr_margin" name="margin" class="form-control" min="0" max="64" value="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_module_style">{{ __('qr.module_style') }}</label>
                            <select id="qr_module_style" name="module_style" class="form-select">
                                @foreach($moduleStyles as $style)
                                    <option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_eye_style">{{ __('qr.eye_style') }}</label>
                            <select id="qr_eye_style" name="eye_style" class="form-select">
                                @foreach($eyeStyles as $style)
                                    <option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="qr_frame_style">{{ __('qr.frame_style') }}</label>
                            <select id="qr_frame_style" name="frame_style" class="form-select">
                                @foreach($frameStyles as $style)
                                    <option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="qr_frame_label">{{ __('qr.frame_label') }}</label>
                            <input type="text" id="qr_frame_label" name="frame_label" class="form-control" maxlength="60" placeholder="{{ __('qr.frame_label_ph') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="qr_logo">{{ __('qr.logo') }}</label>
                            <input type="file" id="qr_logo" name="logo" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
                            <div class="form-text" id="qrLogoStatus">{{ __('qr.logo_hint') }}</div>
                            <div class="invalid-feedback" data-error-for="logo"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="qr_logo_size">{{ __('qr.logo_size') }}</label>
                            <input type="number" id="qr_logo_size" name="logo_size" class="form-control" min="24" max="200" value="64">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-brand" id="qrGenerateBtn">
                            <i class="bi bi-lightning-charge-fill me-1"></i> {{ __('qr.generate') }}
                        </button>
                        <button type="button" class="btn btn-outline-brand" id="qrResetBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('qr.reset') }}
                        </button>
                    </div>

                    <div class="qr-dynamic-box mt-4 p-3 border rounded-3" id="qrDynamicBox">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h2 class="h6 mb-1">{{ __('qr.dynamic_heading') }}</h2>
                                <p class="small text-muted-custom mb-0">{{ __('qr.dynamic_help') }}</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border">Phase 3</span>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="qr_is_dynamic" {{ $isAuthenticated ? '' : 'disabled' }}>
                            <label class="form-check-label" for="qr_is_dynamic">{{ __('qr.dynamic_toggle') }}</label>
                        </div>

                        @unless($isAuthenticated)
                            <p class="small text-muted-custom mb-0">
                                <a href="{{ route('login') }}">{{ __('nav.login') }}</a> {{ __('qr.dynamic_login_hint') }}
                            </p>
                        @else
                            <div id="qrDynamicFields" class="d-none">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="qr_dynamic_title">{{ __('qr.dynamic_title') }}</label>
                                        <input type="text" id="qr_dynamic_title" class="form-control" maxlength="120" placeholder="Campaign / product link">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="qr_dynamic_password">{{ __('qr.dynamic_password') }}</label>
                                        <input type="password" id="qr_dynamic_password" class="form-control" autocomplete="new-password" placeholder="{{ __('qr.dynamic_password_ph') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="qr_dynamic_expires">{{ __('qr.dynamic_expires') }}</label>
                                        <input type="datetime-local" id="qr_dynamic_expires" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-brand" id="qrCreateDynamicBtn">
                                            <i class="bi bi-link-45deg me-1"></i> {{ __('qr.dynamic_create') }}
                                        </button>
                                        <a href="{{ route('account.qr-codes.index') }}" class="btn btn-soft ms-1">{{ __('qr.dynamic_manage') }}</a>
                                    </div>
                                    <div class="col-12">
                                        <div class="small text-muted-custom" id="qrDynamicStatus"></div>
                                        <div class="invalid-feedback d-block" data-error-for="destination_url"></div>
                                    </div>
                                </div>
                            </div>
                        @endunless
                    </div>
                </form>

                <section class="qr-history card-surface p-4 mt-4" id="qrHistoryPanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="h6 text-uppercase text-muted-custom mb-0">{{ __('qr.history_heading') }}</h2>
                        <div class="qr-history-tabs" role="tablist">
                            <button type="button" class="btn btn-sm btn-soft is-active js-qr-history-tab" data-tab="recent" role="tab" aria-selected="true">{{ __('qr.tab_recent') }}</button>
                            @if($isAuthenticated)
                                <button type="button" class="btn btn-sm btn-soft js-qr-history-tab" data-tab="saved" role="tab" aria-selected="false">{{ __('qr.tab_saved') }}</button>
                            @endif
                        </div>
                    </div>
                    <div class="qr-history-list" id="qrHistoryList" data-empty="{{ __('qr.history_empty') }}">
                        @forelse($recent as $item)
                            <article class="qr-history-item" data-uuid="{{ $item->uuid }}">
                                <div class="qr-history-thumb">
                                    @if($item->preview_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->preview_path) }}" alt="" width="48" height="48" loading="lazy">
                                    @else
                                        <i class="bi bi-qr-code"></i>
                                    @endif
                                </div>
                                <div class="qr-history-meta">
                                    <strong>{{ $item->title ?: $item->type }}</strong>
                                    <span class="text-muted-custom">{{ $item->type }} · {{ optional($item->created_at)->diffForHumans() }}</span>
                                </div>
                                <div class="qr-history-actions">
                                    @if($isAuthenticated && ! $item->is_saved)
                                        <button type="button" class="btn btn-sm btn-soft js-qr-save" data-uuid="{{ $item->uuid }}" title="{{ __('qr.save') }}">
                                            <i class="bi bi-bookmark"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-soft js-qr-delete" data-uuid="{{ $item->uuid }}" title="{{ __('qr.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <p class="text-muted-custom mb-0 qr-history-empty">{{ __('qr.history_empty') }}</p>
                        @endforelse
                    </div>
                    @unless($isAuthenticated)
                        <p class="small text-muted-custom mt-3 mb-0">{{ __('qr.history_login_hint') }}</p>
                    @endunless
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="qr-preview-panel card-surface p-4 sticky-lg-top" style="top: 96px;">
                    <h2 class="h6 text-uppercase text-muted-custom mb-3">{{ __('qr.preview_heading') }}</h2>

                    <div class="qr-preview-frame mb-3" id="qrPreviewFrame">
                        <div class="qr-preview-placeholder" id="qrPreviewPlaceholder">
                            <i class="bi bi-qr-code"></i>
                            <p class="mb-0">{{ __('qr.preview_empty') }}</p>
                        </div>
                        <img src="" alt="QR code preview" class="qr-preview-image d-none" id="qrPreviewImage" width="256" height="256" loading="lazy">
                        <div class="qr-preview-loading d-none" id="qrPreviewLoading">
                            <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-brand w-100" id="qrDownloadPng" disabled>
                                    <i class="bi bi-download me-1"></i> PNG
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-brand w-100" id="qrDownloadSvg" disabled>
                                    <i class="bi bi-filetype-svg me-1"></i> SVG
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-soft w-100" id="qrDownloadJpg" disabled>JPG</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-soft w-100" id="qrDownloadWebp" disabled>WebP</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-soft w-100" id="qrDownloadPdf" disabled>PDF</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-soft w-100" id="qrCopyImage" disabled>
                                    <i class="bi bi-clipboard-image me-1"></i> {{ __('qr.copy_image') }}
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-soft w-100" id="qrCopyContent" disabled>
                                    <i class="bi bi-clipboard me-1"></i> {{ __('qr.copy_content') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="qr-info small" id="qrInfoBox">
                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted-custom">{{ __('qr.info_type') }}</span><strong id="qrInfoType">—</strong></div>
                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted-custom">{{ __('qr.info_size') }}</span><strong id="qrInfoSize">—</strong></div>
                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted-custom">{{ __('qr.info_chars') }}</span><strong id="qrInfoChars">—</strong></div>
                        <div class="pt-2">
                            <span class="text-muted-custom d-block mb-1">{{ __('qr.info_payload') }}</span>
                            <code class="qr-payload-code d-block" id="qrInfoPayload">—</code>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-12 col-xl-10">
                <section class="card-surface p-4 p-md-5 mb-4 d-none" id="qrRelatedBlog" aria-live="polite">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <span class="eyebrow mb-1 d-inline-block"><i class="bi bi-journal-text"></i> {{ __('qr.related_blog_eyebrow') }}</span>
                            <h2 class="h4 mb-1" id="qrRelatedBlogTitle"></h2>
                            <p class="text-muted-custom mb-0" id="qrRelatedBlogExcerpt"></p>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-brand" id="qrRelatedBlogLink" target="_blank" rel="noopener">{{ __('qr.related_blog_open') }}</a>
                    </div>
                    <div class="small text-muted-custom mb-3" id="qrRelatedBlogMeta"></div>
                    <article class="blog-content qr-inline-blog" id="qrRelatedBlogBody"></article>
                </section>

                <section class="card-surface p-4">
                    <h2 class="h5 mb-3">{{ __('qr.faq_heading') }}</h2>
                    <div class="accordion" id="qrFaq">
                        <div class="accordion-item border-0 mb-2">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#qrFaq1">{{ __('qr.faq1_q') }}</button>
                            </h3>
                            <div id="qrFaq1" class="accordion-collapse collapse" data-bs-parent="#qrFaq">
                                <div class="accordion-body text-muted-custom">{{ __('qr.faq1_a') }}</div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-2">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#qrFaq2">{{ __('qr.faq2_q') }}</button>
                            </h3>
                            <div id="qrFaq2" class="accordion-collapse collapse" data-bs-parent="#qrFaq">
                                <div class="accordion-body text-muted-custom">{{ __('qr.faq2_a') }}</div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#qrFaq3">{{ __('qr.faq3_q') }}</button>
                            </h3>
                            <div id="qrFaq3" class="accordion-collapse collapse" data-bs-parent="#qrFaq">
                                <div class="accordion-body text-muted-custom">{{ __('qr.faq3_a') }}</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
  window.QR_GUIDE_BLOGS = @json($qrGuideBlogs ?? new \stdClass());
</script>
<script src="{{ asset('js/qr-code-generator.js') }}?v={{ @filemtime(public_path('js/qr-code-generator.js')) ?: '1' }}" defer></script>
@endpush

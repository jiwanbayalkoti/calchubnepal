{{-- Login / Register popup (guests only). --}}
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content auth-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h4 mb-1" id="authModalTitle"
                        data-login-title="{{ __('auth.welcome_back') }}"
                        data-register-title="{{ __('auth.create_account') }}">{{ __('auth.welcome_back') }}</h2>
                    <p class="text-muted-custom small mb-0" id="authModalSubtitle"
                       data-login-subtitle="{{ __('auth.login_subtitle') }}"
                       data-register-subtitle="{{ __('auth.register_subtitle') }}">{{ __('auth.login_subtitle') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="auth-modal-tabs btn-group w-100 mb-4" role="tablist">
                    <button type="button" class="btn btn-sm active" data-auth-tab="login" id="authTabLogin">{{ __('auth.login_tab') }}</button>
                    <button type="button" class="btn btn-sm" data-auth-tab="register" id="authTabRegister">{{ __('auth.register_tab') }}</button>
                </div>

                <div id="authPanelLogin" class="auth-modal-panel">
                    <div class="auth-modal-alert d-none" id="loginAlert" role="alert"></div>

                    <form id="authLoginForm" class="auth-form" method="POST" action="{{ route('login') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="modal_login_email" class="form-label">{{ __('auth.email') }}</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input id="modal_login_email" type="email" name="email" class="form-control" required autocomplete="username" placeholder="you@example.com">
                            </div>
                            <div class="invalid-feedback d-block" data-error="email"></div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="modal_login_password" class="form-label mb-0">{{ __('auth.password') }}</label>
                                <a href="{{ route('password.request') }}" class="auth-link small">{{ __('auth.forgot') }}</a>
                            </div>
                            <div class="auth-input-wrap mt-2">
                                <i class="bi bi-lock" aria-hidden="true"></i>
                                <input id="modal_login_password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
                            </div>
                            <div class="invalid-feedback d-block" data-error="password"></div>
                        </div>

                        <div class="form-check mb-4">
                            <input id="modal_remember_me" type="checkbox" class="form-check-input" name="remember" value="1">
                            <label for="modal_remember_me" class="form-check-label">{{ __('auth.remember') }}</label>
                        </div>

                        <button type="submit" class="btn btn-brand w-100 auth-submit">
                            <span class="label">{{ __('auth.login_tab') }}</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </form>

                    @include('partials.auth.google-button')
                </div>

                <div id="authPanelRegister" class="auth-modal-panel d-none">
                    <div class="auth-modal-alert d-none" id="registerAlert" role="alert"></div>

                    <form id="authRegisterForm" class="auth-form" method="POST" action="{{ route('register') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="modal_register_name" class="form-label">{{ __('auth.name') }}</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <input id="modal_register_name" type="text" name="name" class="form-control" required autocomplete="name" placeholder="{{ __('auth.name') }}">
                            </div>
                            <div class="invalid-feedback d-block" data-error="name"></div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_register_email" class="form-label">{{ __('auth.email') }}</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input id="modal_register_email" type="email" name="email" class="form-control" required autocomplete="username" placeholder="you@example.com">
                            </div>
                            <div class="invalid-feedback d-block" data-error="email"></div>
                        </div>

                        <div class="mb-3">
                            <label for="modal_register_password" class="form-label">{{ __('auth.password') }}</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-lock" aria-hidden="true"></i>
                                <input id="modal_register_password" type="password" name="password" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                            </div>
                            <div class="invalid-feedback d-block" data-error="password"></div>
                        </div>

                        <div class="mb-4">
                            <label for="modal_register_password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                <input id="modal_register_password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand w-100 auth-submit">
                            <span class="label">{{ __('auth.create_account') }}</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </form>

                    @include('partials.auth.google-button')
                </div>
            </div>
        </div>
    </div>
</div>

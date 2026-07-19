<div id="cookieConsentBanner" class="cookie-consent d-none" role="dialog" aria-label="{{ __('cookies.title') }}" aria-live="polite">
    <div class="container">
        <div class="cookie-consent-inner">
            <div class="cookie-consent-copy">
                <strong>{{ __('cookies.title') }}</strong>
                <p class="mb-0">
                    {{ __('cookies.body') }}
                    <a href="{{ route('cookies') }}">{{ __('cookies.policy') }}</a>
                    ·
                    <a href="{{ route('privacy') }}">{{ __('cookies.privacy') }}</a>
                </p>
            </div>
            <div class="cookie-consent-actions">
                <button type="button" class="btn btn-sm btn-outline-light" id="cookieConsentDecline">{{ __('cookies.essential') }}</button>
                <button type="button" class="btn btn-sm btn-accent" id="cookieConsentAccept">{{ __('cookies.accept') }}</button>
            </div>
        </div>
    </div>
</div>

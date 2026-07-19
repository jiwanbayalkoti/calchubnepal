{{--
  Google AdSense loader.
  Driven by Admin Settings (ads + site.enable_ads) with .env fallback.
  Personalized ads wait for cookie consent unless require_consent is false.
--}}
@php
    $adsenseEnabled = $hub->adsenseEnabled();
    $clientId = $hub->adsenseClientId();
    $autoAds = $hub->adsenseAutoAds();
    $requireConsent = $hub->adsenseRequireConsent();
@endphp

@if ($adsenseEnabled && filled($clientId))
    <script>
        window.__calcHubAdsense = {
            clientId: @json($clientId),
            autoAds: @json($autoAds),
            requireConsent: @json($requireConsent),
            loaded: false
        };

        window.calcHubLoadAdsense = function () {
            if (window.__calcHubAdsense.loaded) return;
            window.__calcHubAdsense.loaded = true;

            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='
                + encodeURIComponent(window.__calcHubAdsense.clientId);
            s.crossOrigin = 'anonymous';
            document.head.appendChild(s);

            s.addEventListener('load', function () {
                try {
                    document.querySelectorAll('ins.adsbygoogle').forEach(function () {
                        (window.adsbygoogle = window.adsbygoogle || []).push({});
                    });
                } catch (e) { /* ignore */ }
            });
        };
    </script>
@endif

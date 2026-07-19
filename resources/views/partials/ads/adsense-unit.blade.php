{{-- Fallback AdSense unit when no custom Advertisement rows exist for a slot. --}}
@php
    $clientId = $hub->adsenseClientId();
    $slot = $hub->adsenseSlot($position ?? 'sidebar');
@endphp
@if ($hub->adsenseEnabled() && filled($clientId) && filled($slot))
    <div class="ad-banner-card ad-adsense-unit text-center">
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ $clientId }}"
             data-ad-slot="{{ $slot }}"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
    </div>
@endif

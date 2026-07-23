{{-- Fallback AdSense unit when no custom Advertisement rows exist for a slot. --}}
@php
    $clientId = $hub->adsenseClientId();
    $slot = $hub->adsenseSlot($position ?? 'sidebar');
    $adsensePosition = $position ?? 'sidebar';
@endphp
@if ($hub->adsenseEnabled() && filled($clientId) && filled($slot))
    <div class="ad-banner-card ad-adsense-unit text-center position-relative">
        <img src="{{ route('ads.adsense.impression', ['position' => $adsensePosition, 'slot' => $slot]) }}"
             alt="" width="1" height="1"
             style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;"
             loading="lazy">
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ $clientId }}"
             data-ad-slot="{{ $slot }}"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
    </div>
@endif

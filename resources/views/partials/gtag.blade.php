@php
    $gaId = $hub->gaMeasurementId();
@endphp
@if(filled($gaId))
<!-- Google Analytics — delayed until idle to protect TBT/LCP (ID from Admin → Settings → SEO) -->
<script>
(function () {
  var GA_ID = @json($gaId);
  var loaded = false;

  function loadGtag() {
    if (loaded) return;
    loaded = true;
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    window.gtag = gtag;
    gtag('js', new Date());
    gtag('config', GA_ID, { transport_type: 'beacon' });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_ID;
    document.head.appendChild(s);
  }

  function schedule() {
    if ('requestIdleCallback' in window) {
      requestIdleCallback(loadGtag, { timeout: 4000 });
    } else {
      setTimeout(loadGtag, 2500);
    }
  }

  if (document.readyState === 'complete') {
    schedule();
  } else {
    window.addEventListener('load', schedule, { once: true });
  }
})();
</script>
@endif

@if($gtm = $hub->gtmContainerId())
<!-- Google Tag Manager (container from Admin SEO settings) -->
<script>
(function(){
  var id = @json($gtm);
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({'gtm.start': new Date().getTime(), event:'gtm.js'});
  var f = document.getElementsByTagName('script')[0], j = document.createElement('script');
  j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + id;
  f.parentNode.insertBefore(j, f);
})();
</script>
@endif

@if($pixel = $hub->facebookPixelId())
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', @json($pixel));
fbq('track', 'PageView');
</script>
@endif

{!! $hub->headerScripts() !!}

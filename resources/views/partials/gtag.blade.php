<!-- Google Analytics — delayed until idle to protect TBT/LCP -->
<script>
(function () {
  var GA_ID = 'G-ZG8HCJW6ET';
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

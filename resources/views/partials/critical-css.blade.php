{{--
  Above-the-fold critical CSS (header + shell).
  Keeps first paint usable while full stylesheets load asynchronously.
--}}
<style>
:root{--brand:#0B6E4F;--brand-dark:#084C37;--brand-rgb:11,110,79;--accent:#F4A259;--ink:#1A1A1A;--muted:#5C6B73;--surface:#F7F9F8;--card:#FFF;--border:rgba(26,26,26,.08);--font-ui:'DM Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;--font-display:Fraunces,Georgia,serif;--radius-md:16px;--radius-lg:24px;color-scheme:light}
html[data-theme=dark]{--brand:#16A579;--brand-dark:#0B6E4F;--ink:#EDF3F0;--muted:#A2B3AC;--surface:#0E1512;--card:#16201C;--border:rgba(237,243,240,.09);color-scheme:dark}
*{box-sizing:border-box}html{scroll-behavior:smooth}
body{margin:0;font-family:var(--font-ui);color:var(--ink);background:var(--surface);-webkit-font-smoothing:antialiased}
a{color:var(--brand);text-decoration:none}h1,h2,h3{font-family:var(--font-display);color:var(--ink);letter-spacing:-.01em}
.site-header{position:sticky;top:0;z-index:1040;background:rgba(247,249,248,.92);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
html[data-theme=dark] .site-header{background:rgba(14,21,18,.92)}
.brand-logo{display:inline-flex;align-items:center;gap:.55rem;font-weight:700;color:var(--ink);font-size:1.05rem}
.brand-mark{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:10px;background:var(--brand);color:#fff}
.hero-section{padding:4.5rem 0 3.5rem;position:relative;overflow:hidden}
.hero-title{font-size:clamp(2.1rem,5vw,3.5rem);line-height:1.08;margin:0 0 .75rem}
.hero-subtitle{font-size:1.1rem;color:var(--muted);margin:0 0 1.25rem}
.hero-eyebrow{display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;font-weight:600;color:var(--brand);margin-bottom:.75rem}
.btn-brand{background:var(--brand);border-color:var(--brand);color:#fff}
.visually-hidden-focusable:not(:focus):not(:focus-within){position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
</style>

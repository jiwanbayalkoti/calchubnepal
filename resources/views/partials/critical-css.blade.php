{{--
  Above-the-fold critical CSS. Full Bootstrap + app CSS load asynchronously.
  Sized to stabilize hero/header so async CSS does not tank CLS.
--}}
<style>
:root{--brand:#0B6E4F;--brand-dark:#084C37;--brand-rgb:11,110,79;--accent:#F4A259;--accent-rgb:244,162,89;--ink:#1A1A1A;--muted:#4A5A62;--surface:#F7F9F8;--card:#FFF;--border:rgba(26,26,26,.08);--shadow-color:rgba(8,34,26,.10);--font-ui:'DM Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;--font-display:Fraunces,Georgia,'Times New Roman',serif;--radius-sm:10px;--radius-md:16px;--radius-lg:24px;--transition-fast:150ms ease;color-scheme:light}
html[data-theme=dark]{--brand:#16A579;--brand-dark:#0B6E4F;--ink:#EDF3F0;--muted:#B7C4BE;--surface:#0E1512;--card:#16201C;--border:rgba(237,243,240,.09);color-scheme:dark}
*,*::before,*::after{box-sizing:border-box}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{margin:0;font-family:var(--font-ui);color:var(--ink);background:var(--surface);-webkit-font-smoothing:antialiased;line-height:1.5}
a{color:var(--brand);text-decoration:none}
h1,h2,h3,h4,.display-font{font-family:var(--font-display);color:var(--ink);letter-spacing:-.01em;margin-top:0}
img{max-width:100%;height:auto;display:block}
.container,.container-fluid{width:100%;padding-right:.75rem;padding-left:.75rem;margin-right:auto;margin-left:auto}
@media(min-width:576px){.container{max-width:540px}}
@media(min-width:768px){.container{max-width:720px}}
@media(min-width:992px){.container{max-width:960px}}
@media(min-width:1200px){.container{max-width:1140px}}
@media(min-width:1400px){.container{max-width:1320px}}
.row{display:flex;flex-wrap:wrap;margin-right:-.5rem;margin-left:-.5rem}
.row>*{flex-shrink:0;width:100%;max-width:100%;padding-right:.5rem;padding-left:.5rem}
.col-6{flex:0 0 auto;width:50%}.col-12{flex:0 0 auto;width:100%}
@media(min-width:768px){.col-md-4{flex:0 0 auto;width:33.333%}.col-md-6{flex:0 0 auto;width:50%}}
@media(min-width:992px){.col-lg-3{flex:0 0 auto;width:25%}.col-lg-4{flex:0 0 auto;width:33.333%}.col-lg-5{flex:0 0 auto;width:41.666%}.col-lg-8{flex:0 0 auto;width:66.666%}.col-lg-9{flex:0 0 auto;width:75%}}
@media(min-width:1200px){.col-xl-3{flex:0 0 auto;width:25%}.col-xl-4{flex:0 0 auto;width:33.333%}.col-xl-5{flex:0 0 auto;width:41.666%}}
.g-3,.gy-3{--bs-gutter-y:1rem}.g-3,.gx-3{--bs-gutter-x:1rem}
.g-3{margin-top:calc(-1*var(--bs-gutter-y));margin-right:calc(-.5*var(--bs-gutter-x));margin-left:calc(-.5*var(--bs-gutter-x))}
.g-3>*{padding-right:calc(var(--bs-gutter-x)*.5);padding-left:calc(var(--bs-gutter-x)*.5);margin-top:var(--bs-gutter-y)}
.d-flex{display:flex!important}.d-none{display:none!important}.d-block{display:block!important}
.flex-column{flex-direction:column!important}.flex-wrap{flex-wrap:wrap!important}
.align-items-center{align-items:center!important}.align-items-stretch{align-items:stretch!important}
.justify-content-center{justify-content:center!important}.justify-content-between{justify-content:space-between!important}
.gap-2{gap:.5rem!important}.ms-auto{margin-left:auto!important}.me-1{margin-right:.25rem!important}.mb-0{margin-bottom:0!important}.mb-2{margin-bottom:.5rem!important}.mt-2{margin-top:.5rem!important}.mt-3{margin-top:1rem!important}.py-2{padding-top:.5rem!important;padding-bottom:.5rem!important}.pt-0{padding-top:0!important}.w-100{width:100%!important}.h-100{height:100%!important}.text-center{text-align:center!important}.fw-semibold{font-weight:600!important}.small{font-size:.875em}
@media(min-width:992px){.d-lg-inline-block{display:inline-block!important}.d-lg-flex{display:flex!important}.flex-lg-row{flex-direction:row!important}.align-items-lg-center{align-items:center!important}.ms-lg-3{margin-left:1rem!important}.ms-lg-4{margin-left:1.5rem!important}.me-lg-auto{margin-right:auto!important}.mt-lg-0{margin-top:0!important}.text-lg-start{text-align:left!important}}
@media(min-width:1200px){.d-xl-inline{display:inline!important}}
.navbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;padding:.5rem 0}
.navbar-expand-lg{flex-wrap:nowrap;justify-content:flex-start}
.navbar-toggler{padding:.25rem .5rem;font-size:1.25rem;background:transparent;border:0;border-radius:.375rem;min-width:44px;min-height:44px}
.navbar-collapse{flex-basis:100%;flex-grow:1}
@media(min-width:992px){.navbar-expand-lg .navbar-toggler{display:none}.navbar-expand-lg .navbar-collapse{display:flex!important;flex-basis:auto}}
.navbar-nav{display:flex;flex-direction:column;padding-left:0;margin:0;list-style:none}
@media(min-width:992px){.navbar-nav{flex-direction:row}}
.nav-link{display:block;padding:.5rem .75rem;color:var(--ink);font-weight:500;min-height:44px;line-height:1.7}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;font-weight:600;line-height:1.5;text-align:center;vertical-align:middle;cursor:pointer;border:1px solid transparent;padding:.5rem 1rem;font-size:1rem;border-radius:999px;min-height:44px}
.btn-sm{padding:.4rem .85rem;font-size:.875rem;min-height:44px;min-width:44px}
.btn-lg{padding:.7rem 1.5rem;font-size:1.05rem}
.btn-brand{background:var(--brand);border-color:var(--brand);color:#fff;box-shadow:0 10px 24px rgba(var(--brand-rgb),.28)}
.btn-outline-brand{background:transparent;border-color:var(--brand);color:var(--brand)}
.btn-soft{background:rgba(var(--brand-rgb),.08);border-color:transparent;color:var(--ink)}
.form-control{display:block;width:100%;padding:.45rem .9rem;font-size:1rem;font-weight:400;line-height:1.5;color:var(--ink);background:var(--card);border:1px solid var(--border);border-radius:999px;min-height:44px}
.site-header{position:sticky;top:0;z-index:1040;background:rgba(247,249,248,.94);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)}
html[data-theme=dark] .site-header{background:rgba(14,21,18,.94)}
.brand-logo{display:inline-flex;align-items:center;gap:.55rem;font-weight:700;color:var(--ink);font-size:1.05rem}
.brand-mark{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:10px;background:var(--brand);color:#fff;flex-shrink:0}
.main-nav .nav-link.active{color:var(--brand)}
.header-actions{width:100%}
@media(min-width:992px){.header-actions{width:auto}}
.search-box{position:relative;min-width:min(100%,220px)}
.search-box .form-control{padding-left:2.4rem;height:44px}
.theme-toggle{width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);background:var(--card);color:var(--ink)}
.lang-switch .btn{border-radius:999px;min-width:44px}
.hero-section{padding:3.5rem 0 3rem;position:relative;overflow:hidden;min-height:420px}
.hero-title{font-size:clamp(2.1rem,5vw,3.5rem);line-height:1.08;margin:0 0 .75rem;font-family:Georgia,'Times New Roman',serif}
.hero-subtitle{font-size:1.1rem;color:var(--muted);margin:0 0 1.25rem;max-width:36ch}
.hero-eyebrow{display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;font-weight:600;color:var(--brand);margin-bottom:.75rem}
.atmosphere{position:relative;overflow:hidden}
.atmosphere-shape{position:absolute;border-radius:50%;filter:blur(40px);opacity:.18;pointer-events:none;z-index:0}
.card-surface{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:0 8px 24px var(--shadow-color)}
.text-muted-custom{color:var(--muted)!important}
.visually-hidden-focusable:not(:focus):not(:focus-within){position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.collapse:not(.show){display:none}
@media(min-width:992px){.navbar-expand-lg .collapse:not(.show){display:flex}}
</style>

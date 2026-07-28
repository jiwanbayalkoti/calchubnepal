{{--
  Load a stylesheet without blocking first paint.
  Usage: @include('partials.async-css', ['href' => '...'])
--}}
<link rel="preload" as="style" href="{{ $href }}" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ $href }}"></noscript>

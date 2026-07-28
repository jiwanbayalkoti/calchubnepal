{{--
  Non-render-blocking stylesheet.
  media="print" + onload is the pattern Lighthouse recognizes as non-blocking.
--}}
<link rel="stylesheet" href="{{ $href }}" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="{{ $href }}"></noscript>

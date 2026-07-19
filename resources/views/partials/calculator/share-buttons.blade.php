@php($shareUrl = urlencode(url()->current()))
@php($shareTitle = urlencode($calculator->title ?? config('app.name')))

<div class="share-buttons d-flex align-items-center">
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" title="Share on Facebook" aria-label="Share on Facebook">
        <i class="bi bi-facebook"></i>
    </a>
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" title="Share on X" aria-label="Share on X (Twitter)">
        <i class="bi bi-twitter-x"></i>
    </a>
    <a href="https://api.whatsapp.com/send?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" title="Share on WhatsApp" aria-label="Share on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" title="Share on LinkedIn" aria-label="Share on LinkedIn">
        <i class="bi bi-linkedin"></i>
    </a>
    <a href="mailto:?subject={{ $shareTitle }}&body={{ $shareUrl }}" title="Share via Email" aria-label="Share via Email">
        <i class="bi bi-envelope"></i>
    </a>
</div>

@if(($relatedBlogs ?? collect())->isNotEmpty())
<section class="mb-5">
    <h2 class="h4 mb-3">Related Guides</h2>
    <div class="row g-3">
        @foreach($relatedBlogs as $post)
            <div class="col-md-6">
                <a href="{{ route('blog.show', $post) }}" class="card-surface p-3 d-block text-decoration-none h-100">
                    <span class="badge-soft-brand small">{{ $post->category?->name ?? 'Blog' }}</span>
                    <h3 class="h6 mt-2 mb-1" style="color:var(--ink);">{{ $post->title }}</h3>
                    <p class="small text-muted-custom mb-0">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 110) }}</p>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif

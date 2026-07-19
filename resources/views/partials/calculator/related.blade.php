@if(($related ?? collect())->isNotEmpty())
    <section class="mt-5">
        <h3 class="h4 mb-4">Related Calculators</h3>
        <div class="row g-3">
            @foreach($related as $item)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('calculators.show', $item) }}" class="calc-card h-100">
                        <span class="calc-icon"><i class="bi {{ $item->icon ?? 'bi-calculator' }}"></i></span>
                        <p class="calc-title">{{ $item->title }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif

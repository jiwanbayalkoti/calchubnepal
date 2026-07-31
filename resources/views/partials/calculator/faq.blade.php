@if($calculator->faqs->isNotEmpty())
    <section class="mt-5" id="faq">
        <h2 class="h4 mb-4">Frequently Asked Questions</h2>
        <div class="accordion faq-accordion" id="faqAccordion">
            @foreach($calculator->faqs as $index => $faq)
                <div class="accordion-item">
                    {{-- Use div (not h2) so FAQ questions stay under the section H2 for SEO --}}
                    <div class="accordion-header" id="faqHeading{{ $faq->id }}">
                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $faq->id }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $faq->id }}">
                            <span class="faq-question-text">{{ $faq->question }}</span>
                        </button>
                    </div>
                    <div id="faqCollapse{{ $faq->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                         aria-labelledby="faqHeading{{ $faq->id }}" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            {{ $faq->answer }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

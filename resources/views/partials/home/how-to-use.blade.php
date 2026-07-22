<section class="section how-to-section">
    <div class="container">
        <div class="how-to-intro">
            <span class="eyebrow">{{ __('home.how.eyebrow', ['site' => $hub->siteName()]) }}</span>
            <h2>{{ __('home.how.title') }}</h2>
            <p class="how-to-lead">{{ __('home.how.intro') }}</p>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <article class="how-to-card">
                    <div class="how-to-card__icons">
                        <span class="how-to-card__num">1</span>
                        <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-search"></i></span>
                    </div>
                    <h3>{{ __('home.how.step1_title') }}</h3>
                    <p>{{ __('home.how.step1_body') }}</p>
                    <a href="{{ route('calculators.index') }}" class="how-to-card__link js-focus-home-search">
                        {{ __('home.how.step1_link') }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            </div>

            <div class="col-md-4">
                <article class="how-to-card">
                    <div class="how-to-card__icons">
                        <span class="how-to-card__num">2</span>
                        <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-layers"></i></span>
                    </div>
                    <h3>{{ __('home.how.step2_title') }}</h3>
                    <p>{{ __('home.how.step2_body', ['categories' => $categoryCount, 'calculators' => $calculatorCount]) }}</p>
                    <a href="{{ route('categories.index') }}" class="how-to-card__link">
                        {{ __('home.how.step2_link') }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            </div>

            <div class="col-md-4">
                <article class="how-to-card">
                    <div class="how-to-card__icons">
                        <span class="how-to-card__num">3</span>
                        <span class="how-to-card__glyph" aria-hidden="true"><i class="bi bi-stars"></i></span>
                    </div>
                    <h3>{{ __('home.how.step3_title') }}</h3>
                    <p>{{ __('home.how.step3_body') }}</p>
                    <a href="{{ route('calculators.show', 'bmi-calculator') }}" class="how-to-card__link">
                        {{ __('home.how.step3_link') }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            </div>
        </div>
    </div>
</section>

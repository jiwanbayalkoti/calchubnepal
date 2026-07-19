@extends('layouts.public')

@push('schemas')
    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endpush

@section('breadcrumb')
    @include('partials.calculator.breadcrumb', ['breadcrumbs' => $breadcrumbs])
@endsection

@section('content')
    <section class="section atmosphere pt-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="calc-icon" style="width:56px;height:56px;flex-shrink:0;"><i class="bi {{ $calculator->icon ?? 'bi-calculator' }} fs-4"></i></span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h1 class="h3 mb-1">{{ $calculator->title }}</h1>
                                    <p class="text-muted-custom mb-0">{{ $calculator->short_description }}</p>
                                </div>
                                @auth
                                    <button type="button"
                                            class="btn btn-sm {{ $isFavorited ? 'btn-brand' : 'btn-soft' }} js-toggle-favorite"
                                            data-url="{{ route('account.favorites.toggle', $calculator) }}"
                                            data-favorited="{{ $isFavorited ? '1' : '0' }}"
                                            aria-pressed="{{ $isFavorited ? 'true' : 'false' }}">
                                        <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                        <span class="js-favorite-label">{{ $isFavorited ? __('calc.favorited') : __('calc.favorite') }}</span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-soft js-open-auth" data-auth="login">
                                        <i class="bi bi-heart"></i> Favorite
                                    </button>
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- ==================================================== --}}
                    {{-- Interactive calculator form                          --}}
                    {{-- ==================================================== --}}
                    <div class="calc-form-card mb-4">
                        <form class="js-calculator-form row g-3" data-slug="{{ $calculator->slug }}" data-result-target="resultPanel">
                            @foreach($calculator->input_schema as $field)
                                @include('partials.calculator.field', ['field' => $field])
                            @endforeach

                            <div class="col-12 d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-brand">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> {{ __('calc.calculate') }}
                                </button>
                                <button type="reset" class="btn btn-outline-brand">{{ __('calc.reset') }}</button>
                            </div>
                        </form>
                        <div class="mt-3 pt-3 border-top">
                            @include('partials.calculator.disclaimer')
                        </div>
                    </div>

                    {{-- ==================================================== --}}
                    {{-- Results panel                                        --}}
                    {{-- ==================================================== --}}
                    <div class="calc-result-card mb-4" id="resultPanel">
                        <div class="result-loading">
                            <div class="spinner-border text-success" role="status"><span class="visually-hidden">Calculating...</span></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ __('calc.result') }}</h5>
                            <div class="result-actions d-none gap-2">
                                @if ($hub->aiEnabled())
                                    <button type="button" class="btn btn-sm btn-soft js-ai-explain ai-explain-toggle d-none" data-slug="{{ $calculator->slug }}">
                                        <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                        <i class="bi bi-stars"></i> {{ __('calc.ai_explain') }}
                                    </button>
                                @endif
                                @auth
                                    <button type="button" class="btn btn-sm btn-soft js-save-result" data-slug="{{ $calculator->slug }}" data-title="{{ $calculator->title }}">
                                        <i class="bi bi-bookmark-plus"></i> {{ __('calc.save') }}
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-soft js-open-auth" data-auth="login">
                                        <i class="bi bi-bookmark-plus"></i> Save
                                    </button>
                                @endauth
                                <button type="button" class="btn btn-sm btn-outline-brand js-print"><i class="bi bi-printer"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-brand js-download-pdf" data-slug="{{ $calculator->slug }}"><i class="bi bi-file-earmark-pdf"></i></button>
                            </div>
                        </div>

                        <p class="results-placeholder text-muted-custom">{{ __('calc.placeholder') }}</p>

                        <div class="ai-explain-box mt-3"></div>
                    </div>

                    @include('partials.ads.between-results')

                    <div class="d-flex align-items-center justify-content-between mb-4 no-print">
                        @include('partials.calculator.share-buttons')
                    </div>

                    {{-- ==================================================== --}}
                    {{-- Formula explanation                                  --}}
                    {{-- ==================================================== --}}
                    @if($calculator->formula_description)
                        <section class="mb-5">
                            <h3 class="h4 mb-3">{{ __('calc.how') }}</h3>
                            <div class="card-surface p-4">
                                <p class="mb-0">{{ $calculator->formula_description }}</p>
                                @if($calculator->formula_expression)
                                    <code class="d-block mt-3 p-2 rounded" style="background: rgba(var(--brand-rgb), .08);">{{ $calculator->formula_expression }}</code>
                                @endif
                            </div>
                        </section>
                    @endif

                    {{-- ==================================================== --}}
                    {{-- Example                                              --}}
                    {{-- ==================================================== --}}
                    @if($calculator->examples->isNotEmpty())
                        <section class="mb-5">
                            <h3 class="h4 mb-3">Example</h3>
                            @foreach($calculator->examples as $example)
                                <div class="card-surface p-4 mb-3">
                                    <h4 class="h6">{{ $example->title }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted-custom small mb-1 text-uppercase fw-bold">Inputs</p>
                                            <ul class="list-unstyled small">
                                                @foreach($example->inputs as $key => $value)
                                                    <li>
                                                        <strong>{{ ucwords(str_replace('_', ' ', is_string($key) ? $key : (string) $key)) }}:</strong>
                                                        @if(is_array($value))
                                                            <pre class="mb-0 mt-1 small p-2 rounded" style="background: rgba(var(--brand-rgb), .06); white-space: pre-wrap;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted-custom small mb-1 text-uppercase fw-bold">Outputs</p>
                                            <ul class="list-unstyled small">
                                                @foreach($example->outputs as $key => $value)
                                                    <li>
                                                        <strong>{{ ucwords(str_replace('_', ' ', is_string($key) ? $key : (string) $key)) }}:</strong>
                                                        @if(is_array($value))
                                                            <pre class="mb-0 mt-1 small p-2 rounded" style="background: rgba(var(--brand-rgb), .06); white-space: pre-wrap;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    @if($example->explanation)
                                        <p class="mt-3 mb-0 text-muted-custom">{{ $example->explanation }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </section>
                    @endif

                    @include('partials.ads.in-content')

                    @if($calculator->description)
                        <section class="mb-5">
                            <h3 class="h4 mb-3">{{ __('calc.about') }}</h3>
                            <div class="text-muted-custom">{!! $calculator->description !!}</div>
                        </section>
                    @endif

                    @include('partials.calculator.faq', ['calculator' => $calculator])

                    @include('partials.calculator.related', ['related' => $related])
                </div>

                <div class="col-lg-4">
                    @include('partials.ads.sidebar')

                    <div class="card-surface p-3 mb-3">
                        <h6 class="text-uppercase small fw-bold text-muted-custom mb-3">{{ __('calc.in_category') }}</h6>
                        <a href="{{ route('categories.show', $calculator->category) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                            <span class="cat-icon" style="width:44px;height:44px;"><i class="bi {{ $calculator->category->icon ?? 'bi-grid' }}"></i></span>
                            <span class="fw-semibold" style="color: var(--ink);">{{ $calculator->category->name }}</span>
                        </a>
                    </div>

                    @if($calculator->is_premium)
                        <div class="card-surface p-3 border" style="border-color: rgba(var(--accent-rgb), .4) !important;">
                            <span class="badge-soft-accent mb-2 d-inline-block">Premium</span>
                            <p class="small text-muted-custom mb-2">Unlock unlimited saved calculations and priority AI explanations.</p>
                            <a href="{{ route('pricing') }}" class="btn btn-accent btn-sm w-100">View Plans</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

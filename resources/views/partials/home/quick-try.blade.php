@php
    $quickTryTabs = [
        'mortgage' => [
            'label' => __('home.quick.tab_mortgage'),
            'icon' => 'bi-house-door',
            'url' => route('calculators.show', 'mortgage-calculator'),
            'name' => __('home.quick.name_mortgage'),
        ],
        'bmi' => [
            'label' => __('home.quick.tab_bmi'),
            'icon' => 'bi-heart-pulse',
            'url' => route('calculators.show', 'bmi-calculator'),
            'name' => __('home.quick.name_bmi'),
        ],
        'percent' => [
            'label' => __('home.quick.tab_percent'),
            'icon' => 'bi-percent',
            'url' => route('calculators.show', 'percentage-calculator'),
            'name' => __('home.quick.name_percent'),
        ],
        'tip' => [
            'label' => __('home.quick.tab_tip'),
            'icon' => 'bi-cash-coin',
            'url' => route('calculators.show', 'tip-calculator'),
            'name' => __('home.quick.name_tip'),
        ],
        'age' => [
            'label' => __('home.quick.tab_age'),
            'icon' => 'bi-calendar3',
            'url' => route('calculators.show', 'age-calculator'),
            'name' => __('home.quick.name_age'),
        ],
    ];
@endphp

<div
    class="quick-try"
    x-data="quickTryWidget(@js($quickTryTabs))"
>
    <div class="quick-try__header">
        <span class="quick-try__title">
            <i class="bi bi-stars" aria-hidden="true"></i>
            {{ __('home.quick.title') }}
        </span>
        <span class="quick-try__live">
            <span class="quick-try__live-dot" aria-hidden="true"></span>
            {{ __('home.quick.live') }}
        </span>
    </div>

    <div class="quick-try__tabs" role="tablist" aria-label="{{ __('home.quick.title') }}">
        <template x-for="(meta, key) in tabs" :key="key">
            <button
                type="button"
                class="quick-try__tab"
                role="tab"
                :class="{ 'is-active': tab === key }"
                :aria-selected="tab === key"
                @click="tab = key"
            >
                <i class="bi" :class="meta.icon" aria-hidden="true"></i>
                <span x-text="meta.label"></span>
            </button>
        </template>
    </div>

    <div class="quick-try__body">
        {{-- Mortgage --}}
        <div class="quick-try__fields" x-show="tab === 'mortgage'" x-cloak>
            <label class="quick-try__field">
                <span>{{ __('home.quick.loan') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="mortgage.loan" min="1" step="1000" inputmode="decimal">
                    <span class="quick-try__suffix">Rs</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.rate') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="mortgage.rate" min="0" max="30" step="0.01" inputmode="decimal">
                    <span class="quick-try__suffix">%</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.term') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="mortgage.years" min="1" max="40" step="1" inputmode="numeric">
                    <span class="quick-try__suffix">{{ __('home.quick.yrs') }}</span>
                </div>
            </label>
        </div>

        {{-- BMI --}}
        <div class="quick-try__fields" x-show="tab === 'bmi'" x-cloak>
            <label class="quick-try__field">
                <span>{{ __('home.quick.weight') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="bmi.weight" min="1" max="700" step="0.1" inputmode="decimal">
                    <span class="quick-try__suffix">kg</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.height') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="bmi.height" min="30" max="300" step="0.1" inputmode="decimal">
                    <span class="quick-try__suffix">cm</span>
                </div>
            </label>
        </div>

        {{-- Percent --}}
        <div class="quick-try__fields" x-show="tab === 'percent'" x-cloak>
            <label class="quick-try__field">
                <span>{{ __('home.quick.percent_x') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="percent.x" step="0.01" inputmode="decimal">
                    <span class="quick-try__suffix">%</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.percent_y') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="percent.y" step="0.01" inputmode="decimal">
                    <span class="quick-try__suffix">of</span>
                </div>
            </label>
        </div>

        {{-- Tip --}}
        <div class="quick-try__fields" x-show="tab === 'tip'" x-cloak>
            <label class="quick-try__field">
                <span>{{ __('home.quick.bill') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="tip.bill" min="0" step="1" inputmode="decimal">
                    <span class="quick-try__suffix">Rs</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.tip_pct') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="tip.pct" min="0" max="50" step="0.5" inputmode="decimal">
                    <span class="quick-try__suffix">%</span>
                </div>
            </label>
            <label class="quick-try__field">
                <span>{{ __('home.quick.people') }}</span>
                <div class="quick-try__input">
                    <input type="number" x-model.number="tip.people" min="1" max="100" step="1" inputmode="numeric">
                    <span class="quick-try__suffix">ppl</span>
                </div>
            </label>
        </div>

        {{-- Age --}}
        <div class="quick-try__fields" x-show="tab === 'age'" x-cloak>
            <label class="quick-try__field quick-try__field--wide">
                <span>{{ __('home.quick.birth') }}</span>
                <div class="quick-try__input">
                    <input type="date" x-model="age.birth">
                </div>
            </label>
        </div>

        <div class="quick-try__result" aria-live="polite">
            <span class="quick-try__result-label" x-text="result.label"></span>
            <strong class="quick-try__result-value" x-text="result.value"></strong>
            <span class="quick-try__result-sub" x-show="result.sub" x-text="result.sub"></span>
        </div>
    </div>

    <a class="quick-try__footer" :href="tabs[tab].url">
        <span>
            <i class="bi bi-stars" aria-hidden="true"></i>
            <span x-text="footerLabel"></span>
        </span>
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
</div>

@once
    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('quickTryWidget', (tabs) => ({
                    tabs,
                    tab: 'mortgage',
                    mortgage: { loan: 8000000, rate: 10.5, years: 20 },
                    bmi: { weight: 70, height: 170 },
                    percent: { x: 20, y: 200 },
                    tip: { bill: 1500, pct: 10, people: 2 },
                    age: { birth: '1995-06-15' },
                    footerTemplate: @json(__('home.quick.open_full')),

                    get footerLabel() {
                        return this.footerTemplate.replace(':name', this.tabs[this.tab].name);
                    },

                    get result() {
                        switch (this.tab) {
                            case 'mortgage': return this.calcMortgage();
                            case 'bmi': return this.calcBmi();
                            case 'percent': return this.calcPercent();
                            case 'tip': return this.calcTip();
                            case 'age': return this.calcAge();
                            default: return { label: '', value: '—', sub: '' };
                        }
                    },

                    fmt(n, digits = 0) {
                        if (!Number.isFinite(n)) return '—';
                        return Number(n).toLocaleString(undefined, {
                            maximumFractionDigits: digits,
                            minimumFractionDigits: digits > 0 ? Math.min(digits, 1) : 0,
                        });
                    },

                    calcMortgage() {
                        const loan = Number(this.mortgage.loan) || 0;
                        const years = Number(this.mortgage.years) || 0;
                        const rate = Number(this.mortgage.rate) || 0;
                        const months = Math.round(years * 12);
                        if (loan <= 0 || months <= 0) {
                            return { label: @json(__('home.quick.per_month')), value: '—', sub: '' };
                        }
                        const r = rate / 12 / 100;
                        let monthly;
                        if (r === 0) {
                            monthly = loan / months;
                        } else {
                            const factor = Math.pow(1 + r, months);
                            monthly = loan * r * factor / (factor - 1);
                        }
                        const totalInterest = monthly * months - loan;
                        return {
                            label: @json(__('home.quick.per_month')),
                            value: 'Rs ' + this.fmt(monthly),
                            sub: @json(__('home.quick.total_interest')).replace(':amount', 'Rs ' + this.fmt(totalInterest)),
                        };
                    },

                    calcBmi() {
                        const w = Number(this.bmi.weight) || 0;
                        const hCm = Number(this.bmi.height) || 0;
                        if (w <= 0 || hCm <= 0) {
                            return { label: @json(__('home.quick.bmi_label')), value: '—', sub: '' };
                        }
                        const h = hCm / 100;
                        const bmi = w / (h * h);
                        let category = @json(__('home.quick.bmi_obese'));
                        if (bmi < 18.5) category = @json(__('home.quick.bmi_under'));
                        else if (bmi < 25) category = @json(__('home.quick.bmi_normal'));
                        else if (bmi < 30) category = @json(__('home.quick.bmi_over'));
                        return {
                            label: @json(__('home.quick.bmi_label')),
                            value: this.fmt(bmi, 1),
                            sub: category,
                        };
                    },

                    calcPercent() {
                        const x = Number(this.percent.x);
                        const y = Number(this.percent.y);
                        if (!Number.isFinite(x) || !Number.isFinite(y)) {
                            return { label: @json(__('home.quick.result')), value: '—', sub: '' };
                        }
                        const value = y * (x / 100);
                        return {
                            label: @json(__('home.quick.result')),
                            value: this.fmt(value, 2),
                            sub: this.fmt(x, 2) + '% of ' + this.fmt(y, 2),
                        };
                    },

                    calcTip() {
                        const bill = Number(this.tip.bill) || 0;
                        const pct = Number(this.tip.pct) || 0;
                        const people = Math.max(1, Number(this.tip.people) || 1);
                        const tipAmt = bill * pct / 100;
                        const total = bill + tipAmt;
                        return {
                            label: @json(__('home.quick.total')),
                            value: 'Rs ' + this.fmt(total),
                            sub: @json(__('home.quick.tip_sub'))
                                .replace(':tip', 'Rs ' + this.fmt(tipAmt))
                                .replace(':each', 'Rs ' + this.fmt(total / people)),
                        };
                    },

                    calcAge() {
                        const birthRaw = this.age.birth;
                        if (!birthRaw) {
                            return { label: @json(__('home.quick.age_label')), value: '—', sub: '' };
                        }
                        const birth = new Date(birthRaw + 'T00:00:00');
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (Number.isNaN(birth.getTime()) || birth > today) {
                            return { label: @json(__('home.quick.age_label')), value: '—', sub: '' };
                        }
                        let y = today.getFullYear() - birth.getFullYear();
                        let m = today.getMonth() - birth.getMonth();
                        let d = today.getDate() - birth.getDate();
                        if (d < 0) {
                            m -= 1;
                            const prev = new Date(today.getFullYear(), today.getMonth(), 0);
                            d += prev.getDate();
                        }
                        if (m < 0) {
                            y -= 1;
                            m += 12;
                        }
                        return {
                            label: @json(__('home.quick.age_label')),
                            value: y + ' ' + @json(__('home.quick.years')),
                            sub: @json(__('home.quick.age_sub'))
                                .replace(':months', String(m))
                                .replace(':days', String(d)),
                        };
                    },
                }));
            });
        </script>
    @endpush
@endonce

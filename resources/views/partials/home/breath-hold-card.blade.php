<div
    class="breath-card card-surface h-100"
    x-data="breathHoldCard()"
    data-store-url="{{ route('breath-hold.store') }}"
    data-claim-url="{{ route('breath-hold.claim') }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
>
    <div class="breath-card__top">
        <div>
            <span class="eyebrow"><i class="bi bi-lungs" aria-hidden="true"></i> {{ __('home.breath.eyebrow') }}</span>
            <h2 class="breath-card__title">{{ __('home.breath.title') }}</h2>
        </div>
        <div class="breath-card__timer" :class="{ 'is-holding': phase === 'holding', 'is-done': phase === 'done' }" x-text="displayTime"></div>
    </div>

    <div class="breath-card__timeline" aria-hidden="true">
        <div class="breath-card__track">
            <span class="breath-card__zone breath-card__zone--poor" style="width:33.333%"></span>
            <span class="breath-card__zone breath-card__zone--medium" style="width:33.333%"></span>
            <span class="breath-card__zone breath-card__zone--healthy" style="width:33.334%"></span>
            <span class="breath-card__fill" :style="'width:' + progressPct + '%'"></span>
            <span class="breath-card__marker" :style="'left:' + progressPct + '%'"></span>
        </div>
        <div class="breath-card__scale">
            <span>0</span>
            <span>20 · {{ __('home.breath.poor') }}</span>
            <span>40 · {{ __('home.breath.medium') }}</span>
            <span>60s</span>
        </div>
    </div>

    <div class="breath-card__actions">
        <button type="button" class="btn btn-sm btn-brand" x-show="phase === 'idle'" @click="start()">
            <i class="bi bi-play-fill"></i> {{ __('home.breath.start') }}
        </button>
        <button type="button" class="btn btn-sm btn-accent" x-show="phase === 'holding'" @click="stop()">
            <i class="bi bi-stop-fill"></i> {{ __('home.breath.release') }}
        </button>
        <button type="button" class="btn btn-sm btn-outline-brand" x-show="phase === 'done'" @click="reset()">
            <i class="bi bi-arrow-repeat"></i> {{ __('home.breath.again') }}
        </button>
        <button
            type="button"
            class="btn btn-sm btn-brand"
            x-show="phase === 'done' && !certificateUrl && !saving"
            :disabled="claiming"
            @click="getCertificate()"
        >
            <i class="bi bi-award"></i> {{ __('home.breath.get_certificate') }}
        </button>
        <button
            type="button"
            class="btn btn-sm btn-brand"
            x-show="phase === 'done' && certificateUrl"
            @click="viewCertificate()"
            x-cloak
        >
            <i class="bi bi-eye"></i> {{ __('home.breath.view_certificate') }}
        </button>
    </div>
    <p class="breath-card__hint" x-text="hint"></p>

    <div class="breath-card__report" x-show="phase === 'done'" x-cloak aria-live="polite">
        <div class="breath-card__report-badge" :class="'is-' + report.key">
            <i class="bi" :class="report.icon" aria-hidden="true"></i>
            <strong x-text="report.label"></strong>
        </div>
        <p class="breath-card__report-msg" x-text="report.message"></p>
        <p class="breath-card__cert-status small mb-0" x-show="statusMessage" x-text="statusMessage" x-cloak></p>
    </div>

    <p class="breath-card__disclaimer">{{ __('home.breath.disclaimer') }}</p>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('breathHoldCard', () => ({
                    phase: 'idle',
                    elapsedMs: 0,
                    startedAt: 0,
                    raf: null,
                    maxMs: 60000,
                    saving: false,
                    claiming: false,
                    claimToken: null,
                    certificateUrl: null,
                    certificateImageUrl: null,
                    certificateDownloadUrl: null,
                    certificateCode: null,
                    statusMessage: '',
                    authenticated: false,
                    storeUrl: '',
                    claimUrl: '',

                    init() {
                        const el = this.$el;
                        this.authenticated = el.dataset.authenticated === '1';
                        this.storeUrl = el.dataset.storeUrl || '';
                        this.claimUrl = el.dataset.claimUrl || '';
                        const pending = localStorage.getItem('breath_hold_claim_token');
                        if (pending) this.claimToken = pending;
                    },

                    get displayTime() {
                        const total = Math.max(0, this.elapsedMs / 1000);
                        const m = Math.floor(total / 60);
                        const s = total - m * 60;
                        const sec = s.toFixed(2).padStart(5, '0');
                        return m > 0 ? String(m).padStart(2, '0') + ':' + sec : sec + 's';
                    },

                    get progressPct() {
                        return Math.min(100, (this.elapsedMs / this.maxMs) * 100);
                    },

                    get hint() {
                        if (this.phase === 'holding') return @json(__('home.breath.hint_hold'));
                        if (this.phase === 'done') return @json(__('home.breath.hint_done'));
                        return @json(__('home.breath.hint_idle'));
                    },

                    get report() {
                        const sec = this.elapsedMs / 1000;
                        if (sec < 20) {
                            return {
                                key: 'poor',
                                icon: 'bi-emoji-frown',
                                label: @json(__('home.breath.poor')),
                                message: @json(__('home.breath.report_poor')),
                            };
                        }
                        if (sec < 40) {
                            return {
                                key: 'medium',
                                icon: 'bi-emoji-neutral',
                                label: @json(__('home.breath.medium')),
                                message: @json(__('home.breath.report_medium')),
                            };
                        }
                        return {
                            key: 'healthy',
                            icon: 'bi-emoji-smile',
                            label: @json(__('home.breath.healthy')),
                            message: @json(__('home.breath.report_healthy')),
                        };
                    },

                    csrf() {
                        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    },

                    start() {
                        if (this.raf) cancelAnimationFrame(this.raf);
                        this.phase = 'holding';
                        this.elapsedMs = 0;
                        this.startedAt = performance.now();
                        this.claimToken = null;
                        this.certificateUrl = null;
                        this.certificateImageUrl = null;
                        this.certificateDownloadUrl = null;
                        this.certificateCode = null;
                        this.statusMessage = '';
                        const tick = (now) => {
                            if (this.phase !== 'holding') return;
                            this.elapsedMs = now - this.startedAt;
                            this.raf = requestAnimationFrame(tick);
                        };
                        this.raf = requestAnimationFrame(tick);
                    },

                    stop() {
                        if (this.phase !== 'holding') return;
                        if (this.raf) cancelAnimationFrame(this.raf);
                        this.raf = null;
                        this.elapsedMs = performance.now() - this.startedAt;
                        this.phase = 'done';
                        this.persistResult();
                    },

                    reset() {
                        if (this.raf) cancelAnimationFrame(this.raf);
                        this.raf = null;
                        this.phase = 'idle';
                        this.elapsedMs = 0;
                        this.startedAt = 0;
                        this.saving = false;
                        this.claiming = false;
                        this.claimToken = null;
                        this.certificateUrl = null;
                        this.certificateImageUrl = null;
                        this.certificateDownloadUrl = null;
                        this.certificateCode = null;
                        this.statusMessage = '';
                    },

                    applyCertificatePayload(data) {
                        this.claimToken = data.claim_token || this.claimToken;
                        this.certificateUrl = data.certificate_url || null;
                        this.certificateImageUrl = data.image_url || null;
                        this.certificateDownloadUrl = data.download_url || null;
                        this.certificateCode = data.certificate_code || null;
                        if (this.certificateUrl || this.certificateImageUrl) {
                            localStorage.removeItem('breath_hold_claim_token');
                        } else if (this.claimToken) {
                            localStorage.setItem('breath_hold_claim_token', this.claimToken);
                        }
                    },

                    viewCertificate() {
                        if (typeof window.openBreathHoldCertificate === 'function') {
                            if (this.certificateImageUrl) {
                                window.openBreathHoldCertificate({
                                    image_url: this.certificateImageUrl,
                                    download_url: this.certificateDownloadUrl,
                                    view_url: this.certificateUrl,
                                    certificate_code: this.certificateCode,
                                    duration: this.displayTime,
                                    band_label: this.report.label,
                                    funny_title: @json(__('home.breath.title')),
                                });
                                return;
                            }
                            if (this.certificateUrl) {
                                window.openBreathHoldCertificate(this.certificateUrl);
                                return;
                            }
                        }
                        if (this.certificateUrl) {
                            window.location.href = this.certificateUrl;
                        }
                    },

                    persistResult() {
                        if (!this.storeUrl || this.saving) return;
                        this.saving = true;
                        this.statusMessage = @json(__('home.breath.saving'));

                        fetch(this.storeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ duration_ms: Math.round(this.elapsedMs) }),
                            credentials: 'same-origin',
                        })
                            .then((r) => r.json().then((data) => ({ ok: r.ok, data })))
                            .then(({ ok, data }) => {
                                if (!ok) throw new Error(data.message || 'Save failed');
                                this.applyCertificatePayload(data);
                                if (this.certificateUrl) {
                                    this.statusMessage = @json(__('home.breath.ready'));
                                } else {
                                    this.statusMessage = @json(__('home.breath.signup_hint'));
                                }
                            })
                            .catch(() => {
                                this.statusMessage = @json(__('home.breath.save_failed'));
                            })
                            .finally(() => {
                                this.saving = false;
                            });
                    },

                    getCertificate() {
                        if (this.saving || this.claiming) return;

                        if (this.certificateUrl || this.certificateImageUrl) {
                            this.viewCertificate();
                            return;
                        }

                        if (!this.claimToken) {
                            this.persistResult();
                            this.statusMessage = @json(__('home.breath.saving'));
                            return;
                        }

                        if (!this.authenticated) {
                            localStorage.setItem('breath_hold_claim_token', this.claimToken);
                            this.statusMessage = @json(__('home.breath.signup_required'));
                            if (typeof window.openAuthModal === 'function') {
                                window.openAuthModal('register');
                            } else {
                                window.location.href = '/register?page=1';
                            }
                            return;
                        }

                        this.claimCertificate();
                    },

                    claimCertificate() {
                        if (!this.claimUrl || !this.claimToken) return;
                        this.claiming = true;
                        this.statusMessage = @json(__('home.breath.claiming'));

                        fetch(this.claimUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ claim_token: this.claimToken }),
                            credentials: 'same-origin',
                        })
                            .then((r) => r.json().then((data) => ({ ok: r.ok, status: r.status, data })))
                            .then(({ ok, status, data }) => {
                                if (status === 401) {
                                    this.authenticated = false;
                                    if (typeof window.openAuthModal === 'function') {
                                        window.openAuthModal('register');
                                    }
                                    throw new Error(data.message || 'Auth required');
                                }
                                if (!ok) throw new Error(data.message || 'Claim failed');
                                this.applyCertificatePayload(data);
                                this.statusMessage = @json(__('home.breath.ready'));
                                this.viewCertificate();
                            })
                            .catch((err) => {
                                this.statusMessage = err.message || @json(__('home.breath.claim_failed'));
                            })
                            .finally(() => {
                                this.claiming = false;
                            });
                    },
                }));
            });
        </script>
    @endpush
@endonce

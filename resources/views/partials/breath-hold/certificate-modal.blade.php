{{-- Shared Breath Hold certificate preview modal (AJAX, no page refresh) --}}
<div class="modal fade" id="breathHoldCertModal" tabindex="-1" aria-labelledby="breathHoldCertModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-0" id="breathHoldCertModalTitle">Breath Hold certificate</h2>
                    <p class="small text-muted mb-0" id="breathHoldCertModalMeta"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="breathHoldCertLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-success" role="status" aria-hidden="true"></div>
                    <p class="small text-muted mt-2 mb-0">Loading certificate…</p>
                </div>
                <div id="breathHoldCertError" class="alert alert-danger d-none mb-0" role="alert"></div>
                <div id="breathHoldCertBody" class="d-none">
                    <div class="text-center mb-3">
                        <img id="breathHoldCertImage" src="" alt="Breath Hold certificate" class="img-fluid rounded border breath-cert-modal-img">
                    </div>
                    <div class="row g-2 small text-start" id="breathHoldCertDetails"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a id="breathHoldCertDownload" href="#" class="btn btn-brand" download>
                    <i class="bi bi-download"></i> Download PNG
                </a>
            </div>
        </div>
    </div>
</div>

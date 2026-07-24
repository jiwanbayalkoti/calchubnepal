/**
 * Visiting Card Designer — live preview + PNG/PDF download.
 */
(function ($) {
  'use strict';

  const root = document.getElementById('visitingCardApp');
  if (!root) return;

  const form = document.getElementById('visitingCardForm');
  const previewUrl = root.dataset.previewUrl;
  const downloadUrl = root.dataset.downloadUrl;
  const logoUrl = root.dataset.logoUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const i18n = {
    uploading: root.dataset.logoUploading || 'Uploading logo…',
    ready: root.dataset.logoReady || 'Logo added — visible on preview',
  };

  const els = {
    image: document.getElementById('vcPreviewImage'),
    placeholder: document.getElementById('vcPreviewPlaceholder'),
    loading: document.getElementById('vcPreviewLoading'),
    btnPng: document.getElementById('vcDownloadPng'),
    btnPdf: document.getElementById('vcDownloadPdf'),
    btnPreview: document.getElementById('vcPreviewBtn'),
    btnReset: document.getElementById('vcResetBtn'),
    logoInput: document.getElementById('vc_logo'),
    logoToken: document.getElementById('vc_logo_token'),
    logoStatus: document.getElementById('vcLogoStatus'),
    logoBox: document.getElementById('vcLogoBox'),
    logoDrop: document.getElementById('vcLogoDrop'),
    logoPreviewWrap: document.getElementById('vcLogoPreviewWrap'),
    logoPreview: document.getElementById('vcLogoPreview'),
    logoPlaceholder: document.getElementById('vcLogoPlaceholder'),
    logoPickBtn: document.getElementById('vcLogoPickBtn'),
    logoRemoveBtn: document.getElementById('vcLogoRemoveBtn'),
    includeQr: document.getElementById('vc_include_qr'),
  };

  let debounceTimer = null;
  let requestSeq = 0;
  let lastImage = '';
  let logoUploading = false;
  let localPreviewUrl = '';

  function syncTemplates() {
    form.querySelectorAll('.vc-template-card').forEach((card) => {
      const input = card.querySelector('input');
      card.classList.toggle('is-active', !!input?.checked);
    });
  }

  function applyTemplateColors(input) {
    if (!input) return;
    const map = {
      vc_primary: input.dataset.primary,
      vc_secondary: input.dataset.secondary,
      vc_text: input.dataset.text,
      vc_bg: input.dataset.background,
    };
    Object.entries(map).forEach(([id, value]) => {
      const el = document.getElementById(id);
      if (el && value) el.value = value;
    });
  }

  function filterTemplates(category) {
    form.querySelectorAll('.vc-template-card').forEach((card) => {
      const match = category === 'all' || card.dataset.category === category;
      card.classList.toggle('is-hidden', !match);
    });
    document.querySelectorAll('.vc-filter-btn').forEach((btn) => {
      const active = btn.dataset.filter === category;
      btn.classList.toggle('is-active', active);
      btn.classList.toggle('btn-brand', active);
      btn.classList.toggle('btn-outline-brand', !active);
    });
  }

  function clearErrors() {
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    form.querySelectorAll('[data-error-for]').forEach((el) => {
      el.textContent = '';
      el.style.display = 'none';
    });
  }

  function showErrors(errors) {
    clearErrors();
    Object.keys(errors || {}).forEach((key) => {
      const messages = errors[key];
      const msg = Array.isArray(messages) ? messages[0] : String(messages);
      const feedback = form.querySelector('[data-error-for="' + key + '"]');
      if (feedback) {
        feedback.textContent = msg;
        feedback.style.display = 'block';
      }
      const field = form.querySelector('[name="' + key + '"]');
      if (field) field.classList.add('is-invalid');
      if (!feedback && window.toastr) toastr.error(msg);
    });
  }

  function buildFormData() {
    const fd = new FormData(form);
    if (!els.includeQr.checked) {
      fd.set('include_qr', '0');
    } else {
      fd.set('include_qr', '1');
    }
    if (els.logoToken.value) {
      fd.delete('logo');
    }
    return fd;
  }

  function setLoading(on) {
    els.loading.classList.toggle('d-none', !on);
  }

  function setPreview(dataUrl) {
    lastImage = dataUrl || '';
    if (dataUrl) {
      els.image.src = dataUrl;
      els.image.classList.remove('d-none');
      els.placeholder.classList.add('d-none');
      els.btnPng.disabled = false;
      els.btnPdf.disabled = false;
    } else {
      els.image.classList.add('d-none');
      els.image.removeAttribute('src');
      els.placeholder.classList.remove('d-none');
      els.btnPng.disabled = true;
      els.btnPdf.disabled = true;
    }
  }

  function setLogoUi(fileOrUrl) {
    const hasLogo = !!fileOrUrl || !!els.logoToken.value;
    els.logoBox?.classList.toggle('has-logo', hasLogo);
    els.logoRemoveBtn?.classList.toggle('d-none', !hasLogo);
    els.logoPlaceholder?.classList.toggle('d-none', hasLogo);
    els.logoPreviewWrap?.classList.toggle('d-none', !hasLogo);

    if (localPreviewUrl) {
      URL.revokeObjectURL(localPreviewUrl);
      localPreviewUrl = '';
    }

    if (fileOrUrl instanceof File) {
      localPreviewUrl = URL.createObjectURL(fileOrUrl);
      if (els.logoPreview) els.logoPreview.src = localPreviewUrl;
    } else if (typeof fileOrUrl === 'string' && fileOrUrl) {
      if (els.logoPreview) els.logoPreview.src = fileOrUrl;
    } else if (!hasLogo && els.logoPreview) {
      els.logoPreview.removeAttribute('src');
    }
  }

  function clearLogo(refreshPreview = true) {
    els.logoToken.value = '';
    if (els.logoInput) els.logoInput.value = '';
    if (els.logoStatus) els.logoStatus.textContent = '';
    setLogoUi(null);
    if (refreshPreview) schedulePreview();
  }

  function preview() {
    if (logoUploading) return;
    const seq = ++requestSeq;
    setLoading(true);
    clearErrors();

    $.ajax({
      url: previewUrl,
      method: 'POST',
      data: buildFormData(),
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    })
      .done(function (res) {
        if (seq !== requestSeq) return;
        if (res.success && res.data?.image) {
          setPreview(res.data.image);
        } else {
          setPreview('');
          if (window.toastr) toastr.error(res.message || 'Preview failed');
        }
      })
      .fail(function (xhr) {
        if (seq !== requestSeq) return;
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          showErrors(xhr.responseJSON.errors);
        } else if (window.toastr) {
          toastr.error(xhr.responseJSON?.message || 'Unable to preview card');
        }
      })
      .always(function () {
        if (seq === requestSeq) setLoading(false);
      });
  }

  function schedulePreview() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(preview, 450);
  }

  function download(format) {
    if (!lastImage && format === 'png') {
      preview();
      return;
    }
    const fd = buildFormData();
    fd.set('format', format);

    setLoading(true);
    fetch(downloadUrl, {
      method: 'POST',
      body: fd,
      headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/octet-stream' },
    })
      .then(async (res) => {
        if (!res.ok) {
          let msg = 'Download failed';
          try {
            const json = await res.json();
            msg = json.message || msg;
            if (json.errors) showErrors(json.errors);
          } catch (_) {}
          throw new Error(msg);
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = format === 'pdf' ? 'visiting-card.pdf' : 'visiting-card.png';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      })
      .catch((err) => {
        if (window.toastr) toastr.error(err.message || 'Download failed');
      })
      .finally(() => setLoading(false));
  }

  function uploadLogo(file) {
    if (!file) return;
    if (!/^image\/(png|jpeg|jpg|webp|gif)$/i.test(file.type)) {
      if (window.toastr) toastr.error('Please upload PNG, JPG, WebP or GIF');
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      if (window.toastr) toastr.error('Logo must be 2MB or smaller');
      return;
    }

    logoUploading = true;
    setLogoUi(file);
    if (els.logoStatus) els.logoStatus.textContent = i18n.uploading;

    const fd = new FormData();
    fd.append('logo', file);

    $.ajax({
      url: logoUrl,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    })
      .done(function (res) {
        if (res.success && res.data?.logo_token) {
          els.logoToken.value = res.data.logo_token;
          if (els.logoStatus) els.logoStatus.textContent = i18n.ready;
          schedulePreview();
        } else {
          clearLogo(false);
          if (window.toastr) toastr.error(res.message || 'Logo upload failed');
        }
      })
      .fail(function (xhr) {
        clearLogo(false);
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          showErrors(xhr.responseJSON.errors);
        } else if (window.toastr) {
          toastr.error(xhr.responseJSON?.message || 'Logo upload failed');
        }
      })
      .always(function () {
        logoUploading = false;
      });
  }

  function resetForm() {
    form.reset();
    clearLogo(false);
    document.querySelector('input[name="template"]')?.click();
    document.getElementById('vc_primary').value = '#0B6E4F';
    document.getElementById('vc_secondary').value = '#F4A259';
    document.getElementById('vc_text').value = '#1A1A1A';
    document.getElementById('vc_bg').value = '#FFFFFF';
    clearErrors();
    syncTemplates();
    setPreview('');
    schedulePreview();
  }

  function openLogoPicker() {
    els.logoInput?.click();
  }

  form.addEventListener('input', schedulePreview);
  form.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'template') {
      syncTemplates();
      applyTemplateColors(e.target);
    }
    if (e.target && e.target.id === 'vc_logo') return;
    schedulePreview();
  });

  document.getElementById('vcTemplateFilters')?.addEventListener('click', function (e) {
    const btn = e.target.closest('.vc-filter-btn');
    if (!btn) return;
    filterTemplates(btn.dataset.filter || 'all');
  });

  els.btnPreview?.addEventListener('click', preview);
  els.btnReset?.addEventListener('click', resetForm);
  els.btnPng?.addEventListener('click', () => download('png'));
  els.btnPdf?.addEventListener('click', () => download('pdf'));
  els.logoPickBtn?.addEventListener('click', openLogoPicker);
  els.logoRemoveBtn?.addEventListener('click', clearLogo);
  els.logoDrop?.addEventListener('click', openLogoPicker);
  els.logoDrop?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      openLogoPicker();
    }
  });

  ['dragenter', 'dragover'].forEach((evt) => {
    els.logoDrop?.addEventListener(evt, function (e) {
      e.preventDefault();
      e.stopPropagation();
      els.logoDrop.classList.add('is-dragover');
    });
  });
  ['dragleave', 'drop'].forEach((evt) => {
    els.logoDrop?.addEventListener(evt, function (e) {
      e.preventDefault();
      e.stopPropagation();
      els.logoDrop.classList.remove('is-dragover');
    });
  });
  els.logoDrop?.addEventListener('drop', function (e) {
    const file = e.dataTransfer?.files?.[0];
    if (file) uploadLogo(file);
  });

  els.logoInput?.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (file) uploadLogo(file);
  });

  syncTemplates();
  const checkedTemplate = form.querySelector('input[name="template"]:checked');
  applyTemplateColors(checkedTemplate);
  schedulePreview();
})(jQuery);

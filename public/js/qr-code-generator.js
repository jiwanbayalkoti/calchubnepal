/**
 * Phase 2 QR Code Generator — FormData preview, styles, logo, downloads, history.
 */
(function ($) {
  'use strict';

  const root = document.getElementById('qrGeneratorApp');
  if (!root) return;

  const form = document.getElementById('qrGeneratorForm');
  const previewUrl = root.dataset.previewUrl;
  const downloadUrl = root.dataset.downloadUrl;
  const logoUrl = root.dataset.logoUrl;
  const recentUrl = root.dataset.recentUrl;
  const savedUrl = root.dataset.savedUrl;
  const saveBaseUrl = root.dataset.saveUrl || '';
  const dynamicUrl = root.dataset.dynamicUrl || '';
  const isAuthenticated = root.dataset.authenticated === '1';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const els = {
    image: document.getElementById('qrPreviewImage'),
    placeholder: document.getElementById('qrPreviewPlaceholder'),
    loading: document.getElementById('qrPreviewLoading'),
    infoType: document.getElementById('qrInfoType'),
    infoSize: document.getElementById('qrInfoSize'),
    infoChars: document.getElementById('qrInfoChars'),
    infoPayload: document.getElementById('qrInfoPayload'),
    btnPng: document.getElementById('qrDownloadPng'),
    btnSvg: document.getElementById('qrDownloadSvg'),
    btnJpg: document.getElementById('qrDownloadJpg'),
    btnWebp: document.getElementById('qrDownloadWebp'),
    btnPdf: document.getElementById('qrDownloadPdf'),
    btnCopyImage: document.getElementById('qrCopyImage'),
    btnCopyContent: document.getElementById('qrCopyContent'),
    btnReset: document.getElementById('qrResetBtn'),
    logoInput: document.getElementById('qr_logo'),
    logoToken: document.getElementById('qr_logo_token'),
    saveHistory: document.getElementById('qr_save_history'),
    logoStatus: document.getElementById('qrLogoStatus'),
    historyList: document.getElementById('qrHistoryList'),
  };

  let debounceTimer = null;
  let lastPayload = '';
  let lastImage = '';
  let requestSeq = 0;
  let historyTab = 'recent';
  let logoUploading = false;

  function activeType() {
    return form.querySelector('input[name="type"]:checked')?.value || 'url';
  }

  function syncRelatedBlog() {
    const box = document.getElementById('qrRelatedBlog');
    if (!box) return;
    const guides = window.QR_GUIDE_BLOGS || {};
    const guide = guides[activeType()];
    if (!guide || !guide.content) {
      box.classList.add('d-none');
      return;
    }
    const title = document.getElementById('qrRelatedBlogTitle');
    const excerpt = document.getElementById('qrRelatedBlogExcerpt');
    const link = document.getElementById('qrRelatedBlogLink');
    const meta = document.getElementById('qrRelatedBlogMeta');
    const body = document.getElementById('qrRelatedBlogBody');
    if (title) title.textContent = guide.title || '';
    if (excerpt) excerpt.textContent = guide.excerpt || '';
    if (body) body.innerHTML = guide.content || '';
    if (link) {
      link.href = guide.url || '#';
      link.classList.toggle('d-none', !guide.url);
    }
    if (meta) {
      const bits = [];
      if (guide.reading_time) bits.push(guide.reading_time + ' min read');
      if (guide.published_at) bits.push('Published ' + guide.published_at);
      if (guide.keywords) bits.push(guide.keywords.split(',').slice(0, 3).join(',').trim());
      meta.textContent = bits.join(' · ');
    }
    box.classList.remove('d-none');
  }

  function syncTypeFields() {
    const type = activeType();
    form.querySelectorAll('.qr-field-group').forEach((group) => {
      const types = (group.getAttribute('data-types') || '').split(/\s+/);
      const active = types.includes(type);
      group.classList.toggle('d-none', !active);
      group.querySelectorAll('input, textarea, select').forEach((input) => {
        input.disabled = !active;
      });
    });
    form.querySelectorAll('.qr-type-card').forEach((card) => {
      const input = card.querySelector('input');
      card.classList.toggle('is-active', !!input?.checked);
    });
    syncRelatedBlog();
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
      let selector = '[name="' + key + '"]';
      if (key.startsWith('input.')) {
        const parts = key.split('.');
        selector = '[name="input[' + parts.slice(1).join('][') + ']"]';
      }
      const field = form.querySelector(selector + ':not([disabled])');
      if (field) field.classList.add('is-invalid');
      if (!feedback && window.toastr) toastr.error(msg);
    });
  }

  function hasMeaningfulInput() {
    const group = form.querySelector('.qr-field-group:not(.d-none)');
    if (!group) return false;
    let found = false;
    group.querySelectorAll('input, textarea, select').forEach((input) => {
      if (input.disabled) return;
      if (input.type === 'checkbox' || input.type === 'radio') {
        if (input.checked) found = true;
        return;
      }
      if (String(input.value || '').trim() !== '') found = true;
    });
    return found;
  }

  function buildFormData(extra) {
    const fd = new FormData(form);
    // Logo is uploaded separately; send token only.
    fd.delete('logo');
    const token = els.logoToken?.value || '';
    if (token) {
      fd.set('logo_token', token);
    } else {
      fd.delete('logo_token');
    }
    Object.keys(extra || {}).forEach((key) => {
      fd.set(key, extra[key]);
    });
    return fd;
  }

  function setLoading(on) {
    els.loading.classList.toggle('d-none', !on);
  }

  function downloadButtons() {
    return [els.btnPng, els.btnSvg, els.btnJpg, els.btnWebp, els.btnPdf, els.btnCopyImage, els.btnCopyContent];
  }

  function setActionsEnabled(on) {
    downloadButtons().forEach((btn) => {
      if (btn) btn.disabled = !on;
    });
  }

  function renderPreview(data) {
    lastPayload = data.payload || '';
    lastImage = data.image || '';

    if (data.image) {
      els.image.src = data.image;
      els.image.classList.remove('d-none');
      els.placeholder.classList.add('d-none');
    }

    els.infoType.textContent = data.type_label || data.type || '—';
    els.infoSize.textContent = data.size ? data.size + 'px' : '—';
    els.infoChars.textContent = data.characters != null ? String(data.characters) : '—';
    els.infoPayload.textContent = data.payload || '—';
    setActionsEnabled(!!data.image);
  }

  function preview(force, saveHistory) {
    if (logoUploading) return;
    if (!hasMeaningfulInput() && !force) return;

    clearErrors();
    if (els.saveHistory) {
      els.saveHistory.value = saveHistory ? '1' : '0';
    }

    const seq = ++requestSeq;
    setLoading(true);

    const fd = buildFormData({
      save_history: saveHistory ? '1' : '0',
    });

    $.ajax({
      url: previewUrl,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .done(function (res) {
        if (seq !== requestSeq) return;
        if (res.success && res.data) {
          renderPreview(res.data);
          if (saveHistory) {
            loadHistory('recent');
          }
        }
      })
      .fail(function (xhr) {
        if (seq !== requestSeq) return;
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          showErrors(xhr.responseJSON.errors);
        } else if (xhr.responseJSON?.message && window.toastr) {
          toastr.error(xhr.responseJSON.message);
        }
      })
      .always(function () {
        if (seq === requestSeq) setLoading(false);
        if (els.saveHistory) els.saveHistory.value = '0';
      });
  }

  function schedulePreview() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
      preview(false, false);
    }, 450);
  }

  function download(format) {
    const fd = buildFormData({
      format: format,
      save_history: '1',
    });
    setLoading(true);

    fetch(downloadUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json, image/*, application/pdf',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: fd,
    })
      .then(async (res) => {
        if (!res.ok) {
          let message = 'Download failed.';
          try {
            const json = await res.json();
            if (json.errors) showErrors(json.errors);
            if (json.message) message = json.message;
          } catch (e) {}
          throw new Error(message);
        }
        const blob = await res.blob();
        const cd = res.headers.get('Content-Disposition') || '';
        const match = cd.match(/filename=\"?([^\";]+)\"?/i);
        const filename = match ? match[1] : 'qr-code.' + format;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        if (window.toastr) toastr.success(format.toUpperCase() + ' downloaded');
        loadHistory(historyTab);
      })
      .catch((err) => {
        if (window.toastr) toastr.error(err.message || 'Download failed');
      })
      .finally(() => setLoading(false));
  }

  function uploadLogo(file) {
    if (!file || !logoUrl) return;
    logoUploading = true;
    if (els.logoStatus) els.logoStatus.textContent = 'Uploading logo…';

    const fd = new FormData();
    fd.append('logo', file);

    $.ajax({
      url: logoUrl,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .done(function (res) {
        if (res.success && res.data?.logo_token) {
          if (els.logoToken) els.logoToken.value = res.data.logo_token;
          if (els.logoStatus) els.logoStatus.textContent = 'Logo ready';
          if (window.toastr) toastr.success('Logo uploaded');
          schedulePreview();
        }
      })
      .fail(function (xhr) {
        if (els.logoToken) els.logoToken.value = '';
        if (els.logoStatus) els.logoStatus.textContent = 'Logo upload failed';
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

  async function copyImage() {
    if (!lastImage) return;
    try {
      const res = await fetch(lastImage);
      const blob = await res.blob();
      if (navigator.clipboard && window.ClipboardItem) {
        await navigator.clipboard.write([new ClipboardItem({ [blob.type]: blob })]);
        if (window.toastr) toastr.success('QR image copied');
      } else {
        throw new Error('Clipboard image not supported');
      }
    } catch (e) {
      if (window.toastr) toastr.error('Could not copy image on this browser. Download PNG instead.');
    }
  }

  async function copyContent() {
    if (!lastPayload) return;
    try {
      await navigator.clipboard.writeText(lastPayload);
      if (window.toastr) toastr.success('QR content copied');
    } catch (e) {
      if (window.toastr) toastr.error('Could not copy content');
    }
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderHistoryItems(items) {
    const list = els.historyList;
    if (!list) return;
    const emptyMsg = list.dataset.empty || 'No history yet.';

    if (!items || !items.length) {
      list.innerHTML = '<p class="text-muted-custom mb-0 qr-history-empty">' + escapeHtml(emptyMsg) + '</p>';
      return;
    }

    list.innerHTML = items
      .map(function (item) {
        const thumb = item.preview_url
          ? '<img src="' + escapeHtml(item.preview_url) + '" alt="" width="48" height="48" loading="lazy">'
          : '<i class="bi bi-qr-code"></i>';
        const title = escapeHtml(item.title || item.type || 'QR');
        const meta = escapeHtml((item.type || '') + (item.created_at ? ' · ' + item.created_at : ''));
        let actions = '';
        if (isAuthenticated && historyTab === 'recent' && !item.is_saved) {
          actions +=
            '<button type="button" class="btn btn-sm btn-soft js-qr-save" data-uuid="' +
            escapeHtml(item.uuid) +
            '" title="Save"><i class="bi bi-bookmark"></i></button>';
        }
        actions +=
          '<button type="button" class="btn btn-sm btn-soft js-qr-delete" data-uuid="' +
          escapeHtml(item.uuid) +
          '" title="Delete"><i class="bi bi-trash"></i></button>';

        return (
          '<article class="qr-history-item" data-uuid="' +
          escapeHtml(item.uuid) +
          '">' +
          '<div class="qr-history-thumb">' +
          thumb +
          '</div>' +
          '<div class="qr-history-meta"><strong>' +
          title +
          '</strong><span class="text-muted-custom">' +
          meta +
          '</span></div>' +
          '<div class="qr-history-actions">' +
          actions +
          '</div></article>'
        );
      })
      .join('');
  }

  function loadHistory(tab) {
    historyTab = tab || historyTab;
    const url = historyTab === 'saved' ? savedUrl : recentUrl;
    if (!url) return;
    if (historyTab === 'saved' && !isAuthenticated) return;

    $.ajax({
      url: url,
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .done(function (res) {
        if (res.success) {
          renderHistoryItems(res.data || []);
        }
      })
      .fail(function (xhr) {
        if (xhr.status === 401 && window.toastr) {
          toastr.error(xhr.responseJSON?.message || 'Login required');
        }
      });
  }

  function saveItem(uuid) {
    if (!uuid || !saveBaseUrl) return;
    $.ajax({
      url: saveBaseUrl.replace(/\/$/, '') + '/' + encodeURIComponent(uuid) + '/save',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .done(function (res) {
        if (res.success) {
          if (window.toastr) toastr.success('Saved to favorites');
          loadHistory(historyTab);
        }
      })
      .fail(function (xhr) {
        if (window.toastr) toastr.error(xhr.responseJSON?.message || 'Could not save');
      });
  }

  function deleteItem(uuid) {
    if (!uuid || !saveBaseUrl) return;
    $.ajax({
      url: saveBaseUrl.replace(/\/$/, '') + '/' + encodeURIComponent(uuid),
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .done(function (res) {
        if (res.success) {
          if (window.toastr) toastr.success('Deleted');
          loadHistory(historyTab);
        }
      })
      .fail(function (xhr) {
        if (window.toastr) toastr.error(xhr.responseJSON?.message || 'Could not delete');
      });
  }

  function resetForm() {
    form.reset();
    form.querySelector('input[name="type"][value="url"]').checked = true;
    form.querySelector('[name="size"]').value = '256';
    form.querySelector('[name="error_correction"]').value = 'M';
    form.querySelector('[name="foreground"]').value = '#0B6E4F';
    form.querySelector('[name="background"]').value = '#FFFFFF';
    form.querySelector('[name="margin"]').value = '10';
    if (form.querySelector('[name="module_style"]')) form.querySelector('[name="module_style"]').value = 'square';
    if (form.querySelector('[name="eye_style"]')) form.querySelector('[name="eye_style"]').value = 'square';
    if (form.querySelector('[name="frame_style"]')) form.querySelector('[name="frame_style"]').value = 'none';
    if (form.querySelector('[name="logo_size"]')) form.querySelector('[name="logo_size"]').value = '64';
    if (els.logoToken) els.logoToken.value = '';
    if (els.saveHistory) els.saveHistory.value = '0';
    if (els.logoStatus) els.logoStatus.textContent = els.logoStatus.dataset.default || 'PNG, JPG, WebP or GIF · max 2MB';
    syncTypeFields();
    clearErrors();
    lastPayload = '';
    lastImage = '';
    els.image.src = '';
    els.image.classList.add('d-none');
    els.placeholder.classList.remove('d-none');
    els.infoType.textContent = '—';
    els.infoSize.textContent = '—';
    els.infoChars.textContent = '—';
    els.infoPayload.textContent = '—';
    setActionsEnabled(false);
  }

  // Events
  form.querySelectorAll('.js-qr-type').forEach((input) => {
    input.addEventListener('change', function () {
      syncTypeFields();
      schedulePreview();
    });
  });

  form.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'qr_logo') return;
    schedulePreview();
  });
  form.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'qr_logo') return;
    schedulePreview();
  });

  els.logoInput?.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) {
      if (els.logoToken) els.logoToken.value = '';
      return;
    }
    uploadLogo(file);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    preview(true, true);
  });

  els.btnPng?.addEventListener('click', () => download('png'));
  els.btnSvg?.addEventListener('click', () => download('svg'));
  els.btnJpg?.addEventListener('click', () => download('jpg'));
  els.btnWebp?.addEventListener('click', () => download('webp'));
  els.btnPdf?.addEventListener('click', () => download('pdf'));
  els.btnCopyImage?.addEventListener('click', copyImage);
  els.btnCopyContent?.addEventListener('click', copyContent);
  els.btnReset?.addEventListener('click', resetForm);

  root.querySelectorAll('.js-qr-history-tab').forEach((btn) => {
    btn.addEventListener('click', function () {
      root.querySelectorAll('.js-qr-history-tab').forEach((b) => {
        b.classList.remove('is-active');
        b.setAttribute('aria-selected', 'false');
      });
      this.classList.add('is-active');
      this.setAttribute('aria-selected', 'true');
      loadHistory(this.dataset.tab || 'recent');
    });
  });

  els.historyList?.addEventListener('click', function (e) {
    const saveBtn = e.target.closest('.js-qr-save');
    if (saveBtn) {
      e.preventDefault();
      saveItem(saveBtn.dataset.uuid);
      return;
    }
    const delBtn = e.target.closest('.js-qr-delete');
    if (delBtn) {
      e.preventDefault();
      deleteItem(delBtn.dataset.uuid);
    }
  });

  if (els.logoStatus) {
    els.logoStatus.dataset.default = els.logoStatus.textContent;
  }

  syncTypeFields();

  const dynamicToggle = document.getElementById('qr_is_dynamic');
  const dynamicFields = document.getElementById('qrDynamicFields');
  const dynamicStatus = document.getElementById('qrDynamicStatus');
  const createDynamicBtn = document.getElementById('qrCreateDynamicBtn');

  function syncDynamicFields() {
    if (!dynamicFields || !dynamicToggle) return;
    dynamicFields.classList.toggle('d-none', !dynamicToggle.checked);
  }

  dynamicToggle?.addEventListener('change', syncDynamicFields);
  syncDynamicFields();

  createDynamicBtn?.addEventListener('click', function () {
    if (!isAuthenticated || !dynamicUrl) {
      if (window.toastr) toastr.info('Please log in to create dynamic QR codes.');
      return;
    }
    if (activeType() !== 'url') {
      if (window.toastr) toastr.warning('Switch QR type to Website URL for dynamic QR.');
      return;
    }
    const urlInput = form.querySelector('[name="input[url]"]:not([disabled])');
    const destination = (urlInput?.value || '').trim();
    if (!destination) {
      if (window.toastr) toastr.error('Enter a website URL first.');
      urlInput?.focus();
      return;
    }

    clearErrors();
    setLoading(true);
    if (dynamicStatus) dynamicStatus.textContent = 'Creating dynamic QR…';

    const fd = buildFormData();
    fd.set('destination_url', destination);
    fd.set('title', document.getElementById('qr_dynamic_title')?.value || '');
    fd.set('password', document.getElementById('qr_dynamic_password')?.value || '');
    const expires = document.getElementById('qr_dynamic_expires')?.value || '';
    if (expires) fd.set('expires_at', expires);

    $.ajax({
      url: dynamicUrl,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    })
      .done(function (res) {
        if (!res.success || !res.data) {
          if (window.toastr) toastr.error(res.message || 'Failed');
          return;
        }
        const d = res.data;
        if (d.image) {
          renderPreview({
            image: d.image,
            payload: d.short_url,
            type: 'dynamic',
            type_label: 'Dynamic URL',
            size: form.querySelector('[name="size"]')?.value || 256,
            characters: (d.short_url || '').length,
          });
        }
        if (dynamicStatus) {
          dynamicStatus.innerHTML =
            'Short URL: <a href="' + d.short_url + '" target="_blank" rel="noopener">' + d.short_url + '</a>' +
            (d.manage_url ? ' · <a href="' + d.manage_url + '">Manage / analytics</a>' : '');
        }
        if (window.toastr) toastr.success('Dynamic QR created');
        if (navigator.clipboard && d.short_url) {
          navigator.clipboard.writeText(d.short_url).catch(function () {});
        }
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          showErrors(xhr.responseJSON.errors);
        }
        if (window.toastr) toastr.error(xhr.responseJSON?.message || 'Unable to create dynamic QR');
        if (dynamicStatus) dynamicStatus.textContent = '';
      })
      .always(function () {
        setLoading(false);
      });
  });
})(window.jQuery);

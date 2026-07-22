/**
 * AI Calculator Hub — public front-end behaviour.
 * AJAX calculate/explain, inline validation, dark mode, print, live search.
 * Depends on jQuery, Bootstrap 5, Toastr and SweetAlert2 already loaded by
 * layouts/public.blade.php.
 */
(function ($) {
  'use strict';

  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  if (window.$ && $.ajaxSetup) {
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': csrfToken },
    });
  }

  if (window.toastr) {
    toastr.options = {
      closeButton: true,
      progressBar: true,
      positionClass: 'toast-top-right',
      timeOut: 4000,
    };
  }

  /* ------------------------------------------------------------------ */
  /* Dark mode                                                          */
  /* ------------------------------------------------------------------ */

  const THEME_KEY = 'calc_hub_theme';
  const root = document.documentElement;

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    $('.theme-toggle i').attr('class', theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars');
  }

  function initTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    const preferred = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(preferred);
  }

  $(document).on('click', '.theme-toggle', function () {
    const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, next);
    applyTheme(next);
  });

  initTheme();

  /* ------------------------------------------------------------------ */
  /* Live search suggestions                                            */
  /* ------------------------------------------------------------------ */

  let searchTimer = null;

  $(document).on('input', '.js-live-search', function () {
    const $input = $(this);
    const $box = $input.closest('.search-box');
    const $suggestions = $box.find('.search-suggestions');
    const term = $input.val().trim();

    clearTimeout(searchTimer);

    if (term.length < 2) {
      $suggestions.remove();
      return;
    }

    searchTimer = setTimeout(function () {
      $.get('/api/search/suggest', { q: term }, function (response) {
        renderSuggestions($box, $suggestions, response.suggestions || []);
      });
    }, 250);
  });

  function renderSuggestions($box, $existing, suggestions) {
    $existing.remove();

    if (!suggestions.length) {
      return;
    }

    const $list = $('<div class="search-suggestions"></div>');

    suggestions.forEach(function (item) {
      const $item = $('<a></a>')
        .addClass('suggestion-item text-decoration-none')
        .attr('href', item.url)
        .html(
          '<i class="bi bi-calculator text-brand"></i>' +
            '<span><span class="fw-semibold d-block">' + escapeHtml(item.title) + '</span>' +
            '<span class="text-muted-custom small">' + escapeHtml(item.category || '') + '</span></span>'
        );
      $list.append($item);
    });

    $box.append($list);
  }

  $(document).on('click', function (e) {
    if (!$(e.target).closest('.search-box').length) {
      $('.search-suggestions').remove();
    }
  });

  function escapeHtml(str) {
    return $('<div>').text(str || '').html();
  }

  /* ------------------------------------------------------------------ */
  /* Array field rows (repeatable groups, e.g. GPA courses / CGPA semesters) */
  /* ------------------------------------------------------------------ */

  function addArrayRow($container) {
    const $template = $container.find('.js-array-row-template');
    const $rows = $container.find('.js-array-rows');
    const index = $rows.children('.array-row').length;
    const html = $template.html().replace(/__INDEX__/g, index);
    $rows.append(html);
  }

  $(document).on('click', '.js-add-array-row', function () {
    addArrayRow($(this).closest('.js-array-field'));
  });

  $(document).on('click', '.js-remove-array-row', function () {
    const $container = $(this).closest('.js-array-field');
    $(this).closest('.array-row').remove();
    reindexArrayRows($container);
  });

  function reindexArrayRows($container) {
    const fieldName = $container.data('field');
    $container.find('.js-array-rows .array-row').each(function (index) {
      $(this).find('[name]').each(function () {
        const name = $(this).attr('name');
        const updated = name.replace(new RegExp('^' + fieldName + '\\[\\d+\\]'), fieldName + '[' + index + ']');
        $(this).attr('name', updated);
      });
    });
  }

  $(function () {
    $('.js-array-field').each(function () {
      addArrayRow($(this));
    });
  });

  /* ------------------------------------------------------------------ */
  /* Calculator form — AJAX calculate                                   */
  /* ------------------------------------------------------------------ */

  function clearErrors($form) {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').remove();
  }

  function showErrors($form, errors) {
    $.each(errors, function (field, messages) {
      const $field = $form.find('[name="' + field + '"]');
      $field.addClass('is-invalid');
      $field.after('<div class="invalid-feedback">' + escapeHtml(messages[0]) + '</div>');
    });
  }

  function renderMetric(label, value, unit) {
    return (
      '<div class="result-metric">' +
      '<div class="metric-label">' + escapeHtml(label) + '</div>' +
      '<div class="metric-value">' + escapeHtml(String(value)) + (unit ? ' <small class="fs-6 text-muted-custom">' + escapeHtml(unit) + '</small>' : '') +
      '</div></div>'
    );
  }

  function humanizeKey(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
  }

  function formatResultValue(value) {
    if (value === null || value === undefined) {
      return '—';
    }
    if (typeof value === 'number' && isFinite(value)) {
      return Number.isInteger(value)
        ? value.toLocaleString()
        : value.toLocaleString(undefined, { maximumFractionDigits: 4 });
    }
    return String(value);
  }

  function isPlainObject(value) {
    return Object.prototype.toString.call(value) === '[object Object]';
  }

  function isSlabRow(row) {
    if (!isPlainObject(row)) {
      return false;
    }
    return (
      Object.prototype.hasOwnProperty.call(row, 'range') ||
      Object.prototype.hasOwnProperty.call(row, 'rate_percent') ||
      Object.prototype.hasOwnProperty.call(row, 'taxable_in_slab') ||
      Object.prototype.hasOwnProperty.call(row, 'tax') ||
      Object.prototype.hasOwnProperty.call(row, 'tax_amount')
    );
  }

  function renderSlabTable(rows, title) {
    let html = '<div class="mt-3">';
    if (title) {
      html += '<div class="fw-semibold mb-2">' + escapeHtml(title) + '</div>';
    }
    html += '<div class="table-responsive"><table class="table breakdown-table table-sm table-striped mb-0">';
    html += '<thead><tr>' +
      '<th>Range</th><th class="text-end">Rate</th>' +
      '<th class="text-end">Amount in band</th><th class="text-end">Tax / fee</th>' +
      '</tr></thead><tbody>';

    rows.forEach(function (row) {
      const range = row.range || (
        (row.range_from !== undefined || row.range_to !== undefined)
          ? (formatResultValue(row.range_from) + ' – ' + (row.range_to === null || row.range_to === undefined ? '∞' : formatResultValue(row.range_to)))
          : (row.band || '—')
      );
      const rate = row.rate_percent !== undefined && row.rate_percent !== null
        ? formatResultValue(row.rate_percent) + (typeof row.rate_percent === 'number' ? '%' : '')
        : '—';
      const taxable = row.taxable_in_slab !== undefined ? row.taxable_in_slab : (row.amount_in_slab !== undefined ? row.amount_in_slab : '—');
      const tax = row.tax !== undefined ? row.tax : (row.tax_amount !== undefined ? row.tax_amount : '—');

      html += '<tr>' +
        '<td>' + escapeHtml(String(range)) + '</td>' +
        '<td class="text-end">' + escapeHtml(rate) + '</td>' +
        '<td class="text-end">' + escapeHtml(formatResultValue(taxable)) + '</td>' +
        '<td class="text-end fw-semibold">' + escapeHtml(formatResultValue(tax)) + '</td>' +
        '</tr>';
    });

    html += '</tbody></table></div></div>';
    return html;
  }

  function renderBreakdownValue(value) {
    if (Array.isArray(value)) {
      if (!value.length) {
        return '<span class="text-muted-custom">—</span>';
      }
      if (value.every(isSlabRow)) {
        return renderSlabTable(value, '');
      }
      if (value.every(isPlainObject)) {
        const keys = Object.keys(value[0]);
        let html = '<div class="table-responsive"><table class="table breakdown-table table-sm mb-0"><thead><tr>';
        keys.forEach(function (key) {
          html += '<th>' + escapeHtml(humanizeKey(key)) + '</th>';
        });
        html += '</tr></thead><tbody>';
        value.forEach(function (row) {
          html += '<tr>';
          keys.forEach(function (key) {
            html += '<td class="text-end">' + escapeHtml(formatResultValue(row[key])) + '</td>';
          });
          html += '</tr>';
        });
        html += '</tbody></table></div>';
        return html;
      }
      return escapeHtml(value.map(formatResultValue).join(', '));
    }

    if (isPlainObject(value)) {
      let html = '<table class="table table-sm mb-0"><tbody>';
      Object.keys(value).forEach(function (key) {
        html += '<tr><th class="fw-normal text-muted-custom">' + escapeHtml(humanizeKey(key)) + '</th>' +
          '<td class="text-end">' + escapeHtml(formatResultValue(value[key])) + '</td></tr>';
      });
      html += '</tbody></table>';
      return html;
    }

    return escapeHtml(formatResultValue(value));
  }

  /**
   * Convert a calculator form into a plain { field: value } object,
   * including array fields (e.g. GPA courses[0][grade]).
   */
  function formToInputs($form) {
    const inputs = {};

    $.each($form.serializeArray(), function (_, field) {
      if (field.name === '_token') {
        return;
      }

      const match = field.name.match(/^([^\[]+)\[(\d+)\]\[([^\]]+)\]$/);
      if (match) {
        const group = match[1];
        const index = parseInt(match[2], 10);
        const key = match[3];
        if (!Array.isArray(inputs[group])) {
          inputs[group] = [];
        }
        if (!inputs[group][index] || typeof inputs[group][index] !== 'object') {
          inputs[group][index] = {};
        }
        inputs[group][index][key] = field.value;
        return;
      }

      if (Object.prototype.hasOwnProperty.call(inputs, field.name)) {
        if (!Array.isArray(inputs[field.name])) {
          inputs[field.name] = [inputs[field.name]];
        }
        inputs[field.name].push(field.value);
      } else {
        inputs[field.name] = field.value;
      }
    });

    // Unchecked checkboxes are omitted by serializeArray — send explicit falsey values.
    $form.find('input[type="checkbox"][name]').each(function () {
      if (!Object.prototype.hasOwnProperty.call(inputs, this.name)) {
        inputs[this.name] = '0';
      }
    });

    return inputs;
  }

  function renderResults($panel, data) {
    const results = data.results || {};
    const breakdown = data.breakdown || {};
    const units = data.units || {};

    let html = '<div class="results-inner">';

    $.each(results, function (key, value) {
      html += renderMetric(humanizeKey(key), formatResultValue(value), units[key]);
    });

    if (Object.keys(breakdown).length) {
      html += '<hr class="divider-soft my-3">';
      html += '<div class="fw-semibold mb-2">Breakdown</div>';

      const scalarRows = [];
      const tableBlocks = [];

      $.each(breakdown, function (key, value) {
        if (Array.isArray(value) && value.length && value.every(isSlabRow)) {
          tableBlocks.push(renderSlabTable(value, humanizeKey(key)));
          return;
        }
        if (Array.isArray(value) && value.length && value.every(isPlainObject)) {
          tableBlocks.push('<div class="mt-3"><div class="fw-semibold mb-2">' + escapeHtml(humanizeKey(key)) + '</div>' + renderBreakdownValue(value) + '</div>');
          return;
        }
        scalarRows.push(
          '<tr><th class="fw-normal text-muted-custom">' + escapeHtml(humanizeKey(key)) + '</th>' +
          '<td class="text-end fw-semibold">' + renderBreakdownValue(value) +
          (units[key] ? ' ' + escapeHtml(units[key]) : '') + '</td></tr>'
        );
      });

      if (scalarRows.length) {
        html += '<table class="table breakdown-table table-sm mb-0"><tbody>' + scalarRows.join('') + '</tbody></table>';
      }
      html += tableBlocks.join('');
    }

    html += '</div>';

    $panel.find('.results-placeholder').remove();
    $panel.find('.results-inner').remove();
    $panel.find('.result-actions').before(html);
    $panel.find('.result-actions, .ai-explain-toggle').removeClass('d-none');

    $panel.data('lastResult', {
      inputs: (data.inputs && Object.keys(data.inputs).length) ? data.inputs : {},
      results: results,
      breakdown: breakdown,
      units: units,
    });
  }

  $(document).on('submit', '.js-calculator-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $panel = $('#' + $form.data('result-target'));
    const $loading = $panel.find('.result-loading');
    const slug = $form.data('slug');
    const $submit = $form.find('[type="submit"]');
    const formInputs = formToInputs($form);

    clearErrors($form);
    $loading.addClass('active');
    $submit.prop('disabled', true);
    $panel.find('.ai-explain-box').removeClass('active').empty();

    $.ajax({
      url: '/calculator/' + slug + '/calculate',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .done(function (response) {
        if (response.success) {
          const payload = response.data || {};
          // Prefer server-validated inputs; always fall back to what the user just submitted.
          if (!payload.inputs || !Object.keys(payload.inputs).length) {
            payload.inputs = formInputs;
          }
          renderResults($panel, payload);
          // Keep a reliable copy of the submitted inputs for AI Explain / PDF.
          const stored = $panel.data('lastResult') || {};
          stored.inputs = payload.inputs || formInputs;
          $panel.data('lastResult', stored);
          if (window.toastr) {
            toastr.success('Calculation complete!');
          }
        } else {
          if (window.toastr) {
            toastr.error(response.message || 'Unable to calculate.');
          }
        }
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          showErrors($form, xhr.responseJSON.errors);
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          if (window.toastr) {
            toastr.error(xhr.responseJSON.message);
          }
        } else {
          if (window.toastr) {
            toastr.error('Something went wrong. Please try again.');
          }
        }
      })
      .always(function () {
        $loading.removeClass('active');
        $submit.prop('disabled', false);
      });
  });

  /* ------------------------------------------------------------------ */
  /* AI Explain                                                          */
  /* ------------------------------------------------------------------ */

  $(document).on('click', '.js-ai-explain', function () {
    const $btn = $(this);
    const $panel = $btn.closest('.calc-result-card');
    const slug = $btn.data('slug');
    const lastResult = $panel.data('lastResult') || {};
    const $box = $panel.find('.ai-explain-box');
    const $form = $('.js-calculator-form[data-slug="' + slug + '"]');

    if (!lastResult.results || !Object.keys(lastResult.results).length) {
      if (window.toastr) {
        toastr.warning('Please calculate a result first.');
      }
      return;
    }

    // Re-read live form values so explain never depends on a missing inputs blob.
    const liveInputs = $form.length ? formToInputs($form) : {};
    const inputs = (lastResult.inputs && Object.keys(lastResult.inputs).length)
      ? lastResult.inputs
      : liveInputs;

    $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
    $box.addClass('active').html('<div class="text-muted-custom"><span class="spinner-border spinner-border-sm me-2"></span>Thinking...</div>');

    $.ajax({
      url: '/calculator/' + slug + '/explain',
      method: 'POST',
      dataType: 'json',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
      },
      data: {
        inputs: inputs,
        results: lastResult.results || {},
        breakdown: lastResult.breakdown || {},
        units: lastResult.units || {},
      },
    })
      .done(function (response) {
        if (response.success) {
          $box.html('<div class="d-flex gap-2"><i class="bi bi-stars text-accent fs-5"></i><div>' + escapeHtml(response.explanation).replace(/\n/g, '<br>') + '</div></div>');
        } else {
          $box.html('<span class="text-danger">' + escapeHtml(response.message || 'AI explanation unavailable.') + '</span>');
        }
      })
      .fail(function (xhr) {
        let message = 'AI explanation unavailable right now.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (xhr.responseJSON.errors) {
            const first = Object.values(xhr.responseJSON.errors)[0];
            message = Array.isArray(first) ? first[0] : String(first);
          }
        }
        $box.html('<span class="text-danger">' + escapeHtml(message) + '</span>');
      })
      .always(function () {
        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
      });
  });

  /* ------------------------------------------------------------------ */
  /* PDF download — resubmit last computed payload                      */
  /* ------------------------------------------------------------------ */

  $(document).on('click', '.js-download-pdf', function () {
    const $panel = $(this).closest('.calc-result-card');
    const slug = $(this).data('slug');
    const lastResult = $panel.data('lastResult');

    if (!lastResult) {
      if (window.toastr) {
        toastr.warning('Please calculate a result first.');
      }
      return;
    }

    const $form = $('<form>', { method: 'POST', action: '/calculator/' + slug + '/pdf', target: '_blank' });
    $form.append($('<input>', { type: 'hidden', name: '_token', value: csrfToken }));
    $form.append($('<input>', { type: 'hidden', name: 'inputs', value: JSON.stringify(lastResult.inputs || {}) }));
    $form.append($('<input>', { type: 'hidden', name: 'results', value: JSON.stringify(lastResult.results || {}) }));
    $form.append($('<input>', { type: 'hidden', name: 'breakdown', value: JSON.stringify(lastResult.breakdown || {}) }));
    $form.append($('<input>', { type: 'hidden', name: 'units', value: JSON.stringify(lastResult.units || {}) }));
    $('body').append($form);
    $form.submit();
    $form.remove();
  });

  /* ------------------------------------------------------------------ */
  /* Print                                                              */
  /* ------------------------------------------------------------------ */

  $(document).on('click', '.js-print', function () {
    window.print();
  });

  /* ------------------------------------------------------------------ */
  /* Contact form (AJAX)                                                 */
  /* ------------------------------------------------------------------ */

  $(document).on('submit', '.js-contact-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $submit = $form.find('[type="submit"]');

    clearErrors($form);
    $submit.prop('disabled', true);

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
    })
      .done(function (response) {
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Message sent!',
            text: response.message,
            confirmButtonColor: '#0B6E4F',
          });
        } else if (window.toastr) {
          toastr.success(response.message);
        }
        $form[0].reset();
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          showErrors($form, xhr.responseJSON.errors);
        } else if (window.toastr) {
          toastr.error('Unable to send your message. Please try again.');
        }
      })
      .always(function () {
        $submit.prop('disabled', false);
      });
  });

  /* ------------------------------------------------------------------ */
  /* Feedback form (AJAX)                                                */
  /* ------------------------------------------------------------------ */

  $(document).on('submit', '.js-feedback-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $submit = $form.find('[type="submit"]');

    clearErrors($form);
    $submit.prop('disabled', true);

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
    })
      .done(function (response) {
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Thanks!',
            text: response.message,
            confirmButtonColor: '#0B6E4F',
          });
        } else if (window.toastr) {
          toastr.success(response.message);
        } else {
          alert(response.message || 'Thanks for your feedback!');
        }
        $form[0].reset();
      })
      .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          showErrors($form, xhr.responseJSON.errors);
        } else if (window.toastr) {
          toastr.error('Unable to send feedback. Please try again.');
        } else {
          alert('Unable to send feedback. Please try again.');
        }
      })
      .always(function () {
        $submit.prop('disabled', false);
      });
  });

  /* ------------------------------------------------------------------ */
  /* Plan interest (AJAX)                                                */
  /* ------------------------------------------------------------------ */

  $(document).on('submit', '.js-plan-interest-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $submit = $form.find('[type="submit"]');
    $submit.prop('disabled', true);

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
    })
      .done(function (response) {
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Request received',
            text: response.message,
            confirmButtonColor: '#0B6E4F',
          });
        } else if (window.toastr) {
          toastr.success(response.message);
        } else {
          alert(response.message || 'Request sent.');
        }
      })
      .fail(function (xhr) {
        const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not send plan request.';
        if (window.toastr) {
          toastr.error(msg);
        } else {
          alert(msg);
        }
      })
      .always(function () {
        $submit.prop('disabled', false);
      });
  });

  /* ------------------------------------------------------------------ */
  /* Favorite toggle                                                     */
  /* ------------------------------------------------------------------ */

  $(document).on('click', '.js-toggle-favorite', function () {
    const $btn = $(this);
    const url = $btn.data('url');

    if (!url) {
      return;
    }

    $btn.prop('disabled', true);

    $.ajax({
      url: url,
      method: 'POST',
      dataType: 'json',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
      },
    })
      .done(function (response) {
        const favorited = !!response.favorited;
        $btn.attr('data-favorited', favorited ? '1' : '0');
        $btn.attr('aria-pressed', favorited ? 'true' : 'false');
        $btn.toggleClass('btn-brand', favorited).toggleClass('btn-soft', !favorited);
        $btn.find('i').attr('class', favorited ? 'bi bi-heart-fill' : 'bi bi-heart');
        $btn.find('.js-favorite-label').text(favorited ? 'Favorited' : 'Favorite');
        if (window.toastr) {
          toastr.success(response.message || (favorited ? 'Added to favorites.' : 'Removed from favorites.'));
        }
      })
      .fail(function (xhr) {
        if (xhr.status === 401 || xhr.status === 419) {
          if (typeof window.openAuthModal === 'function' && document.getElementById('authModal')) {
            window.openAuthModal('login');
          } else {
            window.location.href = '/login?page=1';
          }
          return;
        }
        if (window.toastr) {
          toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to update favorite.');
        }
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  /* ------------------------------------------------------------------ */
  /* Save calculation result                                             */
  /* ------------------------------------------------------------------ */

  $(document).on('click', '.js-save-result', function () {
    const $btn = $(this);
    const $panel = $btn.closest('.calc-result-card');
    const lastResult = $panel.data('lastResult');
    const slug = $btn.data('slug');
    const defaultTitle = $btn.data('title') || 'Saved calculation';

    if (!lastResult || !lastResult.results || !Object.keys(lastResult.results).length) {
      if (window.toastr) {
        toastr.warning('Please calculate a result first.');
      }
      return;
    }

    const askTitle = window.Swal
      ? Swal.fire({
          title: 'Save calculation',
          input: 'text',
          inputLabel: 'Title',
          inputValue: defaultTitle + ' — ' + new Date().toLocaleDateString(),
          inputAttributes: { maxlength: 255 },
          showCancelButton: true,
          confirmButtonText: 'Save',
          confirmButtonColor: '#0B6E4F',
          inputValidator: function (value) {
            if (!value || !value.trim()) {
              return 'Please enter a title.';
            }
          },
        })
      : Promise.resolve({
          isConfirmed: true,
          value: window.prompt('Save as:', defaultTitle) || '',
        });

    askTitle.then(function (result) {
      if (!result.isConfirmed || !result.value || !String(result.value).trim()) {
        return;
      }

      $btn.prop('disabled', true);

      $.ajax({
        url: '/account/saved',
        method: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
          calculator_slug: slug,
          title: String(result.value).trim(),
          inputs: lastResult.inputs || {},
          outputs: lastResult.results || {},
        }),
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken,
        },
      })
        .done(function (response) {
          if (window.toastr) {
            toastr.success(response.message || 'Calculation saved.');
          }
          if (response.data && response.data.url) {
            setTimeout(function () {
              // Keep user on page; optional toast already confirms save.
            }, 0);
          }
        })
        .fail(function (xhr) {
          if (xhr.status === 401 || xhr.status === 419) {
            if (typeof window.openAuthModal === 'function' && document.getElementById('authModal')) {
              window.openAuthModal('login');
            } else {
              window.location.href = '/login?page=1';
            }
            return;
          }
          const message = (xhr.responseJSON && xhr.responseJSON.message)
            || 'Unable to save this calculation.';
          if (window.toastr) {
            toastr.error(message);
          }
          if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.upgrade_url) {
            setTimeout(function () {
              window.location.href = xhr.responseJSON.upgrade_url;
            }, 1200);
          }
        })
        .always(function () {
          $btn.prop('disabled', false);
        });
    });
  });

  /* ------------------------------------------------------------------ */
  /* Auth modal (login / register popup)                                 */
  /* ------------------------------------------------------------------ */

  function getAuthModal() {
    const el = document.getElementById('authModal');
    if (!el || !window.bootstrap) {
      return null;
    }
    return bootstrap.Modal.getOrCreateInstance(el);
  }

  function switchAuthTab(tab) {
    const isLogin = tab !== 'register';
    const $title = $('#authModalTitle');
    const $subtitle = $('#authModalSubtitle');
    $('#authTabLogin').toggleClass('active', isLogin);
    $('#authTabRegister').toggleClass('active', !isLogin);
    $('#authPanelLogin').toggleClass('d-none', !isLogin);
    $('#authPanelRegister').toggleClass('d-none', isLogin);
    $title.text(
      isLogin
        ? ($title.data('login-title') || 'Welcome back')
        : ($title.data('register-title') || 'Create your account')
    );
    $subtitle.text(
      isLogin
        ? ($subtitle.data('login-subtitle') || 'Sign in to save calculations and unlock more tools.')
        : ($subtitle.data('register-subtitle') || 'Join to save work and access more tools.')
    );
    $('#loginAlert, #registerAlert').addClass('d-none').empty();
    $('#authLoginForm .is-invalid, #authRegisterForm .is-invalid').removeClass('is-invalid');
    $('#authLoginForm [data-error], #authRegisterForm [data-error]').text('');
  }

  window.openAuthModal = function (tab) {
    const modal = getAuthModal();
    if (!modal) {
      window.location.href = tab === 'register' ? '/register?page=1' : '/login?page=1';
      return;
    }
    switchAuthTab(tab || 'login');
    modal.show();
  };

  $(document).on('click', '.js-open-auth', function (e) {
    e.preventDefault();
    window.openAuthModal($(this).data('auth') || 'login');
  });

  $(document).on('click', '[data-auth-tab]', function () {
    switchAuthTab($(this).data('auth-tab'));
  });

  // Intercept plain login/register links on public pages (when modal exists).
  $(document).on('click', 'a[href]', function (e) {
    if (!document.getElementById('authModal')) {
      return;
    }
    const href = this.getAttribute('href') || '';
    if (href.indexOf('page=1') !== -1) {
      return;
    }
    try {
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin) {
        return;
      }
      if (url.pathname === '/login') {
        e.preventDefault();
        window.openAuthModal('login');
      } else if (url.pathname === '/register') {
        e.preventDefault();
        window.openAuthModal('register');
      }
    } catch (err) {
      // ignore invalid hrefs
    }
  });

  function setAuthLoading($form, loading) {
    const $btn = $form.find('[type="submit"]');
    $btn.prop('disabled', loading);
    $btn.find('.spinner-border').toggleClass('d-none', !loading);
    $btn.find('.label').toggleClass('opacity-50', loading);
  }

  function showAuthErrors($form, errors) {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('[data-error]').text('');
    $.each(errors || {}, function (field, messages) {
      const $input = $form.find('[name="' + field + '"]');
      $input.addClass('is-invalid');
      $form.find('[data-error="' + field + '"]').text(messages[0] || '');
    });
  }

  function bindAuthForm(formSelector, alertSelector) {
    $(document).on('submit', formSelector, function (e) {
      e.preventDefault();
      const $form = $(this);
      const $alert = $(alertSelector);

      $alert.addClass('d-none').empty();
      setAuthLoading($form, true);

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken,
        },
      })
        .done(function (response) {
          if (window.toastr) {
            toastr.success(response.message || 'Success');
          }
          window.location.href = response.redirect || '/account';
        })
        .fail(function (xhr) {
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            showAuthErrors($form, xhr.responseJSON.errors);
            return;
          }
          const message = (xhr.responseJSON && xhr.responseJSON.message)
            || 'Unable to continue. Please try again.';
          $alert.removeClass('d-none').text(message);
        })
        .always(function () {
          setAuthLoading($form, false);
        });
    });
  }

  bindAuthForm('#authLoginForm', '#loginAlert');
  bindAuthForm('#authRegisterForm', '#registerAlert');

  $(function () {
    const params = new URLSearchParams(window.location.search);
    const auth = params.get('auth');
    if (auth === 'login' || auth === 'register') {
      window.openAuthModal(auth);
      if (window.history && window.history.replaceState) {
        params.delete('auth');
        const next = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
        window.history.replaceState({}, '', next);
      }
    }

    const flash = document.getElementById('flashMessages');
    if (flash && window.toastr) {
      const error = flash.getAttribute('data-error');
      const status = flash.getAttribute('data-status');
      const success = flash.getAttribute('data-success');
      if (error) toastr.error(error);
      else if (success) toastr.success(success);
      else if (status) toastr.info(status);
    }
  });

  /* ------------------------------------------------------------------ */
  /* Select2 init                                                        */
  /* ------------------------------------------------------------------ */

  $(function () {
    if ($.fn.select2) {
      $('.js-select2').select2({ width: '100%', minimumResultsForSearch: 6 });
    }
  });

  /* ------------------------------------------------------------------ */
  /* Cookie consent + AdSense load                                       */
  /* ------------------------------------------------------------------ */

  const COOKIE_CONSENT_KEY = 'calc_hub_cookie_consent';

  function getCookieConsent() {
    try {
      return window.localStorage.getItem(COOKIE_CONSENT_KEY);
    } catch (e) {
      return null;
    }
  }

  function setCookieConsent(value) {
    try {
      window.localStorage.setItem(COOKIE_CONSENT_KEY, value);
    } catch (e) { /* ignore */ }
  }

  function maybeLoadAdsense() {
    const cfg = window.__calcHubAdsense || {};
    const requireConsent = cfg.requireConsent !== false;
    const consent = getCookieConsent();
    if (typeof window.calcHubLoadAdsense !== 'function') {
      return;
    }
    if (!requireConsent || consent === '1') {
      window.calcHubLoadAdsense();
    }
  }

  $(function () {
    const $banner = $('#cookieConsentBanner');
    if (!$banner.length) {
      maybeLoadAdsense();
      return;
    }

    const consent = getCookieConsent();
    if (consent === null) {
      $banner.removeClass('d-none');
    } else {
      maybeLoadAdsense();
    }

    $('#cookieConsentAccept').on('click', function () {
      setCookieConsent('1');
      $banner.addClass('d-none');
      maybeLoadAdsense();
    });

    $('#cookieConsentDecline').on('click', function () {
      setCookieConsent('0');
      $banner.addClass('d-none');
    });
  });
})(window.jQuery);

/**
 * AdminCRUD - reusable AJAX CRUD helper for the AdminLTE admin panel.
 *
 * Conventions expected by this helper:
 *  - DataTables are server-side processed against a `{module}/data` endpoint
 *    returning the standard DataTables JSON payload.
 *  - Create/Edit forms live inside Bootstrap modals and are submitted via
 *    AJAX. Validation errors (HTTP 422) are rendered inline next to each
 *    field automatically (looks for `#{field}-error` or falls back to
 *    injecting a `.invalid-feedback` node after the input).
 *  - Delete actions are confirmed through SweetAlert2 before firing the
 *    AJAX DELETE request.
 *  - Every successful mutation shows a Toastr notification and reloads the
 *    bound DataTable without a full page refresh.
 */
(function (window, $) {
    'use strict';

    const AdminCRUD = {
        table: null,

        /**
         * Initialize a server-side DataTable.
         * @param {string} selector
         * @param {object} options { ajaxUrl, columns, order, extraFilters }
         */
        initDataTable(selector, options) {
            const $table = $(selector);

            if ($.fn.DataTable.isDataTable(selector)) {
                $table.DataTable().destroy();
            }

            this.table = $table.DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: options.ajaxUrl,
                    data: function (d) {
                        if (typeof options.extraFilters === 'function') {
                            Object.assign(d, options.extraFilters());
                        }
                    },
                    error: function (xhr) {
                        AdminCRUD.notifyError(xhr, 'Failed to load data.');
                    },
                },
                columns: options.columns,
                order: options.order || [[0, 'desc']],
                pageLength: options.pageLength || 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...',
                    emptyTable: 'No records found.',
                    zeroRecords: 'No matching records found.',
                },
                dom: '<"row mb-2"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
            });

            return this.table;
        },

        reload() {
            if (this.table) {
                this.table.ajax.reload(null, false);
            }
        },

        /**
         * Reset a modal form: clear inputs, validation state, and hidden id.
         */
        resetForm(formSelector) {
            const $form = $(formSelector);
            if (!$form.length || !$form[0]) {
                return;
            }
            $form[0].reset();
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');
            $form.find('select.select2').val(null).trigger('change');
            $form.find('input[name="id"]').val('');
        },

        /**
         * Open a create modal (Bootstrap 4 jQuery API used by AdminLTE 3).
         */
        openCreate(modalSelector, formSelector, title) {
            if (formSelector) {
                this.resetForm(formSelector);
            }
            if (title) {
                $(modalSelector).find('.modal-title').first().text(title);
            }
            $(modalSelector).modal('show');
        },

        showModal(modalSelector) {
            $(modalSelector).modal('show');
        },

        hideModal(modalSelector) {
            $(modalSelector).modal('hide');
        },

        clearErrors(formSelector) {
            const $form = $(formSelector);
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');
        },

        showErrors(formSelector, errors) {
            const $form = $(formSelector);
            this.clearErrors(formSelector);

            $.each(errors, function (field, messages) {
                const $field = $form.find('[name="' + field + '"]');
                $field.addClass('is-invalid');

                let $feedback = $field.nextAll('.invalid-feedback').first();
                if ($feedback.length === 0) {
                    $feedback = $('#' + field + '-error');
                }
                if ($feedback.length === 0) {
                    $feedback = $('<div class="invalid-feedback"></div>').insertAfter($field);
                }
                $feedback.text(messages[0]);
            });
        },

        toggleLoading($btn, loading, loadingText) {
            if (loading) {
                $btn.data('original-html', $btn.html());
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm mr-1" role="status"></span> ' + (loadingText || 'Saving...')
                );
            } else {
                $btn.prop('disabled', false).html($btn.data('original-html'));
            }
        },

        notifyError(xhr, fallback) {
            let message = fallback || 'Something went wrong.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
        },

        /**
         * Bind a create/edit modal form for AJAX submission.
         *
         * @param {object} cfg {
         *   formSelector, modalSelector, submitBtnSelector,
         *   buildUrl: function(id) -> url,
         *   buildMethod: function(id) -> 'POST'|'PUT',
         *   onSuccess: function(response, id)
         * }
         */
        bindForm(cfg) {
            const self = this;
            const $form = $(cfg.formSelector);

            $form.off('submit.admincrud').on('submit.admincrud', function (e) {
                e.preventDefault();

                const $submitBtn = cfg.submitBtnSelector ? $(cfg.submitBtnSelector) : $form.find('[type="submit"]');
                const id = $form.find('input[name="id"]').val();
                const url = cfg.buildUrl(id);
                const method = cfg.buildMethod(id);

                const formData = new FormData($form[0]);
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                // Unchecked checkboxes are omitted from FormData — send explicit 0/1.
                $form.find('input[type="checkbox"]').each(function () {
                    const name = this.name;
                    if (!name) return;
                    formData.delete(name);
                    formData.append(name, this.checked ? '1' : '0');
                });

                self.clearErrors(cfg.formSelector);
                self.toggleLoading($submitBtn, true);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                    .done(function (response) {
                        toastr.success(response.message || 'Saved successfully.');
                        self.hideModal(cfg.modalSelector);
                        self.resetForm(cfg.formSelector);
                        self.reload();
                        if (typeof cfg.onSuccess === 'function') {
                            cfg.onSuccess(response, id);
                        }
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            self.showErrors(cfg.formSelector, xhr.responseJSON.errors);
                            toastr.warning('Please fix the highlighted fields.');
                        } else {
                            self.notifyError(xhr, 'Unable to save record.');
                        }
                    })
                    .always(function () {
                        self.toggleLoading($submitBtn, false);
                    });
            });
        },

        /**
         * Bind delete-confirmation buttons using SweetAlert2 + AJAX DELETE.
         * Delegated on document so dynamically-rendered DataTable rows work.
         *
         * @param {string} triggerSelector e.g. '.btn-delete'
         * @param {function} buildUrl function(id) -> url
         * @param {object} opts { title, text, confirmText }
         */
        bindDelete(triggerSelector, buildUrl, opts) {
            const self = this;
            opts = opts || {};

            $(document).off('click.admincrud-delete', triggerSelector)
                .on('click.admincrud-delete', triggerSelector, function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const name = $(this).data('name') || '';

                    Swal.fire({
                        title: opts.title || 'Are you sure?',
                        text: opts.text || ('This will permanently delete ' + (name ? '"' + name + '"' : 'this record') + '.'),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: opts.confirmText || 'Yes, delete it',
                    }).then(function (result) {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: buildUrl(id),
                            method: 'DELETE',
                        })
                            .done(function (response) {
                                toastr.success(response.message || 'Deleted successfully.');
                                self.reload();
                            })
                            .fail(function (xhr) {
                                self.notifyError(xhr, 'Unable to delete record.');
                            });
                    });
                });
        },

        /**
         * Bind a boolean toggle button (e.g. is_active / is_featured).
         * @param {string} triggerSelector
         * @param {function} buildUrl function(id) -> url
         */
        bindToggle(triggerSelector, buildUrl) {
            const self = this;

            $(document).off('click.admincrud-toggle', triggerSelector)
                .on('click.admincrud-toggle', triggerSelector, function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');

                    $.ajax({ url: buildUrl(id), method: 'PATCH' })
                        .done(function (response) {
                            toastr.success(response.message || 'Updated successfully.');
                            self.reload();
                        })
                        .fail(function (xhr) {
                            self.notifyError(xhr, 'Unable to update record.');
                        });
                });
        },

        /**
         * Populate an edit modal form via a GET AJAX call, then show it.
         * @param {string} triggerSelector
         * @param {function} buildFetchUrl function(id) -> url
         * @param {string} formSelector
         * @param {string} modalSelector
         * @param {function} [fill] custom fill callback(data, formSelector)
         */
        bindEdit(triggerSelector, buildFetchUrl, formSelector, modalSelector, fill) {
            const self = this;

            $(document).off('click.admincrud-edit', triggerSelector)
                .on('click.admincrud-edit', triggerSelector, function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');

                    $.ajax({ url: buildFetchUrl(id), method: 'GET' })
                        .done(function (response) {
                            const data = response.data || response;
                            self.resetForm(formSelector);

                            if (typeof fill === 'function') {
                                fill(data, formSelector);
                            } else {
                                self.autoFill(formSelector, data);
                            }

                            $(formSelector).find('input[name="id"]').val(data.id);
                            self.showModal(modalSelector);
                        })
                        .fail(function (xhr) {
                            self.notifyError(xhr, 'Unable to load record.');
                        });
                });
        },

        /**
         * Best-effort auto-fill: matches form field names to response keys.
         */
        autoFill(formSelector, data) {
            const $form = $(formSelector);

            $.each(data, function (key, value) {
                const $field = $form.find('[name="' + key + '"]');
                if ($field.length === 0) return;

                if ($field.is(':checkbox')) {
                    $field.prop('checked', !!value);
                } else if ($field.hasClass('select2')) {
                    $field.val(value).trigger('change');
                } else {
                    $field.val(value);
                }
            });
        },
    };

    window.AdminCRUD = AdminCRUD;
})(window, jQuery);

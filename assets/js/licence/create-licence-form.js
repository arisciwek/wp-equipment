/**
 * Create Branch Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Branch
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/licence/create-licence-form.js
 *
 * Description: Handler untuk form tambah surat keterangan.
 *              Includes form validation, AJAX submission,
 *              error handling, dan modal management.
 *              Terintegrasi dengan toast notifications.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - BranchToast for notifications
 * - WIModal for confirmations
 *
 * Last modified: 2024-12-10
 */
(function($) {
    'use strict';

    const CreateBranchForm = {
        modal: null,
        form: null,
        equipmentId: null,

        init() {
            this.modal = $('#create-licence-modal');
            this.form = $('#create-licence-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleCreate(e));
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });

            // Add button handler
            $('#add-licence-btn').on('click', () => {
                const equipmentId = window.Equipment?.currentId;
                if (equipmentId) {
                    this.showModal(equipmentId);
                } else {
                    BranchToast.error('Silakan pilih equipment terlebih dahulu');
                }
            });

            // Modal events
            $('.modal-close', this.modal).on('click', () => this.hideModal());
            $('.cancel-create', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });
        },

        showModal(equipmentId) {
            if (!equipmentId) {
                BranchToast.error('ID Equipment tidak valid');
                return;
            }

            this.equipmentId = equipmentId;
            this.form.find('#equipment_id').val(equipmentId);

            // Reset and show form
            this.resetForm();
            this.modal
                .addClass('licence-modal')
                .fadeIn(300, () => {
                    this.form.find('[name="name"]').focus();
                });
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
                this.equipmentId = null;
            });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    type: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: 'Nama surat keterangan wajib diisi',
                        minlength: 'Nama surat keterangan minimal 3 karakter',
                        maxlength: 'Nama surat keterangan maksimal 100 karakter'
                    },
                    type: {
                        required: 'Tipe surat keterangan wajib dipilih'
                    }
                },
                errorElement: 'span',
                errorClass: 'form-error',
                errorPlacement: (error, element) => {
                    error.insertAfter(element);
                },
                highlight: (element) => {
                    $(element).addClass('error');
                },
                unhighlight: (element) => {
                    $(element).removeClass('error');
                }
            });
        },

        validateField(field) {
            const $field = $(field);
            const value = $field.val().trim();
            const errors = [];

            if (!value) {
                errors.push('Nama surat keterangan wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama surat keterangan minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama surat keterangan maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama surat keterangan hanya boleh mengandung huruf dan spasi');
                }
            }

            const $error = $field.next('.form-error');
            if (errors.length > 0) {
                $field.addClass('error');
                if ($error.length) {
                    $error.text(errors[0]);
                } else {
                    $('<span class="form-error"></span>')
                        .text(errors[0])
                        .insertAfter($field);
                }
                return false;
            } else {
                $field.removeClass('error');
                $error.remove();
                return true;
            }
        },

        async handleCreate(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            const requestData = {
                action: 'create_licence',
                nonce: wpEquipmentData.nonce,
                equipment_id: this.equipmentId,
                code: this.form.find('[name="code"]').val().trim(), // Tambahkan ini
                name: this.form.find('[name="name"]').val().trim(),
                type: this.form.find('[name="type"]').val()
            };

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    BranchToast.success('Surat Keterangan berhasil ditambahkan');
                    this.hideModal();

                    $(document).trigger('licence:created', [response.data]);

                    if (window.LicenceDataTable) {
                        window.LicenceDataTable.refresh();
                    }
                } else {
                    BranchToast.error(response.data?.message || 'Gagal menambah surat keterangan');
                }
            } catch (error) {
                console.error('Create licence error:', error);
                BranchToast.error('Gagal menghubungi server. Silakan coba lagi.');
            } finally {
                this.setLoadingState(false);
            }
        },

        setLoadingState(loading) {
            const $submitBtn = this.form.find('[type="submit"]');
            const $spinner = this.form.find('.spinner');

            if (loading) {
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                this.form.addClass('loading');
            } else {
                $submitBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
                this.form.removeClass('loading');
            }
        },

        resetForm() {
            this.form[0].reset();
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.form.validate().resetForm();
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CreateBranchForm = CreateBranchForm;
        CreateBranchForm.init();
    });

})(jQuery);

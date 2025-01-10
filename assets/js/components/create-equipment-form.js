/**
 * Equipment Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/components/create-equipment-form.js
 *
 * Description: Handler untuk form equipment.
 *              Menangani create dan update equipment.
 *              Includes validasi form, error handling,
 *              dan integrasi dengan komponen lain.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - EquipmentToast for notifications
 * - Equipment main component
 * - WordPress AJAX API
 *
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper form validation
 * - Added AJAX integration
 * - Added modal management
 * - Added loading states
 * - Added error handling
 * - Added toast notifications
 * - Added panel integration
 *
 * Last modified: 2024-12-03 16:30:00
 */
 (function($) {
     'use strict';

    const CreateEquipmentForm = {
        modal: null,
        form: null,

        init() {
            this.modal = $('#create-equipment-modal');
            this.form = $('#create-equipment-form');

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
            $('#add-equipment-btn').on('click', () => this.showModal());

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

        async handleCreate(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            // Ambil semua data form termasuk user_id
            const formData = {
                action: 'create_equipment',
                nonce: wpEquipmentData.nonce,
                name: this.form.find('[name="name"]').val().trim(),
                code: this.form.find('[name="code"]').val().trim(),
            };

            // Tambahkan user_id jika ada
            const userIdField = this.form.find('[name="user_id"]');
            if (userIdField.length && userIdField.val()) {
                formData.user_id = userIdField.val();
            }

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: formData
                });

                if (response.success) {
                    EquipmentToast.success('Equipment berhasil ditambahkan');
                    this.hideModal();
                    $(document).trigger('equipment:created', [response.data]);

                    if (window.EquipmentDataTable) {
                        window.EquipmentDataTable.refresh();
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal menambah equipment');
                }
            } catch (error) {
                console.error('Create equipment error:', error);
                EquipmentToast.error('Gagal menghubungi server. Silakan coba lagi.');
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

        showModal() {
            this.resetForm();
            this.modal.fadeIn(300, () => {
                this.form.find('[name="name"]').focus();
            });
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
            });
        },

        resetForm() {
            this.form[0].reset();
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.form.validate().resetForm();
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    code: {
                        required: true,
                        digits: true,
                        minlength: 2,
                        maxlength: 2
                    }
                },
                messages: {
                    name: {
                        required: 'Nama equipment wajib diisi',
                        minlength: 'Nama equipment minimal 3 karakter',
                        maxlength: 'Nama equipment maksimal 100 karakter'
                    },
                    code: {
                        required: 'Kode wajib diisi',
                        digits: 'Kode harus berupa angka',
                        minlength: 'Kode harus 2 digit',
                        maxlength: 'Kode harus 2 digit'
                    }
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CreateEquipmentForm = CreateEquipmentForm;
        CreateEquipmentForm.init();
    });

 })(jQuery);

/**
 * Equipment Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/components/edit-equipment-form.js
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

// Edit Peralatan Form Handler
(function($) {
    'use strict';

    const EditEquipmentForm = {
        modal: null,
        form: null,

        init() {
            this.modal = $('#edit-equipment-modal');
            this.form = $('#edit-equipment-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleUpdate(e));
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });

            // Modal events
            $('.modal-close', this.modal).on('click', () => this.hideModal());
            $('.cancel-edit', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });
        },

        showEditForm(data) {
            if (!data || !data.equipment) {
                EquipmentToast.error('Data equipment tidak valid');
                return;
            }

            // Reset form first
            this.resetForm();

            // Populate form data
            this.form.find('#equipment-id').val(data.equipment.id);
            this.form.find('[name="name"]').val(data.equipment.name);
            this.form.find('[name="code"]').val(data.equipment.code);
            
            // Set user_id if exists
            const userSelect = this.form.find('[name="user_id"]');
            if (userSelect.length && data.equipment.user_id) {
                userSelect.val(data.equipment.user_id);
            }

            // Update modal title with equipment name
            this.modal.find('.modal-header h3').text(`Edit Peralatan: ${data.equipment.name}`);

            // Show modal with animation
            this.modal.fadeIn(300, () => {
                this.form.find('[name="code"]').focus();
            });
            $('#edit-mode').show();
        },

        hideModal() {
            this.modal
                .removeClass('active')
                .fadeOut(300, () => {
                    this.resetForm();
                    $('#edit-mode').hide();
                });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    code: {
                        required: true,
                        minlength: 2,
                        maxlength: 2,
                        digits: true
                    },
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    user_id: {
                        required: $('#edit-user').length > 0
                    }
                },
                messages: {
                    code: {
                        required: 'Kode equipment wajib diisi',
                        minlength: 'Kode harus 2 digit',
                        maxlength: 'Kode harus 2 digit',
                        digits: 'Kode hanya boleh berisi angka'
                    },
                    name: {
                        required: 'Nama equipment wajib diisi',
                        minlength: 'Nama minimal 3 karakter',
                        maxlength: 'Nama maksimal 100 karakter'
                    },
                    user_id: {
                        required: 'User penanggung jawab wajib dipilih'
                    }
                }
            });
        },

        validateField(field) {
            const $field = $(field);
            const value = $field.val().trim();
            const errors = [];

            if (!value) {
                errors.push('Nama equipment wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama equipment minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama equipment maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama equipment hanya boleh mengandung huruf dan spasi');
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

        async handleUpdate(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            const id = this.form.find('#equipment-id').val();
            const requestData = {
                action: 'update_equipment',
                nonce: wpEquipmentData.nonce,
                id: id,
                name: this.form.find('[name="name"]').val().trim(),
                code: this.form.find('[name="code"]').val().trim(),
                user_id: this.form.find('#edit-user').val() // Tambahkan ini

            };

            this.setLoadingState(true);

            console.log('Sending data:', requestData);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    EquipmentToast.success('Equipment berhasil diperbarui');
                    this.hideModal();

                    // Update URL hash to edited equipment's ID
                    if (id) {
                        window.location.hash = id;
                    }

                    // Trigger events for other components
                    $(document).trigger('equipment:updated', [response]);

                    // Refresh DataTable if exists
                    if (window.EquipmentDataTable) {
                        window.EquipmentDataTable.refresh();
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal memperbarui equipment');
                }
            } catch (error) {
                console.error('Update equipment error:', error);
                EquipmentToast.error('Gagal menghubungi server');
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
        window.EditEquipmentForm = EditEquipmentForm;
        EditEquipmentForm.init();
    });

})(jQuery);

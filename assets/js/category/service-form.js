/**
 * Service Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Category
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/service-form.js
 *
 * Description: Handler untuk form modal service.
 *              Menangani create dan edit dalam satu form.
 *              Includes form validation, AJAX submission,
 *              error handling, dan modal management.
 *              Terintegrasi dengan toast notifications.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - EquipmentToast for notifications
 * - WIModal for confirmations
 *
 * Last modified: 2024-02-21
 */
(function($) {
    'use strict';

    const ServiceForm = {
        modal: null,
        form: null,
        categoryId: null,
        isEdit: false,

        init() {
            this.modal = $('#service-modal');
            this.form = $('#service-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleSubmit(e));

            // Input validation events
            this.form.on('input', 'input[name="nama"], input[name="singkatan"]', (e) => {
                this.validateField(e.target);
            });

            // Add button handler
            $('#add-service-btn').on('click', () => {
                const categoryId = window.Category?.currentId;
                if (categoryId) {
                    this.showModal(categoryId);
                } else {
                    EquipmentToast.error('Silakan pilih kategori terlebih dahulu');
                }
            });

            // Edit button handler
            $(document).on('click', '.edit-service', (e) => {
                const id = $(e.currentTarget).data('id');
                if (id) {
                    this.loadServiceData(id);
                }
            });

            // Modal events
            $('.modal-close, .cancel-button', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });
        },

        showModal(categoryId, isEdit = false) {
            this.isEdit = isEdit;
            this.categoryId = categoryId;
            
            // Reset and prepare form
            this.resetForm();
            
            // Set category ID
            this.form.find('#service-category-id').val(categoryId);
            
            // Update modal title and button text
            const title = isEdit ? 'Edit Layanan' : 'Tambah Layanan';
            const buttonText = isEdit ? 'Perbarui' : 'Simpan';
            
            $('#service-modal-title').text(title);
            this.form.find('.submit-button').text(buttonText);
            
            // Show/hide status field based on mode
            $('.edit-only')[isEdit ? 'show' : 'hide']();
            
            // Show modal with animation
            this.modal.fadeIn(300, () => {
                this.form.find('[name="nama"]').focus();
            });
        },

        async loadServiceData(id) {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_service',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });
        
                if (response.success && response.data.service) {
                    const service = response.data.service;
                    
                    // Tampilkan modal dengan mode edit
                    this.showModal(service.id, true);
                    
                    // Populate form fields yang sesuai dengan struktur form
                    this.form.find('#service-id').val(service.id);
                    this.form.find('#service-name').val(service.nama);
                    this.form.find('#service-singkatan').val(service.singkatan);
                    this.form.find('#service-keterangan').val(service.keterangan || '');
                    this.form.find('#service-status').val(service.status);
        
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal memuat data layanan');
                }
            } catch (error) {
                console.error('Load service error:', error);
                EquipmentToast.error('Gagal menghubungi server');
            }
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
                this.isEdit = false;
                this.categoryId = null;
            });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    nama: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    singkatan: {
                        required: true,
                        maxlength: 5
                    },
                    keterangan: {
                        maxlength: 255
                    }
                },
                messages: {
                    nama: {
                        required: 'Nama layanan wajib diisi',
                        minlength: 'Nama layanan minimal 3 karakter',
                        maxlength: 'Nama layanan maksimal 100 karakter'
                    },
                    singkatan: {
                        required: 'Singkatan wajib diisi',
                        maxlength: 'Singkatan maksimal 5 karakter'
                    },
                    keterangan: {
                        maxlength: 'Keterangan maksimal 255 karakter'
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
            const fieldName = $field.attr('name');
            const value = $field.val().trim();
            const errors = [];

            switch (fieldName) {
                case 'nama':
                    if (!value) {
                        errors.push('Nama layanan wajib diisi');
                    } else {
                        if (value.length < 3) {
                            errors.push('Nama layanan minimal 3 karakter');
                        }
                        if (value.length > 100) {
                            errors.push('Nama layanan maksimal 100 karakter');
                        }
                    }
                    break;

                case 'singkatan':
                    if (!value) {
                        errors.push('Singkatan wajib diisi');
                    } else if (value.length > 5) {
                        errors.push('Singkatan maksimal 5 karakter');
                    }
                    break;
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

        async handleSubmit(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            const formData = {
                action: this.isEdit ? 'update_service' : 'create_service',
                nonce: wpEquipmentData.nonce,
                nama: this.form.find('[name="nama"]').val().trim(),
                singkatan: this.form.find('[name="singkatan"]').val().trim(),
                keterangan: this.form.find('[name="keterangan"]').val().trim()
            };

            // Add ID if editing
            if (this.isEdit) {
                formData.id = this.form.find('#service-id').val();
                formData.status = this.form.find('[name="status"]').val();
            }

            // Add category ID
            formData.category_id = this.categoryId;

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: formData
                });

                if (response.success) {
                    EquipmentToast.success(this.isEdit ? 
                        'Layanan berhasil diperbarui' : 
                        'Layanan berhasil ditambahkan'
                    );
                    
                    this.hideModal();
                    $(document).trigger(
                        this.isEdit ? 'service:updated' : 'service:created', 
                        [response.data]
                    );

                    if (window.ServiceDataTable) {
                        window.ServiceDataTable.refresh();
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 
                        (this.isEdit ? 'Gagal memperbarui layanan' : 'Gagal menambah layanan')
                    );
                }
            } catch (error) {
                console.error('Submit service error:', error);
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
            this.form.find('#service-id').val('');
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.form.validate().resetForm();
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.ServiceForm = ServiceForm;
        ServiceForm.init();
    });

})(jQuery);

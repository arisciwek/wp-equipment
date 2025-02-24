/**
 * Group Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Category
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/group-form.js
 *
 * Description: Handler untuk form modal group.
 *              Menangani create dan edit dalam satu form.
 *              Includes form validation, file upload,
 *              error handling, dan modal management.
 *              Terintegrasi dengan service selection dan
 *              toast notifications.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - EquipmentToast for notifications
 *
 * Changelog:
 * 1.0.0 - 2024-02-24
 * - Initial implementation
 * - Added file upload handling
 * - Added service selection
 * - Added validation rules
 */
(function($) {
    'use strict';

    const GroupForm = {
        modal: null,
        form: null,
        serviceId: null,
        isEdit: false,

        init() {
            this.modal = $('#group-modal');
            this.form = $('#group-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleSubmit(e));

            // Input validation events
            this.form.on('input', 'input[name="nama"]', (e) => {
                this.validateField(e.target);
            });

            // File input change
            this.form.on('change', '#group-dokumen', (e) => {
                this.validateFile(e.target);
            });

            // Service selection change
            this.form.on('change', '#group-service', (e) => {
                this.serviceId = $(e.target).val();
            });

            // Add button handler
            $('#add-group-btn').on('click', () => {
                const serviceId = $(this).data('service');
                if (serviceId) {
                    this.showModal(serviceId);
                } else {
                    EquipmentToast.error('Service ID tidak valid');
                }
            });

            // Edit button handler
            $(document).on('click', '.edit-group', (e) => {
                const id = $(e.currentTarget).data('id');
                if (id) {
                    this.loadGroupData(id);
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

        async loadServices() {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_services',
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success && response.data.services) {
                    const $select = $('#group-service');
                    $select.empty().append(
                        '<option value="">' + 
                        __('Pilih Service', 'wp-equipment') + 
                        '</option>'
                    );

                    response.data.services.forEach(service => {
                        $select.append(
                            `<option value="${service.id}">${service.nama}</option>`
                        );
                    });
                }
            } catch (error) {
                console.error('Load services error:', error);
            }
        },

        showModal(serviceId = null, isEdit = false) {
            this.isEdit = isEdit;
            this.serviceId = serviceId;
            
            // Reset and prepare form
            this.resetForm();
            
            // Load services if not editing
            if (!isEdit) {
                this.loadServices();
            }
            
            // Set service ID if provided
            if (serviceId) {
                this.form.find('#group-service-id').val(serviceId);
                this.form.find('#group-service').val(serviceId);
            }
            
            // Update modal title and button text
            const title = isEdit ? 'Edit Group' : 'Tambah Group';
            const buttonText = isEdit ? 'Perbarui' : 'Simpan';
            
            $('#group-modal-title').text(title);
            this.form.find('.submit-button').text(buttonText);
            
            // Show/hide fields based on mode
            $('.edit-only')[isEdit ? 'show' : 'hide']();
            
            // Show modal with animation
            this.modal.fadeIn(300, () => {
                this.form.find('[name="nama"]').focus();
            });
        },

        async loadGroupData(id) {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_group',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });
        
                if (response.success && response.data.group) {
                    const group = response.data.group;
                    
                    // Tampilkan modal dengan mode edit
                    this.showModal(group.service_id, true);
                    
                    // Populate form fields
                    this.form.find('#group-id').val(group.id);
                    this.form.find('#group-name').val(group.nama);
                    this.form.find('#group-keterangan').val(group.keterangan || '');
                    this.form.find('#group-status').val(group.status);
                    
                    // Handle document info
                    if (group.dokumen_name) {
                        const docInfo = `
                            <a href="${group.dokumen_path}" target="_blank">
                                ${group.dokumen_name}
                            </a> 
                            (${group.dokumen_type.toUpperCase()})
                        `;
                        $('#document-info').html(docInfo);
                        $('#current-document').show();
                    }
        
                } else {
                    EquipmentToast.error(response.data?.message || 'Gagal memuat data group');
                }
            } catch (error) {
                console.error('Load group error:', error);
                EquipmentToast.error('Gagal menghubungi server');
            }
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
                this.isEdit = false;
                this.serviceId = null;
            });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    service_id: {
                        required: true
                    },
                    nama: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    keterangan: {
                        maxlength: 255
                    },
                    dokumen: {
                        extension: "docx|odt",
                        filesize: 5242880 // 5MB
                    }
                },
                messages: {
                    service_id: {
                        required: 'Service wajib dipilih'
                    },
                    nama: {
                        required: 'Nama group wajib diisi',
                        minlength: 'Nama group minimal 3 karakter',
                        maxlength: 'Nama group maksimal 100 karakter'
                    },
                    keterangan: {
                        maxlength: 'Keterangan maksimal 255 karakter'
                    },
                    dokumen: {
                        extension: 'Format file harus DOCX atau ODT',
                        filesize: 'Ukuran file maksimal 5MB'
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

            // Add custom validation method for file size
            $.validator.addMethod('filesize', function(value, element, param) {
                if (!element.files || !element.files[0]) return true;
                return element.files[0].size <= param;
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
                        errors.push('Nama group wajib diisi');
                    } else {
                        if (value.length < 3) {
                            errors.push('Nama group minimal 3 karakter');
                        }
                        if (value.length > 100) {
                            errors.push('Nama group maksimal 100 karakter');
                        }
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

        validateFile(input) {
            const $input = $(input);
            const $error = $input.next('.form-error');
            const file = input.files[0];
            const errors = [];

            if (file) {
                // Check file type
                const allowedTypes = ['docx', 'odt'];
                const extension = file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(extension)) {
                    errors.push('Format file harus DOCX atau ODT');
                }

                // Check file size (5MB)
                if (file.size > 5242880) {
                    errors.push('Ukuran file maksimal 5MB');
                }
            }

            if (errors.length > 0) {
                $input.addClass('error');
                if ($error.length) {
                    $error.text(errors[0]);
                } else {
                    $('<span class="form-error"></span>')
                        .text(errors[0])
                        .insertAfter($input);
                }
                input.value = ''; // Clear input
                return false;
            } else {
                $input.removeClass('error');
                $error.remove();
                return true;
            }
        },

        async handleSubmit(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            const formData = new FormData(this.form[0]);
            formData.append('action', this.isEdit ? 'update_group' : 'create_group');
            formData.append('nonce', wpEquipmentData.nonce);

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });

                if (response.success) {
                    EquipmentToast.success(this.isEdit ? 
                        'Group berhasil diperbarui' : 
                        'Group berhasil ditambahkan'
                    );
                    
                    this.hideModal();
                    $(document).trigger(
                        this.isEdit ? 'group:updated' : 'group:created', 
                        [response.data]
                    );

                    if (window.GroupDataTable) {
                        window.GroupDataTable.refresh();
                    }
                } else {
                    EquipmentToast.error(response.data?.message || 
                        (this.isEdit ? 'Gagal memperbarui group' : 'Gagal menambah group')
                    );
                }
            } catch (error) {
                console.error('Submit group error:', error);
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
            this.form.find('#group-id').val('');
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.form.validate().resetForm();
            $('#document-info').empty();
            $('#current-document').hide();
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.GroupForm = GroupForm;
        GroupForm.init();
    });

})(jQuery);

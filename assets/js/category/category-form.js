/**
 * Category Form Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/category-form.js
 *
 * Description: Handler untuk form kategori.
 *              Menangani create dan update kategori.
 *              Includes validasi form, error handling,
 *              dan integrasi dengan komponen lain.
 *              Handles modal management dan AJAX calls.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - CategoryToast for notifications
 * - Category main component
 * - WordPress AJAX API
 *
 * Related Files:
 * - create-category-form.php: Create form template
 * - edit-category-form.php: Edit form template
 * - category-script.js: Main category handler
 * - category-datatable.js: DataTable handler
 *
 * Changelog:
 * 1.0.0 - 2024-02-12
 * - Initial implementation
 * - Added proper form validation
 * - Added AJAX integration
 * - Added modal management
 * - Added loading states
 * - Added error handling
 * - Added panel integration
 *
 * Last modified: 2024-02-12 17:00:00
 */

(function($) {
    'use strict';

    const CategoryForm = {
        createModal: null,
        editModal: null,
        createForm: null,
        editForm: null,
        initialized: false,

        init() {
            if (this.initialized) {
                return;
            }

            // Initialize form elements
            this.createModal = $('#create-category-modal');
            this.editModal = $('#edit-category-modal');
            this.createForm = $('#create-category-form');
            this.editForm = $('#edit-category-form');

            this.bindEvents();
            this.initializeValidation();
            this.initialized = true;
        },

        bindEvents() {
            // Create form events
            $('#add-category-btn').on('click', () => this.showCreateForm());
            this.createForm.on('submit', (e) => this.handleCreate(e));
            
            // Edit form events
            this.editForm.on('submit', (e) => this.handleUpdate(e));

            // Level change handlers
            $('#category-level, #edit-category-level').on('change', (e) => this.handleLevelChange(e));

            // Modal close handlers
            $('.modal-close, .cancel-button', this.createModal).on('click', () => this.hideCreateForm());
            $('.modal-close, .cancel-button', this.editModal).on('click', () => this.hideEditForm());

            // Parent category handlers
            $('#category-parent, #edit-category-parent').on('change', (e) => this.handleParentChange(e));

            // Close modal when clicking overlay
            $('.modal-overlay').on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModals();
                }
            });
        },

        initializeValidation() {
            // Extend jQuery validation with custom methods
            $.validator.addMethod("validCode", function(value, element) {
                return /^[A-Za-z0-9-_]+$/.test(value);
            }, "Kode hanya boleh berisi huruf, angka, dash dan underscore");

            // Configure validation for create form
            this.createForm.validate({
                rules: {
                    code: {
                        required: true,
                        minlength: 3,
                        maxlength: 20,
                        validCode: true
                    },
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    level: {
                        required: true
                    }
                },
                messages: {
                    code: {
                        required: "Kode kategori wajib diisi",
                        minlength: "Kode minimal 3 karakter",
                        maxlength: "Kode maksimal 20 karakter"
                    },
                    name: {
                        required: "Nama kategori wajib diisi",
                        minlength: "Nama minimal 3 karakter",
                        maxlength: "Nama maksimal 100 karakter"
                    },
                    level: {
                        required: "Level kategori wajib dipilih"
                    }
                },
                errorElement: "span",
                errorClass: "form-error",
                validClass: "form-valid"
            });

            // Configure validation for edit form
            this.editForm.validate({
                rules: {
                    code: {
                        required: true,
                        minlength: 3,
                        maxlength: 20,
                        validCode: true
                    },
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    level: {
                        required: true
                    }
                },
                messages: {
                    code: {
                        required: "Kode kategori wajib diisi",
                        minlength: "Kode minimal 3 karakter",
                        maxlength: "Kode maksimal 20 karakter"
                    },
                    name: {
                        required: "Nama kategori wajib diisi",
                        minlength: "Nama minimal 3 karakter",
                        maxlength: "Nama maksimal 100 karakter"
                    },
                    level: {
                        required: "Level kategori wajib dipilih"
                    }
                },
                errorElement: "span",
                errorClass: "form-error",
                validClass: "form-valid"
            });
        },

        handleLevelChange(e) {
            const $select = $(e.target);
            const $form = $select.closest('form');
            const $parentSelect = $form.find('[name="parent_id"]');
            const level = parseInt($select.val());

            if (!level) {
                $parentSelect.prop('disabled', true).html('<option value="">Pilih Kategori Induk</option>');
                return;
            }

            // Enable parent select for non-level-1 categories
            if (level > 1) {
                this.loadParentOptions(level, $parentSelect);
                $parentSelect.prop('disabled', false);
            } else {
                $parentSelect.prop('disabled', true).val('');
            }

            // Show warning if editing and level changed
            if ($form.is('#edit-category-form')) {
                const originalLevel = $form.data('original-level');
                if (originalLevel && originalLevel !== level) {
                    $('.level-warning', $form).show();
                } else {
                    $('.level-warning', $form).hide();
                }
            }
        },

        async loadParentOptions(childLevel, $select) {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category_parents',
                        child_level: childLevel,
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success) {
                    let options = '<option value="">Pilih Kategori Induk</option>';
                    response.data.forEach(category => {
                        options += `<option value="${category.id}">${category.code} - ${category.name}</option>`;
                    });
                    $select.html(options);
                } else {
                    EquipmentToast.error(response.data.message);
                }
            } catch (error) {
                console.error('Error loading parent options:', error);
                EquipmentToast.error('Gagal memuat opsi kategori induk');
            }
        },

        async handleCreate(e) {
            e.preventDefault();

            if (!this.createForm.valid()) {
                return;
            }

            this.setLoadingState(this.createForm, true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'create_category',
                        nonce: wpEquipmentData.nonce,
                        ...this.getFormData(this.createForm)
                    }
                });

                if (response.success) {
                    EquipmentToast.success('Kategori berhasil ditambahkan');
                    this.hideCreateForm();
                    $(document).trigger('category:created', [response.data]);
                } else {
                    EquipmentToast.error(response.data.message);
                }
            } catch (error) {
                console.error('Create category error:', error);
                EquipmentToast.error('Gagal membuat kategori');
            } finally {
                this.setLoadingState(this.createForm, false);
            }
        },

        async handleUpdate(e) {
            e.preventDefault();

            if (!this.editForm.valid()) {
                return;
            }

            this.setLoadingState(this.editForm, true);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'update_category',
                        nonce: wpEquipmentData.nonce,
                        ...this.getFormData(this.editForm)
                    }
                });

                if (response.success) {
                    EquipmentToast.success('Kategori berhasil diperbarui');
                    this.hideEditForm();
                    $(document).trigger('category:updated', [response.data]);
                } else {
                    EquipmentToast.error(response.data.message);
                }
            } catch (error) {
                console.error('Update category error:', error);
                EquipmentToast.error('Gagal memperbarui kategori');
            } finally {
                this.setLoadingState(this.editForm, false);
            }
        },

        getFormData($form) {
            const formData = {};
            $form.serializeArray().forEach(item => {
                formData[item.name] = item.value;
            });
            return formData;
        },

        populateEditForm(data) {
            const category = data.category;
            if (!category) return;

            // Reset form first
            this.resetForm(this.editForm);

            // Set form data
            Object.keys(category).forEach(key => {
                const $field = this.editForm.find(`[name="${key}"]`);
                if ($field.length) {
                    $field.val(category[key]);
                }
            });

            // Store original level for change detection
            this.editForm.data('original-level', category.level);

            // Handle parent category loading
            if (category.level > 1) {
                this.loadParentOptions(category.level, $('#edit-category-parent'));
            }

            // Show parent warning if has children
            $('.parent-warning').toggle(!!category.has_children);

            // Update status information
            $('#edit-category-created-by').text(category.created_by_name || '-');
            $('#edit-category-created-at').text(category.created_at || '-');
            $('#edit-category-updated-at').text(category.updated_at || '-');

            // Show modal
            this.showEditForm();
        },

        showCreateForm() {
            this.resetForm(this.createForm);
            this.createModal.fadeIn(300);
        },

        showEditForm() {
            this.editModal.fadeIn(300);
        },

        hideCreateForm() {
            this.createModal.fadeOut(300, () => this.resetForm(this.createForm));
        },

        hideEditForm() {
            this.editModal.fadeOut(300, () => this.resetForm(this.editForm));
        },

        hideModals() {
            this.hideCreateForm();
            this.hideEditForm();
        },

        resetForm($form) {
            $form[0].reset();
            $form.find('.form-error').remove();
            $form.find('.error').removeClass('error');
            $form.validate().resetForm();

            // Reset parent select
            const $parentSelect = $form.find('[name="parent_id"]');
            $parentSelect
                .html('<option value="">Pilih Kategori Induk</option>')
                .prop('disabled', true);

            // Hide warnings
            $form.find('.level-warning, .parent-warning').hide();
        },

        setLoadingState($form, loading) {
            const $submitBtn = $form.find('[type="submit"]');
            const $spinner = $form.find('.spinner');

            if (loading) {
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                $form.addClass('loading');
            } else {
                $submitBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
                $form.removeClass('loading');
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CategoryForm = CategoryForm;
        CategoryForm.init();
    });

})(jQuery);

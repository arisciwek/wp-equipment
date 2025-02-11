/**
 * Category Management Interface
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/category-script.js
 */

(function($) {
    'use strict';

    const Category = {
        currentId: null,
        isLoading: false,
        components: {
            container: null,
            rightPanel: null,
            detailsPanel: null,
            stats: {
                totalCategories: null
            }
        },

        init() {
            this.components = {
                container: $('.wp-equipment-container'),
                rightPanel: $('.wp-equipment-right-panel'),
                detailsPanel: $('#category-details'),
                stats: {
                    totalCategories: $('#total-categories')
                }
            };

            this.bindEvents();
            this.handleInitialState();
            this.loadStats();
        },

        bindEvents() {
            $(document)
                .off('.Category')
                .on('category:created.Category', (e, data) => this.handleCreated(data))
                .on('category:updated.Category', (e, data) => this.handleUpdated(data))
                .on('category:deleted.Category', () => this.handleDeleted())
                .on('category:display.Category', (e, data) => this.displayData(data))
                .on('category:loading.Category', () => this.showLoading())
                .on('category:loaded.Category', () => this.hideLoading());

            $('.wp-equipment-close-panel').off('click').on('click', () => this.closePanel());

            $('.nav-tab').off('click').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });

            $(window).off('hashchange.Category').on('hashchange.Category', () => this.handleHashChange());
        },

        handleInitialState() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const id = hash.substring(1);
                if (id && id !== this.currentId) {
                    this.loadCategoryData(id);
                }
            }
        },

        handleHashChange() {
            const hash = window.location.hash;
            if (!hash) {
                this.closePanel();
                return;
            }

            const id = hash.substring(1);
            if (id && id !== this.currentId) {
                this.loadCategoryData(id);
            }
        },


        // Method untuk memuat data kategori
        async loadCategoryData(id) {
            if (!id || this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            try {
                console.log('Loading category data for ID:', id);

                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });

                console.log('Category data response:', response);

                if (response.success && response.data) {
                    // Update URL hash tanpa trigger reload
                    const newHash = `#${id}`;
                    if (window.location.hash !== newHash) {
                        window.history.pushState(null, '', newHash);
                    }

                    // Reset tab ke default
                    $('.nav-tab').removeClass('nav-tab-active');
                    $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
                    
                    // Sembunyikan semua tab content
                    $('.tab-content').removeClass('active').hide();
                    // Tampilkan tab details
                    $('#category-details').addClass('active').show();

                    // Update data di UI
                    this.displayData(response.data);
                    this.currentId = id;

                    // Trigger success event
                    $(document).trigger('category:loaded', [response.data]);
                } else {
                    throw new Error(response.data?.message || 'Failed to load category data');
                }
            } catch (error) {
                console.error('Error loading category:', error);
                CategoryToast.error(error.message || 'Failed to load category data');
                this.handleLoadError();
            } finally {
                this.isLoading = false;
                this.hideLoading();
            }
        },

        // Method untuk menampilkan data
        displayData(data) {
            if (!data?.category) {
                console.error('Invalid category data:', data);
                return;
            }

            const category = data.category;

            // Tampilkan panel kanan
            this.components.container.addClass('with-right-panel');
            this.components.rightPanel.addClass('visible');

            try {
                // Basic Information
                $('#category-header-name').text(category.name);
                $('#category-code').text(category.code || '-');
                $('#category-name').text(category.name || '-');
                $('#category-description').text(category.description || '-');

                // Status Badge
                const statusBadge = $('#category-status');
                statusBadge
                    .text(category.status === 'active' ? 'Active' : 'Inactive')
                    .removeClass('status-active status-inactive')
                    .addClass(`status-${category.status}`);

                // Category Information
                $('#category-level').text(this.getLevelLabel(category.level));
                $('#category-parent').text(category.parent_name || '-');
                $('#category-sort-order').text(category.sort_order || '0');

                // Product Details
                $('#category-unit').text(category.unit || '-');
                $('#category-price').text(category.formatted_price || '-');

                // Timeline Information
                $('#category-created-by').text(category.created_by_name || '-');
                $('#category-created-at').text(category.created_at || '-');
                $('#category-updated-at').text(category.updated_at || '-');

                // Set permissions based buttons visibility
                $('.edit-category, .delete-category').toggle(data.meta.can_edit);

                // Highlight DataTable row if exists
                if (window.CategoryDataTable) {
                    window.CategoryDataTable.highlightRow(category.id);
                }

                // Trigger success event
                $(document).trigger('category:displayed', [data]);

            } catch (error) {
                console.error('Error displaying category data:', error);
                CategoryToast.error('Error displaying category data');
            }
        },

       getLevelLabel(level) {
           const labels = {
               1: 'Level 1 - Main Category',
               2: 'Level 2 - Sub Category',
               3: 'Level 3 - Service Type'
           };
           return labels[level] || `Level ${level}`;
       },

       renderCategoryTree(children) {
           const $treeView = $('#category-tree-view');
           $treeView.empty();

           const buildTree = (items) => {
               const $ul = $('<ul>');
               items.forEach(item => {
                   const $li = $('<li>')
                       .addClass(`level-${item.level}`)
                       .text(`${item.code} - ${item.name}`);
                   
                   if (item.children && item.children.length) {
                       $li.append(buildTree(item.children));
                   }
                   $ul.append($li);
               });
               return $ul;
           };

           $treeView.append(buildTree(children));
       },


        // Method untuk menangani tab
        switchTab(tabId) {
            console.log('Tab switched to:', tabId);
            $('.nav-tab').removeClass('nav-tab-active');
            $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

            $('.tab-content').removeClass('active').hide();
            $(`#${tabId}`).addClass('active').show();

            // Handle specific tab content loading if needed
            if (tabId === 'category-hierarchy' && this.currentId) {
                this.loadHierarchyData(this.currentId);
            }
        },

       closePanel() {
           this.components.container.removeClass('with-right-panel');
           this.components.rightPanel.removeClass('visible');
           this.currentId = null;
           window.location.hash = '';
           $(document).trigger('panel:closed');
       },

       showLoading() {
           this.components.rightPanel.addClass('loading');
       },

       hideLoading() {
           this.components.rightPanel.removeClass('loading');
       },

       handleCreated(data) {
           if (data && data.id) {
               window.location.hash = data.id;
           }
           if (window.CategoryDataTable) {
               window.CategoryDataTable.refresh();
           }
           this.loadStats();
       },

       handleUpdated(data) {
           if (data && data.data && data.data.category) {
               if (this.currentId === data.data.category.id) {
                   this.displayData(data.data);
               }
           }
           if (window.CategoryDataTable) {
               window.CategoryDataTable.refresh();
           }
       },

       handleDeleted() {
           this.closePanel();
           if (window.CategoryDataTable) {
               window.CategoryDataTable.refresh();
           }
           this.loadStats();
       },

       loadStats() {
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'get_category_stats',
                   nonce: wpEquipmentData.nonce
               },
               success: (response) => {
                   if (response.success) {
                       this.updateStats(response.data.stats);
                   }
               }
           });
       },

       updateStats(stats) {
           $('#total-categories').text(stats.total_categories);
       }
   };

    const CategoryModal = {
        modal: null,
        form: null,

        init() {
            // Initialize modal elements
            this.modal = $('.modal-overlay');
            this.form = this.modal.find('form');
            
            // Bind close events
            this.bindCloseEvents();
            
            // Bind form submit
            this.bindFormSubmit();

            // Bind add button click
            $('#add-category-btn').on('click', () => this.showModal());
        },

        showModal() {
            this.resetForm();
            this.modal.show();
            $('body').addClass('modal-open');
        },

        hideModal() {
            this.modal.hide();
            this.resetForm();
            $('body').removeClass('modal-open');
        },

        resetForm() {
            this.form[0].reset();
            this.form.find('.error-message').remove();
            this.form.find('.has-error').removeClass('has-error');
        },

        bindCloseEvents() {
            // Close on X button
            this.modal.find('.modal-close').on('click', (e) => {
                e.preventDefault();
                this.hideModal();
            });

            // Close on cancel button
            this.modal.find('.cancel-button').on('click', (e) => {
                e.preventDefault();
                this.hideModal();
            });

            // Close on overlay click
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });

            // Close on escape key
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.modal.is(':visible')) {
                    this.hideModal();
                }
            });
        },

        bindFormSubmit() {
            this.form.on('submit', (e) => {
                e.preventDefault();
                // Add your form submission logic here
            });
        }
    };

   $(document).ready(() => {
       window.Category = Category;
       Category.init();
   });

})(jQuery);

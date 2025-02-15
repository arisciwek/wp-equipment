/**
 * Category Management Interface - Core & Panel Management
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/category/category-script.js
 * 
 * Description: Main JavaScript handler untuk halaman kategori.
 *              Bagian 1: Core initialization dan panel management.
 *              Mengatur interaksi antar komponen seperti panel,
 *              tab navigation, dan state management dasar.
 *
 * Dependencies:
 * - jQuery
 * - CategoryDataTable
 * - CategoryForm
 * - EquipmentToast
 * - WordPress AJAX
 *
 * Related Files:
 * - category-datatable.js: Handler untuk DataTable
 * - category-form.js: Handler untuk form operations
 * - CategoryController.php: Backend handler
 *
 * Changelog:
 * 1.0.0 - 2024-02-12
 * - Initial implementation
 * - Added panel management
 * - Added tab navigation
 * - Added state handling
 *
 * Last modified: 2024-02-12 18:00:00
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
                totalCategorys: null,
                totalBranches: null
            }
        },

       init() {
           this.components = {
               container: $('.wp-equipment-container'),
               rightPanel: $('.wp-equipment-right-panel'),
               detailsPanel: $('#category-details'),
               stats: {
                   totalCategorys: $('#total-categories')
               }
           };

           // Tambahkan load tombol tambah category
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'create_category_button',
                   nonce: wpEquipmentData.nonce
               },
               success: (response) => {
                   if (response.success) {
                       $('#tombol-tambah-category').html(response.data.button);
                       
                       // Bind click event using delegation
                       $('#tombol-tambah-category').off('click', '#add-category-btn')
                           .on('click', '#add-category-btn', () => {
                               if (window.CreateCategoryForm) {
                                   window.CreateCategoryForm.showModal();
                               }
                           });
                   }
               }
           });

           this.bindEvents();
           this.handleInitialState();
           this.loadStats();
           
           // Update stats setelah operasi CRUD
           $(document)
               .on('category:created.Category', () => this.loadStats())
               .on('category:deleted.Category', () => this.loadStats())
               .on('branch:created.Category', () => this.loadStats())
               .on('branch:deleted.Category', () => this.loadStats())
               .on('employee:created.Category', () => this.loadStats())
               .on('employee:deleted.Category', () => this.loadStats());
       },

        bindEvents() {
            // Unbind existing events first to prevent duplicates
            $(document)
                .off('.Category')
                .on('category:created.Category', (e, data) => this.handleCreated(data))
                .on('category:updated.Category', (e, data) => this.handleUpdated(data))
                .on('category:deleted.Category', () => this.handleDeleted())
                .on('category:display.Category', (e, data) => this.displayData(data))
                .on('category:loading.Category', () => this.showLoading())
                .on('category:loaded.Category', () => this.hideLoading());

            // Panel events
            $('.wp-equipment-close-panel').off('click').on('click', () => this.closePanel());

            // Panel navigation
            $('.nav-tab').off('click').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });

            // Window events
            $(window).off('hashchange.Category').on('hashchange.Category', () => this.handleHashChange());
        },

        validateCategoryAccess(categoryId, onSuccess, onError) {
            $.ajax({
                url: wpEquipmentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'validate_category_access',
                    id: categoryId,
                    nonce: wpEquipmentData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        if (onSuccess) onSuccess(response.data);
                    } else {
                        if (onError) onError(response.data || { message: 'Access validation failed' });
                    }
                },
                error: (xhr) => {
                    console.error('Validation error:', xhr);
                    if (onError) onError({
                        message: 'Terjadi kesalahan saat validasi akses',
                        code: 'server_error'
                    });
                }
            });
        },
           
        handleInitialState() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                this.handleHashChange();
            }
        },

        handleHashChange() {
            const hash = window.location.hash;
            if (hash) {
                const id = hash.substring(1);
                if (id) {
                    this.loadCategoryData(id);
                }
            }
        },

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
                    // Update URL hash tanpa reload
                    const newHash = `#${id}`;
                    if (window.location.hash !== newHash) {
                        window.history.pushState(null, '', newHash);
                    }
        
                    // Reset tab ke default (Details)
                    $('.nav-tab').removeClass('nav-tab-active');
                    $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
                    
                    // Sembunyikan semua konten tab
                    $('.tab-content').removeClass('active').hide();
                    // Tampilkan tab details
                    $('#category-details').addClass('active').show();
        
                    // Update UI dengan data kategori
                    this.displayData(response.data);
                    this.currentId = id;
        
                    // Trigger event success
                    $(document).trigger('category:loaded', [response.data]);
                } else {
                    throw new Error(response.data?.message || 'Failed to load category data');
                }
            } catch (error) {
                console.error('Error loading category:', error);
                EquipmentToast.error(error.message || 'Failed to load category data');
                this.handleLoadError();
            } finally {
                this.isLoading = false;
                this.hideLoading();
            }
        },

       displayData(data) {
        if (!data?.category) {
            console.error('Invalid category data:', data);
            return;
        }
    
        console.log('Displaying category data:', data);
    
        try {
            // PENTING: Tampilkan panel terlebih dahulu
            this.components.container.addClass('with-right-panel');
            this.components.rightPanel.addClass('visible');
    
            // Basic Information
            $('#category-header-name').text(data.category.name);
            $('#category-code').text(data.category.code || '-');
            $('#category-name').text(data.category.name || '-');
            $('#category-description').text(data.category.description || '-');
    
            // Level information
            $('#category-level').text(this.getLevelLabel(data.category.level));
            $('#category-parent').text(data.category.parent_name || '-');
            $('#category-sort-order').text(data.category.sort_order || '0');
    
            // Product Information
            $('#category-unit').text(data.category.unit || '-');
            $('#category-pnbp').text(data.category.pnbp ? 
                new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(data.category.pnbp) : '-'
            );
    
            // Timeline Information
            $('#category-created-by').text(data.category.created_by_name || '-');
            $('#category-created-at').text(data.category.created_at || '-');
            $('#category-updated-at').text(data.category.updated_at || '-');
    
            // Highlight baris di DataTable jika ada
            if (window.CategoryDataTable) {
                window.CategoryDataTable.highlightRow(data.category.id);
            }
    
            // Trigger event displayed
            $(document).trigger('category:displayed', [data]);
    
        } catch (error) {
            console.error('Error displaying category data:', error);
            EquipmentToast.error('Error menampilkan data kategori');
        }
    }, 

   handleLoadError() {
       this.components.detailsPanel.html(
           '<div class="error-message">' +
           '<p>Failed to load category data. Please try again.</p>' +
           '<button class="button retry-load">Retry</button>' +
           '</div>'
       );
   },
  
     getLevelLabel(level) {
        const labels = {
            1: 'Level 1 - Kategori Utama',
            2: 'Level 2 - Sub Kategori',
            3: 'Level 3 - Tipe Layanan'
        };
        return labels[level] || `Level ${level}`;
    },
             // Helper function untuk label capability
           getCapabilityLabel(cap) {
               const labels = {
                   'can_add_staff': 'Dapat menambah staff',
                   'can_export': 'Dapat export data',
                   'can_bulk_import': 'Dapat bulk import'
               };
               return labels[cap] || cap;
           },

           // Helper function untuk logika tampilan tombol upgrade
           shouldShowUpgradeOption(currentLevel, targetLevel) {
               const levels = ['regular', 'priority', 'utama'];
               const currentIdx = levels.indexOf(currentLevel);
               const targetIdx = levels.indexOf(targetLevel);
               return targetIdx > currentIdx;
           },

           switchTab(tabId) {
            // Hapus tab yang aktif sebelumnya
            $('.nav-tab').removeClass('nav-tab-active');
            $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');
        
            // Sembunyikan semua konten tab
            $('.tab-content').hide();
            
            // Tampilkan konten tab yang dipilih
            $(`#${tabId}`).show();
        
            // Handle konten tab spesifik
            switch(tabId) {
                case 'category-details':
                    // Tab details sudah memiliki konten statis dari template
                    break;
                    
                case 'category-hierarchy':
                    // Tampilkan pesan "sedang dikembangkan"
                    $('#category-hierarchy').html(`
                        <div class="notice notice-info">
                            <p>${wpEquipmentData.texts.development_notice || 'Fitur ini sedang dalam pengembangan'}</p>
                        </div>
                    `);
                    break;
                    
                default:
                    console.warn('Tab tidak dikenal:', tabId);
                    break;
            }
        
            // Trigger event ketika tab berubah
            $(document).trigger('category:tabChanged', [tabId]);
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
        if (data && data.data && data.data.id) {
            // Update hash
            window.location.hash = data.data.id;
            
            // Reset dan aktifkan tab details
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
            $('.tab-content').removeClass('active').hide();
            $('#category-details').addClass('active').show();
            
            // Buka panel kanan
            $('.wp-category-container').addClass('with-right-panel');
            $('.wp-category-right-panel').addClass('visible');
            
            // Refresh DataTable
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }
        }
    },

    handleUpdated(response) {
        if (response && response.data && response.data.category) {
            const editedCategoryId = response.data.category.id;
            
            // Update hash jika belum sesuai
            if (window.location.hash !== `#${editedCategoryId}`) {
                window.location.hash = editedCategoryId;
            }
            
            // Reset dan aktifkan tab details
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
            $('.tab-content').removeClass('active').hide();
            $('#category-details').addClass('active').show();
            
            // Buka panel kanan
            $('.wp-category-container').addClass('with-right-panel');
            $('.wp-category-right-panel').addClass('visible');
            
            // Update display data
            this.displayData(response.data);
            
            // Refresh DataTable
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }
        }
    },
          

        handleDeleted() {
            this.closePanel();
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }
            if (window.Dashboard) {
               window.Dashboard.loadStats(); // Gunakan loadStats() langsung
            }
        },


       /**
        * Load category statistics including total categories and branches.
        * Uses getCurrentCategoryId() to determine which category's stats to load.
        * Updates stats display via updateStats() when data is received.
        * 
        * @async
        * @fires category:loading When stats loading begins
        * @fires category:loaded When stats are successfully loaded
        * @see getCurrentCategoryId
        * @see updateStats
        * 
        * @example
        * // Load stats on page load 
        * Category.loadStats();
        * 
        * // Load stats after category creation
        * $(document).on('category:created', () => Category.loadStats());
        */
       async loadStats() {
           const hash = window.location.hash;
           const categoryId = hash ? parseInt(hash.substring(1)) : 0;
           
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'get_category_stats',
                   nonce: wpEquipmentData.nonce,
                   id: categoryId
               },
               success: (response) => {
                   if (response.success) {
                       this.updateStats(response.data);
                   }
               }
           });
       },

       updateStats(stats) {
           $('#total-categories').text(stats.total_categories);
           $('#total-branches').text(stats.total_branches);
           $('#total-employees').text(stats.total_employees);
       }

    };

       $('.wp-mpdf-category-detail-export-pdf').on('click', function() {
           const categoryId = $('#current-category-id').val();
           
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'generate_category_pdf',
                   id: categoryId,
                   nonce: wpEquipmentData.nonce
               },
               xhrFields: {
                   responseType: 'blob'
               },
               success: function(response) {
                   const blob = new Blob([response], { type: 'application/pdf' });
                   const url = window.URL.createObjectURL(blob);
                   const a = document.createElement('a');
                   a.href = url;
                   a.download = `category-${categoryId}.pdf`;
                   document.body.appendChild(a);
                   a.click();
                   window.URL.revokeObjectURL(url);
               },
               error: function() {
                   EquipmentToast.error('Failed to generate PDF');
               }
           });
       });

       // Document generation handlers
       $('.wp-docgen-category-detail-expot-document').on('click', function() {
           const categoryId = $('#current-category-id').val();
           
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'generate_wp_docgen_category_detail_document',
                   id: categoryId,
                   nonce: wpEquipmentData.nonce
               },
               success: function(response) {
                   if (response.success) {
                       // Create hidden link and trigger download
                       const a = document.createElement('a');
                       a.href = response.data.file_url;
                       a.download = response.data.filename;
                       document.body.appendChild(a);
                       a.click();
                       document.body.removeChild(a);
                   } else {
                       EquipmentToast.error(response.data.message || 'Failed to generate DOCX');
                   }
               },
               error: function() {
                   EquipmentToast.error('Failed to generate DOCX');
               }
           });
       });

       $('.wp-docgen-category-detail-expot-pdf').on('click', function() {
           const categoryId = $('#current-category-id').val();
           
           $.ajax({
               url: wpEquipmentData.ajaxUrl,
               type: 'POST',
               data: {
                   action: 'generate_wp_docgen_category_detail_pdf',
                   id: categoryId,
                   nonce: wpEquipmentData.nonce
               },
               success: function(response) {
                   if (response.success) {
                       // Create hidden link and trigger download
                       const a = document.createElement('a');
                       a.href = response.data.file_url;
                       a.download = response.data.filename;
                       document.body.appendChild(a);
                       a.click();
                       document.body.removeChild(a);
                   } else {
                       EquipmentToast.error(response.data.message || 'Failed to generate PDF');
                   }
               },
               error: function() {
                   EquipmentToast.error('Failed to generate PDF');
               }
           });
       });

       
    // Initialize when document is ready
    $(document).ready(() => {
        window.Category = Category;
        Category.init();
    });

})(jQuery);


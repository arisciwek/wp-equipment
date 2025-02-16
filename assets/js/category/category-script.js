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
                totalCategories: null
            }
        },

        init() {
            this.components = {
                container: $('.wp-category-container'),
                rightPanel: $('.wp-category-right-panel'),
                detailsPanel: $('#category-details'),
                stats: {
                    totalCategories: $('#total-categories')
                }
            };

            // Load add category button
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
            
            // Update stats after CRUD operations
            $(document)
                .on('category:created.Category', () => this.loadStats())
                .on('category:deleted.Category', () => this.loadStats());
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

            // Panel events
            $('.wp-category-close-panel').off('click').on('click', () => this.closePanel());

            // Panel navigation
            $('.nav-tab').off('click').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });

            // Window events
            $(window).off('hashchange.Category').on('hashchange.Category', () => this.handleHashChange());
        },

        handleInitialState() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                this.handleHashChange();
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
                $('.tab-content').removeClass('active');
                $('#category-details').addClass('active');
                $('.nav-tab').removeClass('nav-tab-active');
                $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
                
                this.loadCategoryData(id);
            }
        },
        async loadCategoryData(id) {
            if (!id || this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            try {
                console.log('Memuat data kategori untuk ID:', id);

                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });

                console.log('Response data kategori:', response);

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

                    // Trigger event sukses
                    $(document).trigger('category:loaded', [response.data]);
                } else {
                    throw new Error(response.data?.message || 'Gagal memuat data kategori');
                }
            } catch (error) {
                console.error('Error memuat kategori:', error);
                EquipmentToast.error(error.message || 'Gagal memuat data kategori');
                this.handleLoadError();
            } finally {
                this.isLoading = false;
                this.hideLoading();
            }
        },

        displayData(data) {
            if (!data?.category) {
                console.error('Data kategori tidak valid:', data);
                return;
            }

            console.log('Menampilkan data kategori:', data);

            try {
                // PENTING: Tampilkan panel terlebih dahulu
                this.components.container.addClass('with-right-panel');
                this.components.rightPanel.addClass('visible');

                // Informasi Dasar
                $('#category-header-name').text(data.category.name);
                $('#category-code').text(data.category.code || '-');
                $('#category-name').text(data.category.name || '-');
                $('#category-description').text(data.category.description || '-');

                // Informasi Level
                $('#category-level').text(this.getLevelLabel(data.category.level));
                $('#category-parent').text(data.category.parent_name || '-');
                $('#category-sort-order').text(data.category.sort_order || '0');

                // Informasi Produk
                $('#category-unit').text(data.category.unit || '-');
                $('#category-pnbp').text(data.category.pnbp ? 
                    new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(data.category.pnbp) : '-'
                );

                // Informasi Timeline
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
                console.error('Error menampilkan data kategori:', error);
                EquipmentToast.error('Error menampilkan data kategori');
            }
        },

        handleLoadError() {
            this.components.detailsPanel.html(
                '<div class="error-message">' +
                '<p>Gagal memuat data kategori. Silakan coba lagi.</p>' +
                '<button class="button retry-load">Coba Lagi</button>' +
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
            this.loadStats();
        },

        async loadStats() {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category_stats',
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success) {
                    this.updateStats(response.data);
                }
            } catch (error) {
                console.error('Error memuat statistik:', error);
            }
        },

        updateStats(stats) {
            $('#total-categories').text(stats.total_categories || '0');
        }
    };

    // Inisialisasi saat dokumen siap
    $(document).ready(() => {
        window.Category = Category;
        Category.init();
    });

})(jQuery);
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
        // Properties
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

        // Initialization
        init() {
            // Check dependencies
            if (typeof EquipmentToast === 'undefined') {
                console.error('Required dependency not found: EquipmentToast');
                return;
            }

            // Initialize component references
            this.components = {
                container: $('.wp-equipment-container'),
                rightPanel: $('.wp-equipment-right-panel'),
                detailsPanel: $('#category-details'),
                stats: {
                    totalCategories: $('#total-categories')
                }
            };

            this.initializePanels();
            this.bindCoreEvents();
            this.handleInitialState();
        },

        // Panel Initialization
        initializePanels() {
            // Setup panel visibility classes
            this.components.rightPanel.addClass('panel-initialized');
            
            // Initialize tab navigation
            $('.nav-tab-wrapper .nav-tab').first().addClass('nav-tab-active');
            $('.tab-content').first().addClass('active');
            
            // Setup panel close button
            $('.wp-equipment-close-panel').on('click', () => this.closePanel());
        },

        // Core Event Binding
        bindCoreEvents() {
            // Panel navigation
            $('.nav-tab').off('click').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });

            // Hash change handling
            $(window).off('hashchange.Category').on('hashchange.Category', () => {
                this.handleHashChange();
            });

            // Panel state events
            $(document).off('panel:closed.Category').on('panel:closed.Category', () => {
                this.resetPanelState();
            });

            // Escape key handler
            $(document).on('keyup', (e) => {
                if (e.key === 'Escape' && this.components.rightPanel.is(':visible')) {
                    this.closePanel();
                }
            });
        },

        // Initial State Handler
        handleInitialState() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const id = hash.substring(1);
                if (id && !isNaN(id)) {
                    this.currentId = parseInt(id);
                    this.loadCategoryData(this.currentId);
                }
            }
        },

        // Tab Management
        switchTab(tabId) {
            // Validate tab existence
            if (!tabId || !$(`#${tabId}`).length) {
                console.error('Invalid tab ID:', tabId);
                return;
            }

            // Update active states
            $('.nav-tab').removeClass('nav-tab-active');
            $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

            $('.tab-content').removeClass('active').hide();
            $(`#${tabId}`).addClass('active').show();

            // Trigger tab change event
            $(document).trigger('category:tabChanged', [tabId]);
        },

        // Panel State Management
        showPanel() {
            this.components.container.addClass('with-right-panel');
            this.components.rightPanel
                .addClass('visible')
                .removeClass('loading');
        },

        closePanel() {
            this.components.container.removeClass('with-right-panel');
            this.components.rightPanel.removeClass('visible');
            this.currentId = null;
            
            // Clear hash without triggering reload
            if (window.history.pushState) {
                window.history.pushState('', '/', window.location.pathname + window.location.search);
            } else {
                window.location.hash = '';
            }

            $(document).trigger('panel:closed');
        },

        resetPanelState() {
            // Reset all form states
            $('.tab-content').removeClass('active');
            $('#category-details').addClass('active');
            
            // Reset tab navigation
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="category-details"]').addClass('nav-tab-active');
            
            // Clear any highlighted rows
            $('#categories-table tr').removeClass('highlight');
        },

        // Loading State Management
        showLoading() {
            this.isLoading = true;
            this.components.rightPanel.addClass('loading');
            
            // Add loading overlay if needed
            if (!this.components.rightPanel.find('.loading-overlay').length) {
                this.components.rightPanel.append(`
                    <div class="loading-overlay">
                        <span class="spinner is-active"></span>
                    </div>
                `);
            }
        },

        hideLoading() {
            this.isLoading = false;
            this.components.rightPanel.removeClass('loading');
            this.components.rightPanel.find('.loading-overlay').remove();
        },

        // Hash Change Handler
        handleHashChange() {
            const hash = window.location.hash;
            
            if (!hash) {
                this.closePanel();
                return;
            }

            const id = hash.substring(1);
            if (id && id !== this.currentId) {
                this.loadCategoryData(parseInt(id));
            }
        },

        // Error Handler
        handleError(error, context = '') {
            console.error(`Category Error [${context}]:`, error);
            EquipmentToast.error(
                error?.responseJSON?.message || 
                error?.message || 
                'Terjadi kesalahan dalam memproses permintaan'
            );
        }
    };


    // Extend the Category object with data management methods
    $.extend(Category, {
        // Data Loading Methods
        async loadCategoryData(id) {
            if (!id || this.isLoading) return;

            this.showLoading();
            console.log('Loading category data for ID:', id);

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category',
                        id: id,
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success && response.data) {
                    // Update URL hash tanpa trigger reload
                    const newHash = `#${id}`;
                    if (window.location.hash !== newHash) {
                        window.history.pushState(null, '', newHash);
                    }

                    // Reset ke tab details
                    this.switchTab('category-details');
                    
                    // Update UI dengan data baru
                    this.displayData(response.data);
                    this.currentId = id;

                    // Trigger success event
                    $(document).trigger('category:loaded', [response.data]);
                } else {
                    throw new Error(response.data?.message || 'Failed to load category data');
                }
            } catch (error) {
                this.handleError(error, 'loadCategoryData');
                this.handleLoadError();
            } finally {
                this.hideLoading();
            }
        },

        // Display Methods
        displayData(data) {
            if (!data?.category) {
                console.error('Invalid category data:', data);
                return;
            }

            const category = data.category;
            console.log('Displaying category data:', category);

            try {
                // Show right panel first
                this.showPanel();

                // Basic Information
                $('#category-header-name').text(category.name);
                $('#category-code').text(category.code || '-');
                $('#category-name').text(category.name || '-');
                $('#category-description').text(category.description || '-');

                // Hierarchy Information
                $('#category-level').text(this.getLevelLabel(category.level));
                $('#category-parent').text(category.parent_name || '-');
                $('#category-sort-order').text(category.sort_order || '0');

                // Product Information
                $('#category-unit').text(category.unit || '-');
                $('#category-price').text(category.price ? 
                    new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(category.price) : '-'
                );

                // Timeline Information
                $('#category-created-by').text(category.created_by_name || '-');
                $('#category-created-at').text(category.created_at || '-');
                $('#category-updated-at').text(category.updated_at || '-');

                // Highlight DataTable row if exists
                if (window.CategoryDataTable) {
                    window.CategoryDataTable.highlightRow(category.id);
                }

                // Update hierarchy section if exists
                if (data.hierarchy) {
                    this.updateHierarchyView(data.hierarchy);
                }

                // Trigger display event
                $(document).trigger('category:displayed', [category]);

            } catch (error) {
                this.handleError(error, 'displayData');
                EquipmentToast.error('Error menampilkan data kategori');
            }
        },

        // Stats Management
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
                } else {
                    throw new Error(response.data?.message || 'Failed to load stats');
                }
            } catch (error) {
                this.handleError(error, 'loadStats');
            }
        },

        updateStats(stats) {
            // Update total categories
            this.components.stats.totalCategories.text(
                stats.total_categories?.toLocaleString() || '0'
            );

            // Update recent categories if available
            if (stats.recentlyAdded && Array.isArray(stats.recentlyAdded)) {
                this.updateRecentCategories(stats.recentlyAdded);
            }

            // Trigger stats updated event
            $(document).trigger('category:statsUpdated', [stats]);
        },

        // Helper Methods
        getLevelLabel(level) {
            const labels = {
                1: 'Level 1 - Kategori Utama',
                2: 'Level 2 - Sub Kategori',
                3: 'Level 3 - Tipe Layanan'
            };
            return labels[level] || `Level ${level}`;
        },

        updateHierarchyView(hierarchy) {
            const $container = $('#category-hierarchy');
            if (!$container.length) return;

            const buildHierarchyHtml = (items) => {
                let html = '<ul class="category-tree">';
                items.forEach(item => {
                    html += `
                        <li class="category-item" data-id="${item.id}">
                            <div class="category-info">
                                <span class="category-code">${item.code}</span>
                                <span class="category-name">${item.name}</span>
                                ${item.unit || item.price ? `
                                    <small class="category-meta">
                                        ${item.unit ? `<span class="unit">${item.unit}</span>` : ''}
                                        ${item.price ? `<span class="price">${
                                            new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR'
                                            }).format(item.price)
                                        }</span>` : ''}
                                    </small>
                                ` : ''}
                            </div>
                            ${item.children ? buildHierarchyHtml(item.children) : ''}
                        </li>
                    `;
                });
                html += '</ul>';
                return html;
            };

            $container.html(buildHierarchyHtml(hierarchy));
        },

        updateRecentCategories(categories) {
            const $container = $('#recent-categories');
            if (!$container.length) return;

            let html = '<ul class="recent-list">';
            categories.forEach(category => {
                html += `
                    <li class="recent-item" data-id="${category.id}">
                        <strong>${category.code}</strong>
                        <span class="recent-name">${category.name}</span>
                        <small class="recent-date">${category.created_at}</small>
                    </li>
                `;
            });
            html += '</ul>';

            $container.html(html);
        },

        handleLoadError() {
            this.components.detailsPanel.html(`
                <div class="error-message">
                    <p>Gagal memuat data kategori. Silakan coba lagi.</p>
                    <button class="button retry-load">Coba Lagi</button>
                </div>
            `);

            // Bind retry button
            $('.retry-load').on('click', () => {
                if (this.currentId) {
                    this.loadCategoryData(this.currentId);
                }
            });
        }
    });


    // Extend the Category object with event and integration methods
    $.extend(Category, {
        // Event Binding Methods
        bindEvents() {
            // Unbind existing events first
            $(document)
                .off('.CategoryEvent')
                .on('category:created.CategoryEvent', (e, data) => this.handleCreated(data))
                .on('category:updated.CategoryEvent', (e, data) => this.handleUpdated(data))
                .on('category:deleted.CategoryEvent', () => this.handleDeleted())
                .on('category:loading.CategoryEvent', () => this.showLoading())
                .on('category:loaded.CategoryEvent', () => this.hideLoading())
                .on('category:tabChanged.CategoryEvent', (e, tabId) => this.handleTabChange(tabId));

            // Demo data generation button
            $('#generate-demo-categories-btn').on('click', () => this.handleGenerateDemo());

            // Retry button for error states
            $(document).on('click', '.retry-load', () => {
                if (this.currentId) {
                    this.loadCategoryData(this.currentId);
                }
            });

            // Panel actions
            $(document).on('click', '.category-action-button', (e) => {
                const action = $(e.currentTarget).data('action');
                const id = $(e.currentTarget).data('id');
                this.handlePanelAction(action, id);
            });

            // Tree view expand/collapse
            $('.expand-all').on('click', () => this.expandAllNodes());
            $('.collapse-all').on('click', () => this.collapseAllNodes());
        },

        // CRUD Event Handlers
        handleCreated(data) {
            if (data && data.id) {
                window.location.hash = data.id;
                this.loadCategoryData(data.id);
            }

            // Refresh DataTable
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }

            // Reload stats
            this.loadStats();
        },

        handleUpdated(data) {
            if (data && data.data && data.data.category) {
                const editedCategoryId = data.data.category.id;
                
                if (editedCategoryId === parseInt(window.location.hash.substring(1))) {
                    // Update panel if current category
                    this.displayData(data.data);
                } else {
                    // Change to edited category
                    window.location.hash = editedCategoryId;
                }
            }

            // Refresh DataTable
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }

            // Reload stats
            this.loadStats();
        },

        handleDeleted() {
            this.closePanel();
            
            // Refresh DataTable
            if (window.CategoryDataTable) {
                window.CategoryDataTable.refresh();
            }

            // Reload stats
            this.loadStats();
        },

        // Tab Change Handler
        handleTabChange(tabId) {
            switch(tabId) {
                case 'category-hierarchy':
                    this.loadHierarchyData();
                    break;
                    
                // Add more tab specific handlers here
            }
        },

        // Demo Data Generator
        async handleGenerateDemo() {
            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'generate_demo_categories',
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success) {
                    EquipmentToast.success('Data demo berhasil dibuat');
                    
                    // Refresh components
                    if (window.CategoryDataTable) {
                        window.CategoryDataTable.refresh();
                    }
                    this.loadStats();
                } else {
                    throw new Error(response.data?.message || 'Gagal membuat data demo');
                }
            } catch (error) {
                this.handleError(error, 'generateDemo');
            }
        },

        // Tree View Methods
        expandAllNodes() {
            $('.category-tree li').addClass('expanded');
            $('.category-tree ul').show();
        },

        collapseAllNodes() {
            $('.category-tree li').removeClass('expanded');
            $('.category-tree ul').hide();
        },

        // Panel Action Handler
        handlePanelAction(action, id) {
            if (!action || !id) return;

            switch(action) {
                case 'edit':
                    if (window.CategoryForm) {
                        window.CategoryForm.showEditForm(id);
                    }
                    break;

                case 'delete':
                    if (window.CategoryDataTable) {
                        window.CategoryDataTable.confirmDelete(id);
                    }
                    break;

                case 'view-hierarchy':
                    this.switchTab('category-hierarchy');
                    break;
            }
        },

        // Hierarchy Data Loader
        async loadHierarchyData() {
            if (!this.currentId) return;

            try {
                const response = await $.ajax({
                    url: wpEquipmentData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_category_hierarchy',
                        id: this.currentId,
                        nonce: wpEquipmentData.nonce
                    }
                });

                if (response.success) {
                    this.updateHierarchyView(response.data.hierarchy);
                } else {
                    throw new Error(response.data?.message || 'Gagal memuat data hierarki');
                }
            } catch (error) {
                this.handleError(error, 'loadHierarchy');
            }
        },

        // Integration Methods
        validateDependencies() {
            const dependencies = {
                'CategoryDataTable': window.CategoryDataTable,
                'CategoryForm': window.CategoryForm,
                'EquipmentToast': window.EquipmentToast
            };

            let missing = [];
            for (const [name, component] of Object.entries(dependencies)) {
                if (!component) {
                    missing.push(name);
                }
            }

            if (missing.length > 0) {
                console.error('Missing dependencies:', missing.join(', '));
                return false;
            }

            return true;
        },

        // Override init method to include event binding
        init() {
            // Initialize base components
            this.components = {
                container: $('.wp-equipment-container'),
                rightPanel: $('.wp-equipment-right-panel'),
                detailsPanel: $('#category-details'),
                stats: {
                    totalCategories: $('#total-categories')
                }
            };

            // Validate dependencies
            if (!this.validateDependencies()) {
                console.error('Category initialization failed: missing dependencies');
                return;
            }

            // Initialize panels and core events
            this.initializePanels();
            this.bindCoreEvents();
            this.handleInitialState();

            // Bind extended events
            this.bindEvents();

            // Load initial stats
            this.loadStats();

            console.log('Category management fully initialized');
        },
    });
    
    // Initialize when document is ready
    $(document).ready(() => {
        window.Category = Category;
        Category.init();
    });

})(jQuery);


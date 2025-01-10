/**
 * Equipment Management Interface
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/equipment.js
 *
 * Description: Main JavaScript handler untuk halaman equipment.
 *              Mengatur interaksi antar komponen seperti DataTable,
 *              form, panel kanan, dan notifikasi.
 *              Includes state management dan event handling.
 *              Terintegrasi dengan WordPress AJAX API.
 *
 * Dependencies:
 * - jQuery
 * - EquipmentDataTable
 * - EquipmentForm
 * - EquipmentToast
 * - WordPress AJAX
 *
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper jQuery no-conflict handling
 * - Added panel kanan integration
 * - Added CRUD event handlers
 * - Added toast notifications
 * - Improved error handling
 * - Added loading states
 *
 * Last modified: 2024-12-03 16:45:00
 */
 (function($) {
     'use strict';

     const Equipment = {
         currentId: null,
         isLoading: false,
         components: {
             container: null,
             rightPanel: null,
             detailsPanel: null,
             stats: {
                 totalEquipments: null,
                 totalLicencees: null
             }
         },

         init() {
             this.components = {
                 container: $('.wp-equipment-container'),
                 rightPanel: $('.wp-equipment-right-panel'),
                 detailsPanel: $('#equipment-details'),
                 stats: {
                     totalEquipments: $('#total-equipments'),
                     totalLicencees: $('#total-licencees')
                 }
             };

             this.bindEvents();
             this.handleInitialState();
             // Tambahkan load stats saat inisialisasi
             this.loadStats();

             // Update stats setelah operasi CRUD
            $(document)
                .on('equipment:created.Equipment', () => this.loadStats())
                .on('equipment:deleted.Equipment', () => this.loadStats())
                .on('licence:created.Equipment', () => this.loadStats())
                .on('licence:deleted.Equipment', () => this.loadStats());
         },

         bindEvents() {
             // Unbind existing events first to prevent duplicates
             $(document)
                 .off('.Equipment')
                 .on('equipment:created.Equipment', (e, data) => this.handleCreated(data))
                 .on('equipment:updated.Equipment', (e, data) => this.handleUpdated(data))
                 .on('equipment:deleted.Equipment', () => this.handleDeleted())
                 .on('equipment:display.Equipment', (e, data) => this.displayData(data))
                 .on('equipment:loading.Equipment', () => this.showLoading())
                 .on('equipment:loaded.Equipment', () => this.hideLoading());

             // Panel events
             $('.wp-equipment-close-panel').off('click').on('click', () => this.closePanel());

             // Panel navigation
             $('.nav-tab').off('click').on('click', (e) => {
                 e.preventDefault();
                 this.switchTab($(e.currentTarget).data('tab'));
             });

             // Window events
             $(window).off('hashchange.Equipment').on('hashchange.Equipment', () => this.handleHashChange());
         },

         handleInitialState() {
             const hash = window.location.hash;
             if (hash && hash.startsWith('#')) {
                 const id = hash.substring(1);
                 if (id && id !== this.currentId) {
                     this.loadEquipmentData(id);
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
                 $('.tab-content').removeClass('active');
                 $('#equipment-details').addClass('active');
                 $('.nav-tab').removeClass('nav-tab-active');
                 $('.nav-tab[data-tab="equipment-details"]').addClass('nav-tab-active');

                 this.loadEquipmentData(id);
             }
         },

         async loadEquipmentData(id) {
             if (!id || this.isLoading) return;

             this.isLoading = true;
             this.showLoading();

             try {
                 const response = await $.ajax({
                     url: wpEquipmentData.ajaxUrl,
                     type: 'POST',
                     data: {
                         action: 'get_equipment',
                         id: id,
                         nonce: wpEquipmentData.nonce
                     }
                 });

                 if (response.success) {
                     this.displayData(response.data);
                     this.currentId = id;
                 } else {
                     EquipmentToast.error(response.data?.message || 'Gagal memuat data equipment');
                 }
             } catch (error) {
                 console.error('Load equipment error:', error);
                 if (this.isLoading) {
                     EquipmentToast.error('Gagal menghubungi server');
                 }
             } finally {
                 this.isLoading = false;
                 this.hideLoading();
             }
         },

         displayData(data) {
             if (!data || !data.equipment) {
                 EquipmentToast.error('Data equipment tidak valid');
                 return;
             }

             $('.tab-content').removeClass('active');
             $('#equipment-details').addClass('active');
             $('.nav-tab').removeClass('nav-tab-active');
             $('.nav-tab[data-tab="equipment-details"]').addClass('nav-tab-active');

             this.components.container.addClass('with-right-panel');
             this.components.rightPanel.addClass('visible');

             const createdAt = new Date(data.equipment.created_at).toLocaleString('id-ID');
             const updatedAt = new Date(data.equipment.updated_at).toLocaleString('id-ID');

             $('#equipment-header-name').text(data.equipment.name);
             $('#equipment-name').text(data.equipment.name);
             $('#equipment-licence-count').text(data.licence_count);
             $('#equipment-created-at').text(createdAt);
             $('#equipment-updated-at').text(updatedAt);

             if (window.EquipmentDataTable) {
                 window.EquipmentDataTable.highlightRow(data.equipment.id);
             }

            // Tambahkan handling untuk membership data
            if (data.equipment.membership) {
                // Update membership badge
                $('#current-level-badge').text(data.equipment.membership.level);
                
                // Update staff usage
                const staffUsage = data.equipment.staff_count || 0;
                const staffLimit = data.equipment.membership.max_staff;
                $('#staff-usage-count').text(staffUsage);
                $('#staff-usage-limit').text(staffLimit === -1 ? 'Unlimited' : staffLimit);
                
                // Calculate progress bar percentage
                if (staffLimit !== -1) {
                    const percentage = (staffUsage / staffLimit) * 100;
                    $('#staff-usage-bar').css('width', `${percentage}%`);
                }

                // Update capabilities list
                const $capList = $('#active-capabilities').empty();
                Object.entries(data.equipment.membership.capabilities).forEach(([cap, enabled]) => {
                    if (enabled) {
                        $capList.append(`<li>${this.getCapabilityLabel(cap)}</li>`);
                    }
                });

                // Show/hide upgrade buttons based on current level
                const currentLevel = data.equipment.membership.level;
                $('.upgrade-card').each(function() {
                    const cardLevel = $(this).attr('id').replace('-plan', '');
                    $(this).toggle(this.shouldShowUpgradeOption(currentLevel, cardLevel));
                });
            }



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
                $('.nav-tab').removeClass('nav-tab-active');
                $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

                $('.tab-content').removeClass('active');
                $(`#${tabId}`).addClass('active');

                // Tambahkan ini untuk menangani tampilan tab membership
                if (tabId === 'membership-info') {
                    $('#membership-info').show();
                } else {
                    $('#membership-info').hide();
                }

                if (tabId === 'licence-list' && this.currentId) {
                    if (window.LicenceDataTable) {
                        window.LicenceDataTable.init(this.currentId);
                    }
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

             if (window.EquipmentDataTable) {
                 window.EquipmentDataTable.refresh();
             }

             if (window.Dashboard) {
                 window.Dashboard.refreshStats();
             }
         },

         handleUpdated(data) {
             if (data && data.data && data.data.equipment) {
                 if (this.currentId === data.data.equipment.id) {
                     this.displayData(data.data);
                 }
             }
         },

         handleDeleted() {
             this.closePanel();
             if (window.EquipmentDataTable) {
                 window.EquipmentDataTable.refresh();
             }
             if (window.Dashboard) {
                window.Dashboard.loadStats(); // Gunakan loadStats() langsung
             }
         },

         loadStats() {
            $.ajax({
                url: wpEquipmentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_equipment_stats',
                    nonce: wpEquipmentData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    }
                }
            });
        },

        updateStats(stats) {
            $('#total-equipments').text(stats.total_equipments);
            $('#total-licencees').text(stats.total_licencees);
        }

     };

     // Initialize when document is ready
     $(document).ready(() => {
         window.Equipment = Equipment;
         Equipment.init();
     });

 })(jQuery);

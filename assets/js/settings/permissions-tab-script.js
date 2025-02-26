/**
 * Permission Matrix Script
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Settings
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/settings/permissions-script.js
 *
 * Description: Handler untuk matrix permission
 *              Menangani update dan reset permission matrix
 *              Terintegrasi dengan modal konfirmasi dan toast notifications
 *              Support untuk nested tabs
 *
 * Dependencies:
 * - jQuery
 * - wpEquipmentToast
 * - WIModal component
 *
 * Changelog:
 * 1.1.0 - 2025-02-26
 * - Added support for nested tab navigation
 * - Improved tab state persistence
 * - Optimized AJAX handling
 * 
 * 1.0.1 - 2024-12-08
 * - Replaced native confirm with WIModal for reset confirmation
 * - Added warning type modal styling
 * - Enhanced UX for reset operation
 * - Improved error handling and feedback
 *
 * 1.0.0 - 2024-12-02
 * - Initial implementation
 * - Basic permission matrix handling
 * - AJAX integration
 * - Toast notifications
 */

(function($) {
    'use strict';

    const PermissionMatrix = {
        init() {
            this.form = $('#wp-equipment-permissions-form');
            this.submitBtn = $('#save-permissions');
            this.resetBtn = $('#reset-permissions-btn');
            this.bindEvents();
            this.initTooltips();
        },

        bindEvents() {
            this.form.on('submit', (e) => this.handleSubmit(e));
            this.resetBtn.on('click', (e) => this.handleReset(e));
            
            // Handle tab navigation
            $('.nav-tab').on('click', function() {
                // Visual indication of loading
                $(this).addClass('loading');
            });
        },
        
        initTooltips() {
            if ($.fn.tooltip) {
                $('.tooltip-icon').tooltip({
                    position: { my: "center bottom", at: "center top-10" }
                });
            }
        },

        handleSubmit(e) {
            e.preventDefault();
            const currentSubtab = $('input[name="current_subtab"]').val();

            // Show loading state
            this.submitBtn.prop('disabled', true);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_wp_equipment_permissions',
                    security: $('input[name="_wpnonce"]').val(),
                    permissions: this.collectPermissions(),
                    current_subtab: currentSubtab
                },
                success: (response) => {
                    if (response.success) {
                        wpEquipmentToast.success(response.data.message || 'Hak akses berhasil diperbarui');
                        if (response.data.reload) {
                            window.location.reload();
                        }
                    } else {
                        wpEquipmentToast.error(response.data.message || 'Terjadi kesalahan saat memperbarui hak akses');
                    }
                },
                error: () => {
                    wpEquipmentToast.error('Gagal menghubungi server. Silakan coba lagi.');
                },
                complete: () => {
                    this.submitBtn.prop('disabled', false);
                }
            });
        },

        collectPermissions() {
            const permissions = {};
            this.form.find('input[type="checkbox"]').each(function() {
                const $checkbox = $(this);
                const name = $checkbox.attr('name');
                if (name && name.startsWith('permissions[')) {
                    const matches = name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                    if (matches) {
                        const role = matches[1];
                        const cap = matches[2];
                        if (!permissions[role]) {
                            permissions[role] = {};
                        }
                        permissions[role][cap] = $checkbox.is(':checked') ? 1 : 0;
                    }
                }
            });
            return permissions;
        },

        handleReset(e) {
            e.preventDefault();
        
            WIModal.show({
                title: 'Konfirmasi Reset',
                message: 'Yakin ingin mereset semua hak akses ke default? Aksi ini tidak dapat dibatalkan.',
                icon: 'alert-triangle',
                type: 'warning',
                confirmText: 'Reset',
                confirmClass: 'button-warning',
                cancelText: 'Batal',
                onConfirm: () => this.performReset()
            });
        },
        
        performReset() {
            const $button = $('#reset-permissions-btn');
            
            // Set loading state
            $button.addClass('loading')
                   .prop('disabled', true);
        
            // Perform AJAX reset
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'reset_permissions',
                    nonce: $('#reset_permissions_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        wpEquipmentToast.success(response.data.message || 'Permissions reset successfully');
                        // Reload page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        wpEquipmentToast.error(response.data.message || 'Failed to reset permissions');
                        $button.removeClass('loading')
                               .prop('disabled', false);
                    }
                },
                error: function() {
                    wpEquipmentToast.error('Server error while resetting permissions');
                    $button.removeClass('loading')
                           .prop('disabled', false);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('#wp-equipment-permissions-form').length) {
            PermissionMatrix.init();
        }
    });

})(jQuery);

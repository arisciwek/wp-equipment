/**
 * Select List Handler UI
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.1.0
 * @author      arisciwek
 * 
 * Path: /wp-equipment/assets/js/components/select-handler-ui.js
 * 
 * Description: 
 * - UI components untuk select list wilayah
 * - Menangani tampilan loading states
 * - Error message display
 * - Success indicators
 * - Mobile responsive styling
 * 
 * Dependencies:
 * - jQuery
 * - select-handler-core.js
 * - EquipmentToast (optional)
 * 
 * Usage:
 * Loaded after select-handler-core.js through admin-enqueue-scripts
 * 
 * Changelog:
 * v1.1.0 - 2024-01-07
 * - Added success indicators
 * - Enhanced mobile responsiveness
 * - Improved error display
 * - Added accessibility features
 * 
 * v1.0.0 - 2024-01-06
 * - Initial version
 * - Basic styling
 * - Loading indicators
 */

(function($) {
    'use strict';

    // Extend WPSelect with UI specific methods
    $.extend(window.WPSelect, {
        /**
         * Handle errors
         */
        handleError(e, message) {
            console.error('WP Select Error:', message);
            
            // Show error message
            this.showErrorMessage(message);
            
            // Remove loading states
            $('.wp-equipment-licence-select').each((i, el) => {
                this.hideLoading($(el));
            });
        },

        /**
         * Show error message
         */
        showErrorMessage(message) {
            if (typeof EquipmentToast !== 'undefined') {
                EquipmentToast.error(message);
            } else {
                // Create and show error element if toast not available
                const $error = $('<div>', {
                    class: 'wp-equipment-error',
                    text: message
                });

                // Remove existing error messages
                $('.wp-equipment-error').remove();

                // Add new error message after the licence select
                $('.wp-equipment-licence-select').after($error);

                // Auto hide after 5 seconds
                setTimeout(() => {
                    $error.fadeOut(() => $error.remove());
                }, 5000);
            }
        },

        /**
         * Handle successful licence data load
         */
        handleBranchLoaded(e) {
            const $licence = $(e.target);
            this.debugLog('Branch data loaded');
            
            // Remove any existing error messages
            $('.wp-equipment-error').remove();
            
            // Trigger change event for dependent elements
            $licence.trigger('change');

            // Add success indicator
            this.showSuccessIndicator($licence);
        },

        /**
         * Show success indicator
         */
        showSuccessIndicator($element) {
            const $success = $('<span>', {
                class: 'wp-equipment-success',
                html: '&#10004;' // Checkmark
            });

            // Remove existing indicators
            $('.wp-equipment-success').remove();

            // Add success indicator
            $element.after($success);

            // Auto remove after 2 seconds
            setTimeout(() => {
                $success.fadeOut(() => $success.remove());
            }, 2000);
        },

        /**
         * Debug logging
         */
        debugLog(...args) {
            if (this.debug) {
                console.log('WP Select Debug:', ...args);
            }
        }
    });

    // Add CSS styles
    const style = `
        .wp-equipment-loading {
            margin-left: 8px;
            color: #666;
            display: inline-block;
            vertical-align: middle;
        }
        
        select.wp-equipment-equipment-select,
        select.wp-equipment-licence-select {
            min-width: 200px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        select.wp-equipment-equipment-select:focus,
        select.wp-equipment-licence-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        select.loading {
            background-color: #f8f9fa;
            cursor: wait;
            opacity: 0.8;
        }
        
        .wp-equipment-error {
            color: #dc3545;
            margin-top: 4px;
            font-size: 0.875em;
            padding: 4px 8px;
            background-color: #fff;
            border: 1px solid #dc3545;
            border-radius: 4px;
            display: inline-block;
        }

        .wp-equipment-success {
            color: #28a745;
            margin-left: 8px;
            display: inline-block;
            vertical-align: middle;
            animation: fadeInOut 2s ease-in-out;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* Hover effects */
        select.wp-equipment-equipment-select:not(:disabled):hover,
        select.wp-equipment-licence-select:not(:disabled):hover {
            border-color: #80bdff;
        }

        /* Disabled state */
        select.wp-equipment-equipment-select:disabled,
        select.wp-equipment-licence-select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            select.wp-equipment-equipment-select,
            select.wp-equipment-licence-select {
                width: 100%;
                max-width: none;
            }
            
            .wp-equipment-error {
                display: block;
                margin-top: 8px;
            }
        }
    `;

    $('<style>').text(style).appendTo('head');

    // Add accessibility attributes
    $('.wp-equipment-equipment-select, .wp-equipment-licence-select').each(function() {
        const $select = $(this);
        if (!$select.attr('aria-label')) {
            $select.attr('aria-label', $select.hasClass('wp-equipment-equipment-select') ? 
                'Pilih Equipment' : 'Pilih Surat Keterangan');
        }
    });

})(jQuery);

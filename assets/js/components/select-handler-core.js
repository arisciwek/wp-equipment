/**
 * Select List Handler Core
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.1.0
 * @author      arisciwek
 * 
 * Path: /wp-equipment/assets/js/components/select-handler-core.js
 * 
 * Description: 
 * - Core functionality untuk select list equipments
 * - Menangani AJAX loading untuk data pertama
 * - Includes error handling dan loading states
 * - Terintegrasi dengan cache system
 * 
 * 
 * Dependencies:
 * - jQuery
 * - WordPress AJAX API
 * - EquipmentToast for notifications
 * 
 * Usage:
 * Loaded through admin-enqueue-scripts hook
 * 
 * Changelog:
 * v1.1.0 - 2024-01-07
 * - Added loading state management
 * - Enhanced error handling
 * - Added debug mode
 * - Improved AJAX reliability
 * 
 * v1.0.0 - 2024-01-06
 * - Initial version
 * - Basic AJAX functionality
 * - Equipment-licence relation
 */

(function($) {
    'use strict';

    const WPSelect = {
        /**
         * Initialize the handler
         */
        init() {
            this.debug = typeof wpEquipmentData !== 'undefined' && wpEquipmentData.debug;
            this.bindEvents();
            this.setupLoadingState();

            // Initialize toast if available
            if (typeof EquipmentToast !== 'undefined') {
                this.debugLog('EquipmentToast initialized');
            }

            // Trigger initialization complete event
            $(document).trigger('wp_equipment:initialized');
        },

        /**
         * Bind event handlers
         */
        bindEvents() {
            $(document)
                .on('change', '.wp-equipment-equipment-select', this.handleEquipmentChange.bind(this))
                .on('wp_equipment:loaded', '.wp-equipment-licence-select', this.handleBranchLoaded.bind(this))
                .on('wp_equipment:error', this.handleError.bind(this))
                .on('wp_equipment:beforeLoad', this.handleBeforeLoad.bind(this))
                .on('wp_equipment:afterLoad', this.handleAfterLoad.bind(this));
        },

        /**
         * Setup loading indicator
         */
        setupLoadingState() {
            this.$loadingIndicator = $('<span>', {
                class: 'wp-equipment-loading',
                text: wpEquipmentData.texts.loading || 'Loading...'
            }).hide();

            // Add loading indicator after each licence select
            $('.wp-equipment-licence-select').after(this.$loadingIndicator.clone());
        },

        /**
         * Handle equipment selection change
         */
        handleEquipmentChange(e) {
            const $equipment = $(e.target);
            const $licence = $('.wp-equipment-licence-select');
            const equipmentId = $equipment.val();

            this.debugLog('Equipment changed:', equipmentId);

            // Reset and disable licence select
            this.resetBranchSelect($licence);

            if (!equipmentId) {
                return;
            }

            // Trigger before load event
            $(document).trigger('wp_equipment:beforeLoad', [$equipment, $licence]);

            // Show loading state
            this.showLoading($licence);

            // Make AJAX call
            $.ajax({
                url: wpEquipmentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_licence_options',
                    equipment_id: equipmentId,
                    nonce: wpEquipmentData.nonce
                },
                success: (response) => {
                    this.debugLog('AJAX response:', response);

                    if (response.success) {
                        $licence.html(response.data.html);
                        $licence.trigger('wp_equipment:loaded', [response.data]);
                    } else {
                        $(document).trigger('wp_equipment:error', [
                            response.data.message || wpEquipmentData.texts.error
                        ]);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    this.debugLog('AJAX error:', textStatus, errorThrown);
                    $(document).trigger('wp_equipment:error', [
                        wpEquipmentData.texts.error || 'Failed to load data'
                    ]);
                },
                complete: () => {
                    this.hideLoading($licence);
                    // Trigger after load event
                    $(document).trigger('wp_equipment:afterLoad', [$equipment, $licence]);
                }
            });
        },

        /**
         * Reset licence select to initial state
         */
        resetBranchSelect($licence) {
            $licence
                .prop('disabled', true)
                .html(`<option value="">${wpEquipmentData.texts.select_licence}</option>`);
        },

        /**
         * Show loading state
         */
        showLoading($element) {
            $element.prop('disabled', true);
            $element.next('.wp-equipment-loading').show();
            $element.addClass('loading');
            this.debugLog('Loading state shown');
        },

        /**
         * Hide loading state
         */
        hideLoading($element) {
            $element.prop('disabled', false);
            $element.next('.wp-equipment-loading').hide();
            $element.removeClass('loading');
            this.debugLog('Loading state hidden');
        },

        /**
         * Handle before load event
         */
        handleBeforeLoad(e, $equipment, $licence) {
            this.debugLog('Before load event triggered');
            // Add any custom pre-load handling here
        },

        /**
         * Handle after load event
         */
        handleAfterLoad(e, $equipment, $licence) {
            this.debugLog('After load event triggered');
            // Add any custom post-load handling here
        }
    };

    // Export to window for extensibility
    window.WPSelect = WPSelect;

    // Initialize on document ready
    $(document).ready(() => WPSelect.init());

})(jQuery);

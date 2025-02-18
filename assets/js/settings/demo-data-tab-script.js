/**
 * Demo Data Tab Script
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/settings/demo-data-tab-script.js
 *
 * Description: Handles demo data generation functionality in the settings page
 *              including AJAX requests and UI feedback
 */
jQuery(document).ready(function($) {
    // Check dependencies on page load
    checkDependencies();

    function checkDependencies() {
        $('.generate-demo-data').each(function() {
            const button = $(this);
            const type = button.data('type');
            
            // Check development mode and data existence
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_demo_data',
                    type: type,
                    nonce: button.data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        const devModeEnabled = response.data.dev_mode;
                        
                        // Update button state
                        button.prop('disabled', !devModeEnabled);
                        
                        // Show appropriate message
                        if (!devModeEnabled) {
                            $('#demo-data-messages').html(
                                '<div class="notice notice-warning is-dismissible"><p>' + 
                                'Please enable Development Mode and save changes before generating demo data.' + 
                                '</p></div>'
                            );
                            button.attr('title', 'Enable Development Mode and save changes first');
                        } else {
                            button.attr('title', 'Click to generate demo data');
                        }
                    }
                }
            });
        });
    }

    $('.generate-demo-data').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const type = button.data('type');
        const nonce = button.data('nonce');
        
        // Check if button should be disabled
        if (button.prop('disabled')) {
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).html('Generating...');
        
        // Use consistent action name and pass type as parameter
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_demo_data',
                type: type,
                nonce: nonce
            },
            success: function(response) {
                const messageDiv = $('#demo-data-messages');
                messageDiv.empty();

                if (response.success) {
                    messageDiv.html(
                        '<div class="notice notice-success is-dismissible"><p>' + 
                        response.data.message + 
                        '</p></div>'
                    );
                } else {
                    // Different styling for development mode off vs other errors
                    const noticeClass = response.data.type === 'dev_mode_off' ? 
                        'notice-warning' : 'notice-error';
                    
                    messageDiv.html(
                        '<div class="notice ' + noticeClass + ' is-dismissible"><p>' + 
                        response.data.message + 
                        '</p></div>'
                    );
                }
            },
            error: function() {
                $('#demo-data-messages').html(
                    '<div class="notice notice-error is-dismissible"><p>' + 
                    'An unexpected error occurred while generating demo data.' + 
                    '</p></div>'
                );
            },
            complete: function() {
                // Restore button state and label based on type
                const label = type.charAt(0).toUpperCase() + type.slice(1);
                button.prop('disabled', false).html('Generate ' + label);
                // Recheck dependencies
                checkDependencies();
            }
        });
    });

    // Update dependencies when development mode changes
    $('input[name="wp_equipment_development_settings[enable_development]"]').on('change', function() {
        checkDependencies();
    });
});

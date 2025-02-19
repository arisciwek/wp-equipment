/**
 * Demo Data Services Tab Script
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/settings/demo-data-services-tab-script.js
 */
jQuery(document).ready(function($) {
    // Check service button state on page load
    checkButtonState();

    function checkButtonState() {
        const button = $('.generate-demo-data[data-type="service"]');
            
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'check_demo_data',
                type: 'service',
                nonce: button.data('nonce')
            },
            success: function(response) {
                if (response.success) {
                    const devModeEnabled = response.data.dev_mode;
                    
                    button.prop('disabled', !devModeEnabled);
                    
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
    }

    // Handle service generation
    $('.generate-demo-data[data-type="service"]').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const nonce = button.data('nonce');
        
        button.prop('disabled', true).html('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_demo_data',
                type: 'service',
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
                button.prop('disabled', false).html('Generate Services');
                checkButtonState();
            }
        });
    });

    // Update button state when development mode changes
    $('input[name="wp_equipment_development_settings[enable_development]"]').on('change', function() {
        checkButtonState();
    });
});

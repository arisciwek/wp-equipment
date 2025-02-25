/**
 * Cache Diagnostics Tab Script
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/settings/diagnostics-tab-script.js
 *
 * Description: Fungsi JavaScript untuk halaman diagnostik cache.
 *              Melakukan AJAX request untuk diagnostik cache dan clearing cache.
 *              Juga menangani tab switching pada halaman diagnostik.
 * 
 * Dependencies:
 * - jQuery
 */

jQuery(document).ready(function($) {
    // Tab switching
    $('.diagnostics-tab').on('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.diagnostics-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show corresponding content
        const tab = $(this).data('tab');
        $('.diagnostics-content').hide();
        $(`.diagnostics-content.${tab}`).show();
    });
    
    // Run cache diagnostics
    $('#run-cache-diagnostics').on('click', function() {
        $('.diagnostics-loading').show();
        $('.diagnostics-results').hide();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_equipment_cache_diagnostics',
                nonce: wpEquipmentData.nonce
            },
            success: function(response) {
                $('.diagnostics-loading').hide();
                
                if (response.success) {
                    const diagnostics = response.data.diagnostics;
                    
                    // Clear previous results
                    $('#cache-results').empty();
                    $('#cache-recommendations').empty();
                    
                    // Add status with appropriate color
                    let statusClass = 'neutral';
                    if (diagnostics.status === 'healthy') {
                        statusClass = 'success';
                    } else if (diagnostics.status === 'failing' || diagnostics.status === 'error') {
                        statusClass = 'error';
                    }
                    
                    $('#cache-results').append(`
                        <tr>
                            <td><strong>Overall Status</strong></td>
                            <td><span class="status-badge ${statusClass}">${diagnostics.status}</span></td>
                        </tr>
                        <tr>
                            <td>Using External Cache</td>
                            <td>${diagnostics.using_external_cache ? 'Yes' : 'No'}</td>
                        </tr>
                        <tr>
                            <td>Cache Type</td>
                            <td>${diagnostics.cache_type}</td>
                        </tr>
                        <tr>
                            <td>Cache Tests Passing</td>
                            <td>${diagnostics.cache_test_success ? 'Yes' : 'No'}</td>
                        </tr>
                    `);
                    
                    // Add test results
                    for (const [test, result] of Object.entries(diagnostics.tests)) {
                        $('#cache-results').append(`
                            <tr>
                                <td>Test: ${test}</td>
                                <td>${result ? '✅ Pass' : '❌ Fail'}</td>
                            </tr>
                        `);
                    }
                    
                    // Add cache hit rate if available
                    if (diagnostics.cache_hit_rate) {
                        $('#cache-results').append(`
                            <tr>
                                <td>Cache Hit Rate</td>
                                <td>${diagnostics.cache_hit_rate}</td>
                            </tr>
                        `);
                    }
                    
                    // Add recommendations
                    if (diagnostics.recommendations && diagnostics.recommendations.length > 0) {
                        diagnostics.recommendations.forEach(rec => {
                            $('#cache-recommendations').append(`<li>${rec}</li>`);
                        });
                    } else {
                        $('#cache-recommendations').append(`<li>No recommendations at this time.</li>`);
                    }
                    
                    // Show results
                    $('.diagnostics-results').show();
                    
                } else {
                    alert('Error running diagnostics: ' + response.data.message);
                }
            },
            error: function() {
                $('.diagnostics-loading').hide();
                alert('Server error while running diagnostics.');
            }
        });
    });
    
    // Clear all cache
    $('#clear-all-cache').on('click', function() {
        if (confirm('Are you sure you want to clear all WP Equipment cache?')) {
            $(this).prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_equipment_clear_all_cache',
                    nonce: wpEquipmentData.nonce
                },
                success: function(response) {
                    $('#clear-all-cache').prop('disabled', false).text('Clear All Cache');
                    
                    if (response.success) {
                        alert('All cache cleared successfully!');
                    } else {
                        alert('Error clearing cache: ' + response.data.message);
                    }
                },
                error: function() {
                    $('#clear-all-cache').prop('disabled', false).text('Clear All Cache');
                    alert('Server error while clearing cache.');
                }
            });
        }
    });
    
    // Run diagnostics on page load
    $('#run-cache-diagnostics').trigger('click');
});

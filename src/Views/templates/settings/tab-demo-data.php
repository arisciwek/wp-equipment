<?php
/**
 * Demo Data Generator Tab Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/settings/tab-demo-data.php
 */

if (!defined('ABSPATH')) {
    die;
}

// Verify nonce and capabilities
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
?>

<div class="wrap">
    <div id="demo-data-messages"></div>

    <div class="demo-data-section">
        <h3><?php _e('Generate Demo Data', 'wp-equipment'); ?></h3>
        <p class="description">
            <?php _e('Generate demo data for testing purposes.', 'wp-equipment'); ?>
        </p>

        <div class="demo-data-grid">
            <!-- Services -->
            <div class="demo-data-card">
                <h4><?php _e('Services', 'wp-equipment'); ?></h4>
                <p><?php _e('Generate sample service data as master data for groups.', 'wp-equipment'); ?></p>
                <button type="button" 
                        class="generate-demo-data" 
                        data-type="service"
                        data-nonce="<?php echo wp_create_nonce('generate_demo_service'); ?>">
                    <?php _e('Generate Services', 'wp-equipment'); ?>
                </button>
            </div>

            
            <!-- Groups (Dependent on Services) -->
            <div class="demo-data-card">
                <h4><?php _e('Groups', 'wp-equipment'); ?></h4>
                <p><?php _e('Generate sample group data. Requires Services to be generated first.', 'wp-equipment'); ?></p>
                <button type="button" 
                        class="generate-demo-data" 
                        data-type="group"
                        data-dependency="service"
                        data-nonce="<?php echo wp_create_nonce('generate_demo_group'); ?>">
                    <?php _e('Generate Groups', 'wp-equipment'); ?>
                </button>
            </div>

            <!-- Categories -->
            <div class="demo-data-card">
                <h4><?php _e('Categories', 'wp-equipment'); ?></h4>
                <p><?php _e('Generate sample category data with hierarchical structure.', 'wp-equipment'); ?></p>
					<button type="button" 
					        class="generate-demo-data" 
					        data-type="category"
                            data-dependency="group"
					        data-nonce="<?php echo wp_create_nonce('generate_demo_category'); ?>">
					    <?php _e('Generate Categories', 'wp-equipment'); ?>
					</button>
            </div>
        </div>
    </div>

    <div class="development-settings-section" style="margin-top: 30px;">
        <h3><?php _e('Development Settings', 'wp-equipment'); ?></h3>
        <form method="post" action="options.php">
            <?php 
            settings_fields('wp_equipment_development_settings');
            $dev_settings = get_option('wp_equipment_development_settings', array(
                'enable_development' => 0,
                'clear_data_on_deactivate' => 0
            ));
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php _e('Development Mode', 'wp-equipment'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="wp_equipment_development_settings[enable_development]" 
                                   value="1" 
                                   <?php checked($dev_settings['enable_development'], 1); ?>>
                            <?php _e('Enable development mode', 'wp-equipment'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, this overrides WP_EQUIPMENT_DEVELOPMENT constant.', 'wp-equipment'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Data Cleanup', 'wp-equipment'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="wp_equipment_development_settings[clear_data_on_deactivate]" 
                                   value="1" 
                                   <?php checked($dev_settings['clear_data_on_deactivate'], 1); ?>>
                            <?php _e('Clear demo data on plugin deactivation', 'wp-equipment'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Warning: When enabled, all demo data will be permanently deleted when the plugin is deactivated.', 'wp-equipment'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
</div>

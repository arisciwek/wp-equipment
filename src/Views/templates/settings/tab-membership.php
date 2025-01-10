<?php
/**
 * Membership Levels Tab Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/settings/tab-membership.php
 *
 * Description: Template untuk mengelola membership levels
 *              Menampilkan dan mengelola level keanggotaan equipment
 *              Includes form untuk edit dan tambah level baru
 *
 * Changelog:
 * 1.0.0 - 2024-01-10
 * - Initial version
 * - Added membership levels table
 * - Added management form
 */

if (!defined('ABSPATH')) {
    die;
}

$options = get_option('wp_equipment_membership_settings', array());

// Define membership levels structure
$membership_levels = array(
    'regular' => array(
        'title' => __('Level Regular', 'wp-equipment'),
        'default_staff' => 2,
        'max_staff' => $options['regular_max_staff'] ?? 2,
        'capabilities' => $options['regular_capabilities'] ?? array()
    ),
    'priority' => array(
        'title' => __('Level Priority', 'wp-equipment'),
        'default_staff' => 5,
        'max_staff' => $options['priority_max_staff'] ?? 5,
        'capabilities' => $options['priority_capabilities'] ?? array()
    ),
    'utama' => array(
        'title' => __('Level Utama', 'wp-equipment'),
        'default_staff' => -1,
        'max_staff' => $options['utama_max_staff'] ?? -1,
        'capabilities' => $options['utama_capabilities'] ?? array()
    )
);

// Available capabilities
$available_caps = array(
    'can_add_staff' => __('Dapat menambah staff', 'wp-equipment'),
    'can_export' => __('Dapat export data', 'wp-equipment'), 
    'can_bulk_import' => __('Dapat bulk import', 'wp-equipment')
);

?>


<form method="post" action="options.php">
    <?php settings_fields('wp_equipment_membership_settings'); ?>

    <div class="membership-grid">
        <?php foreach ($membership_levels as $level_key => $level): ?>
            <div class="membership-card">
                <h3><?php echo esc_html($level['title']); ?></h3>
                
                <!-- Staff Limit Section -->
                <div class="membership-section">
                    <h4><?php _e('Batas Staff', 'wp-equipment'); ?></h4>
                    <div class="staff-limit">
                        <input type="number" 
                               name="wp_equipment_membership_settings[<?php echo esc_attr($level_key); ?>_max_staff]" 
                               value="<?php echo esc_attr($level['max_staff']); ?>"
                               min="-1"
                               class="small-text">
                        <p class="description"><?php _e('-1 untuk unlimited', 'wp-equipment'); ?></p>
                    </div>
                </div>

                <!-- Capabilities Section -->
                <div class="membership-section">
                    <h4><?php _e('Capabilities', 'wp-equipment'); ?></h4>
                    <div class="capabilities-list">
                        <?php foreach ($available_caps as $cap_key => $cap_label): ?>
                            <label>
                                <input type="checkbox" 
                                       name="wp_equipment_membership_settings[<?php echo esc_attr($level_key); ?>_capabilities][<?php echo esc_attr($cap_key); ?>]" 
                                       value="1"
                                       <?php checked(isset($level['capabilities'][$cap_key]) ? $level['capabilities'][$cap_key] : false); ?>>
                                <span><?php echo esc_html($cap_label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php submit_button(__('Simpan Perubahan', 'wp-equipment')); ?>
</form>

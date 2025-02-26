<?php
/**
 * Permission Management Tab Template with Nested Tabs
 *
 * @package     WP_Equipment
 * @subpackage  Views/Settings
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/settings/tab-permissions.php
 *
 * Description: Template untuk mengelola hak akses plugin WP Equipment
 *              Menampilkan matrix permission untuk setiap role
 *              Menggunakan nested tabs untuk kategori permissions
 *
 * Changelog:
 * v1.1.0 - 2025-02-26
 * - Added nested tabs for permission categories
 * - Reorganized UI for better user experience
 * - Added AJAX handling for tab switching
 * 
 * v1.0.0 - 2024-01-07
 * - Initial version
 * - Add permission matrix
 * - Add role management
 * - Add tooltips for permissions
 */

if (!defined('ABSPATH')) {
    die;
}

/**
 * Get description for each capability
 * 
 * @param string $capability The capability to get description for
 * @return string The capability description
 */
function get_capability_description($capability) {
    $descriptions = array(
        // Equipment capabilities
        'view_equipment_list' => __('Memungkinkan melihat daftar semua equipment dalam format tabel', 'wp-equipment'),
        'view_equipment_detail' => __('Memungkinkan melihat detail informasi equipment', 'wp-equipment'),
        'view_own_equipment' => __('Memungkinkan melihat equipment yang ditugaskan ke pengguna', 'wp-equipment'),
        'add_equipment' => __('Memungkinkan menambahkan data equipment baru', 'wp-equipment'),
        'edit_all_equipments' => __('Memungkinkan mengedit semua data equipment', 'wp-equipment'),
        'edit_own_equipment' => __('Memungkinkan mengedit hanya equipment yang ditugaskan', 'wp-equipment'),
        'delete_equipment' => __('Memungkinkan menghapus data equipment', 'wp-equipment'),
        
        // Licence capabilities
        'view_licence_list' => __('Memungkinkan melihat daftar semua surat keterangan', 'wp-equipment'),
        'view_licence_detail' => __('Memungkinkan melihat detail surat keterangan', 'wp-equipment'),
        'view_own_licence' => __('Memungkinkan melihat surat keterangan yang ditugaskan', 'wp-equipment'),
        'add_licence' => __('Memungkinkan menambahkan data surat keterangan baru', 'wp-equipment'),
        'edit_all_licencees' => __('Memungkinkan mengedit semua data surat keterangan', 'wp-equipment'),
        'edit_own_licence' => __('Memungkinkan mengedit hanya surat keterangan yang ditugaskan', 'wp-equipment'),
        'delete_licence' => __('Memungkinkan menghapus data surat keterangan', 'wp-equipment'),
        
        // Configuration capabilities - Categories
        'view_categories' => __('Memungkinkan melihat daftar kategori', 'wp-equipment'),
        'add_category' => __('Memungkinkan menambahkan kategori baru', 'wp-equipment'),
        'edit_category' => __('Memungkinkan mengedit kategori', 'wp-equipment'),
        'delete_category' => __('Memungkinkan menghapus kategori', 'wp-equipment'),
        
        // Configuration capabilities - Groups
        'view_groups' => __('Memungkinkan melihat daftar grup', 'wp-equipment'),
        'add_group' => __('Memungkinkan menambahkan grup baru', 'wp-equipment'),
        'edit_group' => __('Memungkinkan mengedit grup', 'wp-equipment'),
        'delete_group' => __('Memungkinkan menghapus grup', 'wp-equipment'),
        
        // Configuration capabilities - Services
        'view_services' => __('Memungkinkan melihat daftar layanan', 'wp-equipment'),
        'add_service' => __('Memungkinkan menambahkan layanan baru', 'wp-equipment'),
        'edit_service' => __('Memungkinkan mengedit layanan', 'wp-equipment'),
        'delete_service' => __('Memungkinkan menghapus layanan', 'wp-equipment')
    );

    return isset($descriptions[$capability]) ? $descriptions[$capability] : '';
}

// Get permission model instance
$permission_model = new \WPEquipment\Models\Settings\PermissionModel();

// Define permission categories
$permission_categories = array(
    'equipment' => array(
        'title' => __('Equipment', 'wp-equipment'),
        'capabilities' => array(
            'view_equipment_list',
            'view_equipment_detail',
            'view_own_equipment',
            'add_equipment',
            'edit_all_equipments',
            'edit_own_equipment',
            'delete_equipment'
        )
    ),
    'licence' => array(
        'title' => __('Licence', 'wp-equipment'),
        'capabilities' => array(
            'view_licence_list',
            'view_licence_detail',
            'view_own_licence',
            'add_licence',
            'edit_all_licencees',
            'edit_own_licence',
            'delete_licence'
        )
    ),
    'configuration' => array(
        'title' => __('Konfigurasi', 'wp-equipment'),
        'capabilities' => array(
            'view_categories',
            'add_category',
            'edit_category',
            'delete_category',
            'view_groups',
            'add_group',
            'edit_group',
            'delete_group',
            'view_services',
            'add_service',
            'edit_service',
            'delete_service'
        )
    )
);

// Get all capabilities
$permission_labels = $permission_model->getAllCapabilities();

// Get all editable roles
$all_roles = get_editable_roles();

// Get current permission subtab
$current_subtab = isset($_GET['permission_subtab']) ? sanitize_key($_GET['permission_subtab']) : 'equipment';

?>

<div class="wrap">
    <?php settings_errors('wp_equipment_messages'); ?>

    <?php wp_nonce_field('wp_equipment_permissions_nonce', '_wpnonce'); ?>
    <input type="hidden" name="current_subtab" value="<?php echo esc_attr(isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'default'); ?>">
    
    <!-- Tab Navigation -->
    <h2 class="nav-tab-wrapper">
        <?php foreach ($permission_categories as $subtab_key => $category): ?>
            <a href="<?php echo add_query_arg(['tab' => 'permissions', 'permission_subtab' => $subtab_key]); ?>" 
               class="nav-tab <?php echo $current_subtab === $subtab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($category['title']); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <div class="permissions-section">
        <!-- Reset Permissions Section -->
        <div class="reset-permissions-section">
            <form id="reset-permissions-form" method="post">
                <?php wp_nonce_field('wp_equipment_reset_permissions', 'reset_permissions_nonce'); ?>
                <button type="button" id="reset-permissions-btn" class="button button-secondary">
                    <i class="dashicons dashicons-image-rotate"></i>
                    <?php _e('Reset to Default', 'wp-equipment'); ?>
                </button>
            </form>
            <p class="description">
                <?php _e('Reset permissions to plugin defaults. This will restore the original capability settings for all roles.', 'wp-equipment'); ?>
            </p>
        </div>

        <form id="wp-equipment-permissions-form" method="post" action="<?php echo add_query_arg(['tab' => 'permissions', 'permission_subtab' => $current_subtab]); ?>">
            <?php wp_nonce_field('wp_equipment_permissions'); ?>
            <input type="hidden" name="action" value="update_role_permissions">
            <input type="hidden" name="current_subtab" value="<?php echo esc_attr($current_subtab); ?>">

            <p class="description">
                <?php printf(__('Konfigurasikan hak akses %s untuk setiap role. Administrator secara otomatis memiliki akses penuh.', 'wp-equipment'), strtolower($permission_categories[$current_subtab]['title'])); ?>
            </p>

            <table class="widefat fixed permissions-matrix">
                <thead>
                    <tr>
                        <th class="column-role"><?php _e('Role', 'wp-equipment'); ?></th>
                        <?php foreach ($permission_categories[$current_subtab]['capabilities'] as $cap): ?>
                            <?php if (!isset($permission_labels[$cap])) continue; ?>
                            <th class="column-permission">
                                <?php echo esc_html($permission_labels[$cap]); ?>
                                <span class="dashicons dashicons-info tooltip-icon" 
                                      title="<?php echo esc_attr(get_capability_description($cap)); ?>">
                                </span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_roles as $role_name => $role_info):
                        if ($role_name === 'administrator') continue;
                        $role = get_role($role_name);
                    ?>
                        <tr>
                            <td class="column-role">
                                <strong><?php echo translate_user_role($role_info['name']); ?></strong>
                            </td>
                            <?php foreach ($permission_categories[$current_subtab]['capabilities'] as $cap): ?>
                                <?php if (!isset($permission_labels[$cap])) continue; ?>
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap); ?>]" 
                                           value="1"
                                           <?php checked($role->has_cap($cap)); ?>>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button(__('Simpan Perubahan', 'wp-equipment'), 'primary', 'save-permissions'); ?>
        </form>
    </div>

    <div class="role-descriptions">
        <h3><?php _e('Gambaran Role Default', 'wp-equipment'); ?></h3>
        <dl>
            <dt><?php _e('Administrator', 'wp-equipment'); ?></dt>
            <dd><?php _e('Memiliki akses penuh ke semua fitur dan pengaturan.', 'wp-equipment'); ?></dd>
            <!-- Deskripsi role lainnya -->
        </dl>
    </div>
</div>

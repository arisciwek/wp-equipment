<?php
/**
 * Permission Management Tab Template
 *
 * @package     WP_Equipment
 * @subpackage  Views/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Views/templates/settings/tab-permissions.php
 *
 * Description: Template untuk mengelola hak akses plugin WP Equipment
 *              Menampilkan matrix permission untuk setiap role
 *
 * Changelog:
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
        
        // Branch capabilities
        'view_licence_list' => __('Memungkinkan melihat daftar semua surat keterangan', 'wp-equipment'),
        'view_licence_detail' => __('Memungkinkan melihat detail surat keterangan', 'wp-equipment'),
        'view_own_licence' => __('Memungkinkan melihat surat keterangan yang ditugaskan', 'wp-equipment'),
        'add_licence' => __('Memungkinkan menambahkan data surat keterangan baru', 'wp-equipment'),
        'edit_all_licencees' => __('Memungkinkan mengedit semua data surat keterangan', 'wp-equipment'),
        'edit_own_licence' => __('Memungkinkan mengedit hanya surat keterangan yang ditugaskan', 'wp-equipment'),
        'delete_licence' => __('Memungkinkan menghapus data surat keterangan', 'wp-equipment')
    );

    return isset($descriptions[$capability]) ? $descriptions[$capability] : '';
}

// Get permission model instance
$permission_model = new \WPEquipment\Models\Settings\PermissionModel();

// Get all capabilities
$permission_labels = $permission_model->getAllCapabilities();

// Get all editable roles
$all_roles = get_editable_roles();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role_permissions') {
    if (!check_admin_referer('wp_equipment_permissions')) {
        wp_die(__('Token keamanan tidak valid.', 'wp-equipment'));
    }

    $updated = false;
    foreach ($all_roles as $role_name => $role_info) {
        // Skip administrator as they have full access
        if ($role_name === 'administrator') {
            continue;
        }

        $role = get_role($role_name);
        if ($role) {
            foreach ($permission_labels as $cap => $label) {
                $has_cap = isset($_POST['permissions'][$role_name][$cap]);
                if ($role->has_cap($cap) !== $has_cap) {
                    if ($has_cap) {
                        $role->add_cap($cap);
                    } else {
                        $role->remove_cap($cap);
                    }
                    $updated = true;
                }
            }
        }
    }

    if ($updated) {
        add_settings_error(
            'wp_equipment_messages', 
            'permissions_updated', 
            __('Hak akses role berhasil diperbarui.', 'wp-equipment'), 
            'success'
        );
    }
}
?>

<div class="permissions-section">
    <form method="post" action="<?php echo add_query_arg('tab', 'permissions'); ?>">
        <?php wp_nonce_field('wp_equipment_permissions'); ?>
        <input type="hidden" name="action" value="update_role_permissions">

        <p class="description">
            <?php _e('Konfigurasikan hak akses untuk setiap role dalam mengelola data equipments. Administrator secara otomatis memiliki akses penuh.', 'wp-equipment'); ?>
        </p>

        <table class="widefat fixed permissions-matrix">
            <thead>
                <tr>
                    <th class="column-role"><?php _e('Role', 'wp-equipment'); ?></th>
                    <?php foreach ($permission_labels as $cap => $label): ?>
                        <th class="column-permission">
                            <?php echo esc_html($label); ?>
                            <span class="dashicons dashicons-info tooltip-icon" 
                                  title="<?php echo esc_attr(get_capability_description($cap)); ?>">
                            </span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($all_roles as $role_name => $role_info):
                    // Skip administrator
                    if ($role_name === 'administrator') continue;
                    
                    $role = get_role($role_name);
                ?>
                    <tr>
                        <td class="column-role">
                            <strong><?php echo translate_user_role($role_info['name']); ?></strong>
                        </td>
                        <?php foreach ($permission_labels as $cap => $label): ?>
                            <td class="column-permission">
                                <label class="screen-reader-text">
                                    <?php echo esc_html(sprintf(
                                        /* translators: 1: permission name, 2: role name */
                                        __('%1$s untuk role %2$s', 'wp-equipment'),
                                        $label,
                                        $role_info['name']
                                    )); ?>
                                </label>
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

        <?php submit_button(__('Simpan Perubahan', 'wp-equipment')); ?>
    </form>

    <div class="role-descriptions">
        <h3><?php _e('Gambaran Role Default', 'wp-equipment'); ?></h3>
        <dl>
            <dt><?php _e('Administrator', 'wp-equipment'); ?></dt>
            <dd><?php _e('Memiliki akses penuh ke semua fitur dan pengaturan.', 'wp-equipment'); ?></dd>
            
            <dt><?php _e('Editor', 'wp-equipment'); ?></dt>
            <dd><?php _e('Dapat melihat dan mengedit data equipment dan surat keterangan yang ditugaskan.', 'wp-equipment'); ?></dd>
            
            <dt><?php _e('Author', 'wp-equipment'); ?></dt>
            <dd><?php _e('Dapat melihat data equipment dan surat keterangan yang ditugaskan.', 'wp-equipment'); ?></dd>
            
            <dt><?php _e('Contributor', 'wp-equipment'); ?></dt>
            <dd><?php _e('Hanya dapat melihat data equipments yang ditugaskan.', 'wp-equipment'); ?></dd>
        </dl>
    </div>
</div>


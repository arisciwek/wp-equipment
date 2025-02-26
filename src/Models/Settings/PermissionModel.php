<?php
/**
 * Permission Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models/Settings
 * @version     1.2.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/Settings/PermissionModel.php
 *
 * Description: Model untuk mengelola hak akses plugin
 *
 * Changelog:
 * 1.2.0 - 2025-02-26
 * - Added getCapabilityGroups method for nested tab support
 * - Restructured capabilities by category
 * - Improved documentation
 * 
 * 1.1.0 - 2024-12-08
 * - Added view_own_equipment capability
 * - Updated default role capabilities for editor and author roles
 * - Added documentation for view_own_equipment permission
 *
 * 1.0.0 - 2024-11-28
 * - Initial release
 * - Basic permission management
 * - Default capabilities setup
 */
namespace WPEquipment\Models\Settings;

class PermissionModel {
    /**
     * Default capabilities with display labels
     */
    private $default_capabilities = [
        // Equipment capabilities
        'view_equipment_list' => 'Lihat Daftar Equipment',
        'view_equipment_detail' => 'Lihat Detail Equipment',
        'view_own_equipment' => 'Lihat Equipment Sendiri',
        'add_equipment' => 'Tambah Peralatan',
        'edit_all_equipments' => 'Edit Semua Equipment',
        'edit_own_equipment' => 'Edit Peralatan Sendiri',
        'delete_equipment' => 'Hapus Peralatan',

        // Licence capabilities
        'view_licence_list' => 'Lihat Daftar Surat Keterangan',
        'view_licence_detail' => 'Lihat Detail Surat Keterangan',
        'view_own_licence' => 'Lihat Surat Keterangan Sendiri',
        'add_licence' => 'Tambah Surat Keterangan',
        'edit_all_licencees' => 'Edit Semua Surat Keterangan',
        'edit_own_licence' => 'Edit Surat Keterangan Sendiri',
        'delete_licence' => 'Hapus Surat Keterangan',

        // Configuration capabilities
        'view_categories' => 'Lihat Kategori',
        'add_category' => 'Tambah Kategori',
        'edit_category' => 'Edit Kategori',
        'delete_category' => 'Hapus Kategori',

        'view_groups' => 'Lihat Grup',
        'add_group' => 'Tambah Grup',
        'edit_group' => 'Edit Grup',
        'delete_group' => 'Hapus Grup',

        'view_services' => 'Lihat Layanan',
        'add_service' => 'Tambah Layanan',
        'edit_service' => 'Edit Layanan',
        'delete_service' => 'Hapus Layanan'        
    ];

    /**
     * Default role capabilities by role
     */
    private $default_role_caps = [
        'editor' => [
            'view_equipment_list',
            'view_equipment_detail',
            'view_own_equipment',
            'edit_own_equipment',
            'view_licence_list',
            'view_licence_detail',
            'view_own_licence',
            'edit_own_licence',
            'view_categories',
            'view_groups',
            'view_services'
        ],
        'author' => [
            'view_equipment_list',
            'view_equipment_detail',
            'view_own_equipment',
            'view_licence_list',
            'view_licence_detail',
            'view_own_licence',
            'view_categories',
            'view_groups',
            'view_services'
        ],
        'contributor' => [
            'view_own_equipment',
            'view_own_licence'
        ]
    ];

    /**
     * Update matrix permission berdasarkan data yang diberikan
     * 
     * @param array $permissions Data permissions yang sudah disanitasi
     * @return bool True jika berhasil, false jika gagal
     */
    public function updatePermissions($permissions) {
        try {
            // Dapatkan semua WordPress roles
            $wp_roles = wp_roles();
            
            // Update capability untuk setiap role
            foreach ($permissions as $role_id => $capabilities) {
                // Pastikan role valid
                if (!$wp_roles->is_role($role_id)) {
                    error_log("WP Equipment: Role tidak valid - " . $role_id);
                    continue;
                }
                
                $role = $wp_roles->get_role($role_id);
                
                // Update setiap capability
                foreach ($capabilities as $cap_name => $enabled) {
                    if ($enabled) {
                        $role->add_cap($cap_name);
                    } else {
                        $role->remove_cap($cap_name);
                    }
                }
            }
            
            // Update versi permission untuk tracking
            update_option('wp_equipment_perm_version', WP_EQUIPMENT_VERSION);
            
            // Hapus cache jika ada
            if (method_exists($this, 'clearPermissionCache')) {
                $this->clearPermissionCache();
            }
            
            return true;
        } catch (\Exception $e) {
            error_log('WP Equipment: Error saat update permissions - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Capability groups for organizing permissions
     */
    private $capability_groups = [
        'equipment' => [
            'title' => 'Equipment',
            'caps' => [
                'view_equipment_list',
                'view_equipment_detail',
                'view_own_equipment',
                'add_equipment',
                'edit_all_equipments',
                'edit_own_equipment',
                'delete_equipment'
            ]
        ],
        'licence' => [
            'title' => 'Surat Keterangan',
            'caps' => [
                'view_licence_list',
                'view_licence_detail',
                'view_own_licence',
                'add_licence',
                'edit_all_licencees',
                'edit_own_licence',
                'delete_licence'
            ]
        ],
        'configuration' => [
            'title' => 'Konfigurasi',
            'caps' => [
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
            ]
        ]
    ];

    /**
     * Get all capabilities with labels
     * 
     * @return array Capabilities with labels
     */
    public function getAllCapabilities(): array {
        return $this->default_capabilities;
    }

    /**
     * Get capability groups for tab organization
     * 
     * @return array Capability groups
     */
    public function getCapabilityGroups(): array {
        return $this->capability_groups;
    }

    /**
     * Check if a role has a specific capability
     * 
     * @param string $role_name Role name
     * @param string $capability Capability name
     * @return bool True if role has capability
     */
    public function roleHasCapability(string $role_name, string $capability): bool {
        $role = get_role($role_name);
        if (!$role) {
            error_log("Role not found: $role_name");
            return false;
        }

        $has_cap = $role->has_cap($capability);
        return $has_cap;
    }

    /**
     * Update capabilities for a role
     * 
     * @param string $role_name Role name
     * @param array $capabilities Capabilities to set
     * @return bool Success status
     */
    public function updateRoleCapabilities(string $role_name, array $capabilities): bool {
        if ($role_name === 'administrator') {
            return false;
        }

        $role = get_role($role_name);
        if (!$role) {
            return false;
        }

        // Reset existing capabilities
        foreach (array_keys($this->default_capabilities) as $cap) {
            $role->remove_cap($cap);
        }

        // Add new capabilities
        foreach (array_keys($this->default_capabilities) as $cap) {
            if (isset($capabilities[$cap]) && $capabilities[$cap]) {
                $role->add_cap($cap);
            }
        }

        return true;
    }

    /**
     * Initialize capabilities for all roles
     */
    public function addCapabilities(): void {
        // Set administrator capabilities
        $admin = get_role('administrator');
        if ($admin) {
            foreach (array_keys($this->default_capabilities) as $cap) {
                $admin->add_cap($cap);
            }
        }

        // Set default role capabilities
        foreach ($this->default_role_caps as $role_name => $caps) {
            $role = get_role($role_name);
            if ($role) {
                // Reset capabilities first
                foreach (array_keys($this->default_capabilities) as $cap) {
                    $role->remove_cap($cap);
                }
                // Add default capabilities
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Reset all roles to default capabilities
     * 
     * @return bool Success status
     */
    public function resetToDefault(): bool {
        try {
            // Reset semua role ke default
            foreach (get_editable_roles() as $role_name => $role_info) {
                $role = get_role($role_name);
                if (!$role) continue;
    
                // Hapus semua existing capabilities
                foreach (array_keys($this->default_capabilities) as $cap) {
                    $role->remove_cap($cap);
                }
    
                // Untuk administrator, berikan semua capabilities
                if ($role_name === 'administrator') {
                    foreach (array_keys($this->default_capabilities) as $cap) {
                        $role->add_cap($cap);
                    }
                    continue;
                }
    
                // Untuk role lain, berikan default capabilities jika didefinisikan
                if (isset($this->default_role_caps[$role_name])) {
                    foreach ($this->default_role_caps[$role_name] as $cap) {
                        $role->add_cap($cap);
                    }
                }
            }
    
            // Update versi database untuk menandai bahwa capabilities sudah diupdate
            update_option('wp_equipment_perm_version', WP_EQUIPMENT_VERSION);
            
            return true;
    
        } catch (\Exception $e) {
            error_log('Error resetting permissions: ' . $e->getMessage());
            return false;
        }
    }
}

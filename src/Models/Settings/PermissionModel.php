<?php
/**
 * Permission Model Class
 *
 * @package     WP_Equipment
 * @subpackage  Models/Settings
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Models/Settings/PermissionModel.php
 *
 * Description: Model untuk mengelola hak akses plugin
 *
 * Changelog:
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
    private $default_capabilities = [
        // Equipment capabilities
        'view_equipment_list' => 'Lihat Daftar Equipment',
        'view_equipment_detail' => 'Lihat Detail Equipment',
        'view_own_equipment' => 'Lihat Equipment Sendiri',
        'add_equipment' => 'Tambah Peralatan',
        'edit_all_equipments' => 'Edit Semua Equipment',
        'edit_own_equipment' => 'Edit Peralatan Sendiri',
        'delete_equipment' => 'Hapus Peralatan',

        // Branch capabilities
        'view_licence_list' => 'Lihat Daftar Surat Keterangan',
        'view_licence_detail' => 'Lihat Detail Surat Keterangan',
        'view_own_licence' => 'Lihat Surat Keterangan Sendiri',
        'add_licence' => 'Tambah Surat Keterangan',
        'edit_all_licencees' => 'Edit Semua Surat Keterangan',
        'edit_own_licence' => 'Edit Surat Keterangan Sendiri',
        'delete_licence' => 'Hapus Surat Keterangan'
    ];

    private $default_role_caps = [
        'editor' => [
            'view_equipment_list',
            'view_equipment_detail',
            'view_own_equipment',
            'edit_own_equipment',
            'view_licence_list',
            'view_licence_detail',
            'view_own_licence',
            'edit_own_licence'
        ],
        'author' => [
            'view_equipment_list',
            'view_equipment_detail',
            'view_own_equipment',
            'view_licence_list',
            'view_licence_detail',
            'view_own_licence'
        ],
        'contributor' => [
            'view_own_equipment',
            'view_own_licence'
        ]
    ];

    public function getAllCapabilities(): array {
        return $this->default_capabilities;
    }

    public function roleHasCapability(string $role_name, string $capability): bool {
        $role = get_role($role_name);
        if (!$role) {
            error_log("Role not found: $role_name");
            return false;
        }

        $has_cap = $role->has_cap($capability);
        return $has_cap;
    }

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
        foreach ($this->default_capabilities as $cap => $label) {
            if (isset($capabilities[$cap]) && $capabilities[$cap]) {
                $role->add_cap($cap);
            }
        }

        return true;
    }

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

    public function resetToDefault(): bool {
    try {
        // Reset semua role ke default
        foreach (get_editable_roles() as $role_name => $role_info) {
            $role = get_role($role_name);
            if (!$role) continue;

            // Hapus semua capability yang ada
            foreach (array_keys($this->default_capabilities) as $cap) {
                $role->remove_cap($cap);
            }

            // Jika administrator, berikan semua capability
            if ($role_name === 'administrator') {
                foreach (array_keys($this->default_capabilities) as $cap) {
                    $role->add_cap($cap);
                }
                continue;
            }

            // Untuk role lain, berikan sesuai default jika ada
            if (isset($this->default_role_caps[$role_name])) {
                foreach ($this->default_role_caps[$role_name] as $cap) {
                    $role->add_cap($cap);
                }
            }
        }

        return true;

    } catch (\Exception $e) {
        error_log('Error resetting permissions: ' . $e->getMessage());
        return false;
    }
}
}

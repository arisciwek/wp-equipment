<?php
/**
 * Equipment Users Data
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo/Data
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/Data/EquipmentUsersData.php
 *
 * Description: Static equipment user data for demo generation.
 *              Used by WPUserGenerator and EquipmentDemoData.
 */

namespace WPEquipment\Database\Demo\Data;

defined('ABSPATH') || exit;

class EquipmentUsersData {
    public static $data = [
        ['id' => 2, 'username' => 'budi_santoso', 'display_name' => 'Budi Santoso', 'role' => 'equipment'],
        ['id' => 3, 'username' => 'dewi_kartika', 'display_name' => 'Dewi Kartika', 'role' => 'equipment'],
        ['id' => 4, 'username' => 'ahmad_hidayat', 'display_name' => 'Ahmad Hidayat', 'role' => 'equipment'],
        ['id' => 5, 'username' => 'siti_rahayu', 'display_name' => 'Siti Rahayu', 'role' => 'equipment'],
        ['id' => 6, 'username' => 'rudi_hermawan', 'display_name' => 'Rudi Hermawan', 'role' => 'equipment'],
        ['id' => 7, 'username' => 'nina_kusuma', 'display_name' => 'Nina Kusuma', 'role' => 'equipment'],
        ['id' => 8, 'username' => 'eko_prasetyo', 'display_name' => 'Eko Prasetyo', 'role' => 'equipment'],
        ['id' => 9, 'username' => 'maya_wijaya', 'display_name' => 'Maya Wijaya', 'role' => 'equipment'],
        ['id' => 10, 'username' => 'dian_pertiwi', 'display_name' => 'Dian Pertiwi', 'role' => 'equipment'],
        ['id' => 11, 'username' => 'agus_suryanto', 'display_name' => 'Agus Suryanto', 'role' => 'equipment']
    ];
}

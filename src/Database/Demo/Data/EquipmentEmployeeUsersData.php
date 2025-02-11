<?php
/**
 * Employee Users Data
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo/Data
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/Data/EquipmentEmployeeUsersData.php
 *
 * Description: Static employee user data for demo generation.
 *              Used by WPUserGenerator and EquipmentEmployeeDemoData.
 *              60 users total (2 per licence × 30 licencees)
 *              User IDs: 42-101
 */

namespace WPEquipment\Database\Demo\Data;

defined('ABSPATH') || exit;

class EquipmentEmployeeUsersData {
    // Constants for user ID ranges
    const USER_ID_START = 42;
    const USER_ID_END = 101;

    public static $data = [
        // Equipment 1 (PT Maju Bersama) - Licence 1 (Pusat)
        42 => [
            'id' => 42,
            'equipment_id' => 1,
            'licence_id' => 1,
            'username' => 'finance_maju1',
            'display_name' => 'Aditya Pratama',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        43 => [
            'id' => 43,
            'equipment_id' => 1,
            'licence_id' => 1,
            'username' => 'legal_maju1',
            'display_name' => 'Sarah Wijaya',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 1 (PT Maju Bersama) - Licence 2 (Cabang 1)
        44 => [
            'id' => 44,
            'equipment_id' => 1,
            'licence_id' => 2,
            'username' => 'finance_maju2',
            'display_name' => 'Bima Setiawan',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        45 => [
            'id' => 45,
            'equipment_id' => 1,
            'licence_id' => 2,
            'username' => 'operation_maju2',
            'display_name' => 'Diana Puspita',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        // Equipment 1 (PT Maju Bersama) - Licence 3 (Cabang 2)
        46 => [
            'id' => 46,
            'equipment_id' => 1,
            'licence_id' => 3,
            'username' => 'operation_maju3',
            'display_name' => 'Eko Wibowo',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        47 => [
            'id' => 47,
            'equipment_id' => 1,
            'licence_id' => 3,
            'username' => 'finance_maju3',
            'display_name' => 'Fina Sari',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 2 (CV Teknologi Nusantara) - Licence 1 (Pusat)
        48 => [
            'id' => 48,
            'equipment_id' => 2,
            'licence_id' => 4,
            'username' => 'legal_tekno1',
            'display_name' => 'Gunawan Santoso',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        49 => [
            'id' => 49,
            'equipment_id' => 2,
            'licence_id' => 4,
            'username' => 'finance_tekno1',
            'display_name' => 'Hana Permata',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 2 (CV Teknologi Nusantara) - Licence 2 (Cabang 1)
        50 => [
            'id' => 50,
            'equipment_id' => 2,
            'licence_id' => 5,
            'username' => 'operation_tekno2',
            'display_name' => 'Irfan Hakim',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        51 => [
            'id' => 51,
            'equipment_id' => 2,
            'licence_id' => 5,
            'username' => 'purchase_tekno2',
            'display_name' => 'Julia Putri',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 2 (CV Teknologi Nusantara) - Licence 3 (Cabang 2)
        52 => [
            'id' => 52,
            'equipment_id' => 2,
            'licence_id' => 6,
            'username' => 'finance_tekno3',
            'display_name' => 'Krisna Wijaya',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        53 => [
            'id' => 53,
            'equipment_id' => 2,
            'licence_id' => 6,
            'username' => 'legal_tekno3',
            'display_name' => 'Luna Safitri',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 3 (PT Sinar Abadi) - Licence 1 (Pusat)
        54 => [
            'id' => 54,
            'equipment_id' => 3,
            'licence_id' => 7,
            'username' => 'operation_sinar1',
            'display_name' => 'Mario Gunawan',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        55 => [
            'id' => 55,
            'equipment_id' => 3,
            'licence_id' => 7,
            'username' => 'finance_sinar1',
            'display_name' => 'Nadia Kusuma',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 3 (PT Sinar Abadi) - Licence 2 (Cabang 1)
        56 => [
            'id' => 56,
            'equipment_id' => 3,
            'licence_id' => 8,
            'username' => 'legal_sinar2',
            'display_name' => 'Oscar Pradana',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        57 => [
            'id' => 57,
            'equipment_id' => 3,
            'licence_id' => 8,
            'username' => 'operation_sinar2',
            'display_name' => 'Putri Handayani',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 3 (PT Sinar Abadi) - Licence 3 (Cabang 2)
        58 => [
            'id' => 58,
            'equipment_id' => 3,
            'licence_id' => 9,
            'username' => 'finance_sinar3',
            'display_name' => 'Qori Rahman',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        59 => [
            'id' => 59,
            'equipment_id' => 3,
            'licence_id' => 9,
            'username' => 'legal_sinar3',
            'display_name' => 'Ratih Purnama',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        // Equipment 4 (PT Global Teknindo) - Licence 1 (Pusat)
        60 => [
            'id' => 60,
            'equipment_id' => 4,
            'licence_id' => 10,
            'username' => 'operation_global1',
            'display_name' => 'Surya Pratama',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        61 => [
            'id' => 61,
            'equipment_id' => 4,
            'licence_id' => 10,
            'username' => 'finance_global1',
            'display_name' => 'Tania Wijaya',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 6 (PT Karya Digital) - Licence 1 (Pusat)
        72 => [
            'id' => 72,
            'equipment_id' => 6,
            'licence_id' => 16,
            'username' => 'finance_karya1',
            'display_name' => 'Eko Santoso',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        73 => [
            'id' => 73,
            'equipment_id' => 6,
            'licence_id' => 16,
            'username' => 'legal_karya1',
            'display_name' => 'Fitri Wulandari',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 6 (PT Karya Digital) - Licence 2 (Cabang 1)
        74 => [
            'id' => 74,
            'equipment_id' => 6,
            'licence_id' => 17,
            'username' => 'operation_karya2',
            'display_name' => 'Galih Prasetyo',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        75 => [
            'id' => 75,
            'equipment_id' => 6,
            'licence_id' => 17,
            'username' => 'finance_karya2',
            'display_name' => 'Hesti Kusuma',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 6 (PT Karya Digital) - Licence 3 (Cabang 2)
        76 => [
            'id' => 76,
            'equipment_id' => 6,
            'licence_id' => 18,
            'username' => 'legal_karya3',
            'display_name' => 'Indra Wijaya',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        77 => [
            'id' => 77,
            'equipment_id' => 6,
            'licence_id' => 18,
            'username' => 'operation_karya3',
            'display_name' => 'Jasmine Putri',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 7 (PT Bumi Perkasa) - Licence 1 (Pusat)
        78 => [
            'id' => 78,
            'equipment_id' => 7,
            'licence_id' => 19,
            'username' => 'finance_bumi1',
            'display_name' => 'Kevin Sutanto',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        79 => [
            'id' => 79,
            'equipment_id' => 7,
            'licence_id' => 19,
            'username' => 'legal_bumi1',
            'display_name' => 'Lina Permata',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 7 (PT Bumi Perkasa) - Licence 2 (Cabang 1)
        80 => [
            'id' => 80,
            'equipment_id' => 7,
            'licence_id' => 20,
            'username' => 'operation_bumi2',
            'display_name' => 'Michael Wirawan',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        81 => [
            'id' => 81,
            'equipment_id' => 7,
            'licence_id' => 20,
            'username' => 'finance_bumi2',
            'display_name' => 'Nadira Sari',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 7 (PT Bumi Perkasa) - Licence 3 (Cabang 2)
        82 => [
            'id' => 82,
            'equipment_id' => 7,
            'licence_id' => 21,
            'username' => 'legal_bumi3',
            'display_name' => 'Oscar Putra',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        83 => [
            'id' => 83,
            'equipment_id' => 7,
            'licence_id' => 21,
            'username' => 'operation_bumi3',
            'display_name' => 'Patricia Dewi',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 8 (CV Cipta Kreasi) - Licence 1 (Pusat)
        84 => [
            'id' => 84,
            'equipment_id' => 8,
            'licence_id' => 22,
            'username' => 'finance_cipta1',
            'display_name' => 'Qori Susanto',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        85 => [
            'id' => 85,
            'equipment_id' => 8,
            'licence_id' => 22,
            'username' => 'legal_cipta1',
            'display_name' => 'Rahma Wati',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 8 (CV Cipta Kreasi) - Licence 2 (Cabang 1)
        86 => [
            'id' => 86,
            'equipment_id' => 8,
            'licence_id' => 23,
            'username' => 'operation_cipta2',
            'display_name' => 'Surya Darma',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        87 => [
            'id' => 87,
            'equipment_id' => 8,
            'licence_id' => 23,
            'username' => 'finance_cipta2',
            'display_name' => 'Tania Putri',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 8 (CV Cipta Kreasi) - Licence 3 (Cabang 2)
        88 => [
            'id' => 88,
            'equipment_id' => 8,
            'licence_id' => 24,
            'username' => 'legal_cipta3',
            'display_name' => 'Umar Prasetyo',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        89 => [
            'id' => 89,
            'equipment_id' => 8,
            'licence_id' => 24,
            'username' => 'operation_cipta3',
            'display_name' => 'Vina Kusuma',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 9 (PT Meta Inovasi) - Licence 1 (Pusat)
        90 => [
            'id' => 90,
            'equipment_id' => 9,
            'licence_id' => 25,
            'username' => 'finance_meta1',
            'display_name' => 'Wayan Sudiarta',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        91 => [
            'id' => 91,
            'equipment_id' => 9,
            'licence_id' => 25,
            'username' => 'legal_meta1',
            'display_name' => 'Xena Maharani',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 9 (PT Meta Inovasi) - Licence 2 (Cabang 1)
        92 => [
            'id' => 92,
            'equipment_id' => 9,
            'licence_id' => 26,
            'username' => 'operation_meta2',
            'display_name' => 'Yoga Pratama',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        93 => [
            'id' => 93,
            'equipment_id' => 9,
            'licence_id' => 26,
            'username' => 'finance_meta2',
            'display_name' => 'Zahra Permata',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 9 (PT Meta Inovasi) - Licence 3 (Cabang 2)
        94 => [
            'id' => 94,
            'equipment_id' => 9,
            'licence_id' => 27,
            'username' => 'legal_meta3',
            'display_name' => 'Adi Wijaya',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        95 => [
            'id' => 95,
            'equipment_id' => 9,
            'licence_id' => 27,
            'username' => 'operation_meta3',
            'display_name' => 'Bella Safina',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        // Equipment 10 (PT Delta Sistem) - Licence 1 (Pusat)
        96 => [
            'id' => 96,
            'equipment_id' => 10,
            'licence_id' => 28,
            'username' => 'finance_delta1',
            'display_name' => 'Candra Kusuma',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
        97 => [
            'id' => 97,
            'equipment_id' => 10,
            'licence_id' => 28,
            'username' => 'legal_delta1',
            'display_name' => 'Devi Puspita',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        // Equipment 10 (PT Delta Sistem) - Licence 2 (Cabang 1)
        98 => [
            'id' => 98,
            'equipment_id' => 10,
            'licence_id' => 29,
            'username' => 'operation_delta2',
            'display_name' => 'Eka Prasetya',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => true,
                'legal' => true,
                'purchase' => false
            ]
        ],
        99 => [
            'id' => 99,
            'equipment_id' => 10,
            'licence_id' => 29,
            'username' => 'finance_delta2',
            'display_name' => 'Farah Sari',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => false,
                'legal' => false,
                'purchase' => true
            ]
        ],
        // Equipment 10 (PT Delta Sistem) - Licence 3 (Cabang 2)
        100 => [
            'id' => 100,
            'equipment_id' => 10,
            'licence_id' => 30,
            'username' => 'legal_delta3',
            'display_name' => 'Galang Wicaksono',
            'role' => 'equipment',
            'departments' => [
                'finance' => false,
                'operation' => false,
                'legal' => true,
                'purchase' => true
            ]
        ],
        101 => [
            'id' => 101,
            'equipment_id' => 10,
            'licence_id' => 30,
            'username' => 'operation_delta3',
            'display_name' => 'Hana Pertiwi',
            'role' => 'equipment',
            'departments' => [
                'finance' => true,
                'operation' => true,
                'legal' => false,
                'purchase' => false
            ]
        ],
    ];
}

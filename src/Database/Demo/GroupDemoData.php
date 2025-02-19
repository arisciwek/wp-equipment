<?php
/**
 * Generator Data Demo untuk Grup
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/GroupDemoData.php
 */

namespace WPEquipment\Database\Demo;

defined('ABSPATH') || exit;

class GroupDemoData extends AbstractDemoData {
    private static $group_ids = [];
    protected $groups = [
        [
            'id' => 1,
            'service_id' => 1, // Manufaktur
            'nama' => 'Industri Logam dan Mesin',
            'keterangan' => 'Kelompok industri yang bergerak di bidang pengolahan logam dan pembuatan mesin',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 2,
            'service_id' => 1, // Manufaktur
            'nama' => 'Industri Elektronik',
            'keterangan' => 'Kelompok industri yang bergerak di bidang pembuatan perangkat elektronik',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 3,
            'service_id' => 2, // Konstruksi
            'nama' => 'Konstruksi Gedung',
            'keterangan' => 'Kelompok konstruksi yang fokus pada pembangunan gedung',
            'dokumen_type' => 'odt'
        ],
        [
            'id' => 4,
            'service_id' => 2, // Konstruksi
            'nama' => 'Konstruksi Jalan',
            'keterangan' => 'Kelompok konstruksi yang fokus pada pembangunan jalan dan jembatan',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 5,
            'service_id' => 3, // Transportasi
            'nama' => 'Transportasi Darat',
            'keterangan' => 'Kelompok transportasi yang melayani pengangkutan darat',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 6,
            'service_id' => 3, // Transportasi
            'nama' => 'Transportasi Laut',
            'keterangan' => 'Kelompok transportasi yang melayani pengangkutan laut',
            'dokumen_type' => 'odt'
        ],
        [
            'id' => 7,
            'service_id' => 4, // Pertambangan
            'nama' => 'Tambang Mineral',
            'keterangan' => 'Kelompok pertambangan mineral dan batuan',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 8,
            'service_id' => 4, // Pertambangan
            'nama' => 'Tambang Batubara',
            'keterangan' => 'Kelompok pertambangan batubara',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 9,
            'service_id' => 5, // Telekomunikasi
            'nama' => 'Telekomunikasi Seluler',
            'keterangan' => 'Kelompok telekomunikasi yang fokus pada layanan seluler',
            'dokumen_type' => 'odt'
        ],
        [
            'id' => 10,
            'service_id' => 5, // Telekomunikasi
            'nama' => 'Internet Service Provider',
            'keterangan' => 'Kelompok telekomunikasi yang fokus pada layanan internet',
            'dokumen_type' => 'docx'
        ]
    ];

    public function __construct() {
        parent::__construct();
        $this->debug('GroupDemoData dibuat dengan ' . count($this->groups) . ' grup');
    }

    /**
     * Validasi data sebelum generate
     */
    protected function validate(): bool {
        try {
            $this->debug('Mulai validasi data grup...');

            // 1. Cek ketersediaan data
            if (empty($this->groups)) {
                $this->debug('Error: Data grup kosong');
                return false;
            }

            // 2. Cek tabel
            $table = $this->wpdb->prefix . 'app_groups';
            $table_exists = $this->wpdb->get_var(
                $this->wpdb->prepare("SHOW TABLES LIKE %s", $table)
            );
            
            if (!$table_exists) {
                $this->debug("Error: Tabel $table tidak ditemukan");
                return false;
            }

            // 3. Cek nama unik per service_id
            $names_by_service = [];
            foreach ($this->groups as $group) {
                $key = $group['service_id'] . '-' . $group['nama'];
                if (isset($names_by_service[$key])) {
                    $this->debug('Error: Ditemukan nama grup duplikat dalam satu sektor');
                    return false;
                }
                $names_by_service[$key] = true;
            }

            // 4. Validasi service_id
            $service_table = $this->wpdb->prefix . 'app_services';
            foreach ($this->groups as $group) {
                $service_exists = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT EXISTS(SELECT 1 FROM $service_table WHERE id = %d)",
                    $group['service_id']
                ));
                
                if (!$service_exists) {
                    $this->debug("Error: service_id {$group['service_id']} tidak ditemukan");
                    return false;
                }
            }

            // 5. Validasi tipe dokumen
            $valid_doc_types = ['docx', 'odt'];
            foreach ($this->groups as $group) {
                if (!in_array($group['dokumen_type'], $valid_doc_types)) {
                    $this->debug("Error: Tipe dokumen {$group['dokumen_type']} tidak valid");
                    return false;
                }
            }

            $this->debug('Validasi data grup berhasil');
            return true;

        } catch (\Exception $e) {
            $this->debug('Error validasi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate data demo
     */
    protected function generate(): void {
        try {
            if (!$this->isDevelopmentMode()) {
                $this->debug('Mode development tidak aktif');
                return;
            }

            $this->debug('Mulai generate data grup...');

            // Bersihkan data lama jika perlu
            if ($this->shouldClearData()) {
                $this->debug('Membersihkan data lama...');
                $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_groups WHERE id > 0");
                $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_groups AUTO_INCREMENT = 1");
            }

            // Generate data baru
            foreach ($this->groups as $group) {
                $this->debug("Memproses grup: {$group['nama']}");
                
                // Cek duplikasi dalam sektor yang sama
                $existing = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT id FROM {$this->wpdb->prefix}app_groups WHERE service_id = %d AND nama = %s",
                    $group['service_id'],
                    $group['nama']
                ));

                if ($existing) {
                    $this->debug("Grup {$group['nama']} sudah ada dalam sektor {$group['service_id']}, melewati...");
                    continue;
                }

                // Generate nama file dokumen
                $doc_filename = sanitize_title($group['nama']) . '.' . $group['dokumen_type'];
                $doc_path = 'wp-content/uploads/wp-equipment/documents/' . $doc_filename;

                // Insert data
                $current_user_id = get_current_user_id();
                $inserted = $this->wpdb->insert(
                    $this->wpdb->prefix . 'app_groups',
                    [
                        'service_id' => $group['service_id'],
                        'nama' => $group['nama'],
                        'keterangan' => $group['keterangan'] ?? '',
                        'dokumen_path' => $doc_path,
                        'dokumen_type' => $group['dokumen_type'],
                        'status' => 'active',
                        'created_by' => $current_user_id,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    [
                        '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
                    ]
                );

                if ($inserted) {
                    $id = $this->wpdb->insert_id;
                    self::$group_ids[] = $id;
                    $this->debug("Berhasil membuat grup {$group['nama']} dengan ID: $id");
                } else {
                    throw new \Exception("Gagal insert grup {$group['nama']}");
                }
            }

            // Bersihkan cache
            $this->debug('Membersihkan cache...');
            $this->cache->invalidateDataTableCache('group_list');
            $this->cache->delete('group_stats');

            $this->debug('Generate data grup selesai');

        } catch (\Exception $e) {
            $this->debug('Error generate: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dapatkan array ID grup yang telah dibuat
     */
    public function getGroupIds(): array {
        return self::$group_ids;
    }
}

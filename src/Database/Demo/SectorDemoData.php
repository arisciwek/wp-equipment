<?php
/**
 * Generator Data Demo untuk Sektor
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/SectorDemoData.php
 */

namespace WPEquipment\Database\Demo;

defined('ABSPATH') || exit;

class SectorDemoData extends AbstractDemoData {
    private static $sector_ids = [];
    protected $sectors =  [
        [
            'id' => 1, 
            'nama' => 'Manufaktur',
            'keterangan' => 'Sektor industri manufaktur dan pengolahan',
        ],
        [
            'id' => 2, 
            'nama' => 'Konstruksi',
            'keterangan' => 'Sektor jasa konstruksi dan pembangunan',
        ],
        [
            'id' => 3, 
            'nama' => 'Transportasi',
            'keterangan' => 'Sektor transportasi dan logistik',
        ],
        [
            'id' => 4, 
            'nama' => 'Pertambangan',
            'keterangan' => 'Sektor pertambangan dan penggalian',
        ],
        [
            'id' => 5, 
            'nama' => 'Telekomunikasi',
            'keterangan' => 'Sektor telekomunikasi dan jaringan',
        ],
        [
            'id' => 6, 
            'nama' => 'Kelistrikan',
            'keterangan' => 'Sektor ketenagalistrikan',
        ],
        [
            'id' => 7, 
            'nama' => 'Elektronika',
            'keterangan' => 'Sektor elektronika dan komponen',
        ],
        [
            'id' => 8, 
            'nama' => 'Permesinan',
            'keterangan' => 'Sektor permesinan dan peralatan',
        ],
        [
            'id' => 9, 
            'nama' => 'Pengujian & Kalibrasi',
            'keterangan' => 'Sektor pengujian dan kalibrasi peralatan',
        ],
        [
            'id' => 10, 
            'nama' => 'Teknologi Informasi',
            'keterangan' => 'Sektor teknologi informasi dan komunikasi',
        ]
    ];

    public function __construct() {
        parent::__construct();
        $this->debug('SectorDemoData dibuat dengan ' . count($this->sectors) . ' sektor');
    }

    /**
     * Validasi data sebelum generate
     */
    protected function validate(): bool {
        try {
            $this->debug('Mulai validasi data sektor...');

            // 1. Cek ketersediaan data
            if (empty($this->sectors)) {
                $this->debug('Error: Data sektor kosong');
                return false;
            }

            // 2. Cek tabel
            $table = $this->wpdb->prefix . 'app_sectors';
            $table_exists = $this->wpdb->get_var(
                $this->wpdb->prepare("SHOW TABLES LIKE %s", $table)
            );
            
            if (!$table_exists) {
                $this->debug("Error: Tabel $table tidak ditemukan");
                return false;
            }

            // 3. Cek nama unik
            $names = array_column($this->sectors, 'nama');
            if (count($names) !== count(array_unique($names))) {
                $this->debug('Error: Ditemukan nama sektor duplikat');
                return false;
            }

            // 4. Validasi struktur kolom sesuai SectorsDB.php
            $columns = $this->wpdb->get_results("DESCRIBE {$table}");
            $required_columns = [
                'id' => 'bigint',
                'nama' => 'varchar',
                'keterangan' => 'text',
                'status' => 'enum',
                'created_by' => 'bigint',
                'created_at' => 'datetime',
                'updated_at' => 'datetime'
            ];

            $existing_columns = [];
            foreach ($columns as $col) {
                $existing_columns[$col->Field] = strtolower($col->Type);
            }

            foreach ($required_columns as $col_name => $col_type) {
                if (!isset($existing_columns[$col_name])) {
                    $this->debug("Error: Kolom {$col_name} tidak ditemukan");
                    return false;
                }
            }

            // 5. Validasi struktur data
            foreach ($this->sectors as $index => $sector) {
                if (!isset($sector['nama']) || empty($sector['nama'])) {
                    $this->debug("Error: Nama sektor kosong pada index $index");
                    return false;
                }
            }

            $this->debug('Validasi data sektor berhasil');
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

            $this->debug('Mulai generate data sektor...');

            // Bersihkan data lama jika perlu
            if ($this->shouldClearData()) {
                $this->debug('Membersihkan data lama...');
                $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_sectors WHERE id > 0");
                $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_sectors AUTO_INCREMENT = 1");
            }

            // Generate data baru
            foreach ($this->sectors as $sector) {
                $this->debug("Memproses sektor: {$sector['nama']}");
                
                // Cek duplikasi
                $existing = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT id FROM {$this->wpdb->prefix}app_sectors WHERE nama = %s",
                    $sector['nama']
                ));

                if ($existing) {
                    $this->debug("Sektor {$sector['nama']} sudah ada, melewati...");
                    continue;
                }

                // Insert data
                $current_user_id = get_current_user_id();
                $inserted = $this->wpdb->insert(
                    $this->wpdb->prefix . 'app_sectors',
                    [
                        'nama' => $sector['nama'],
                        'keterangan' => $sector['keterangan'] ?? '',
                        'status' => 'active',
                        'created_by' => $current_user_id,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s', '%s', '%d', '%s', '%s']
                );

                if ($inserted) {
                    $id = $this->wpdb->insert_id;
                    self::$sector_ids[] = $id;
                    $this->debug("Berhasil membuat sektor {$sector['nama']} dengan ID: $id");
                } else {
                    throw new \Exception("Gagal insert sektor {$sector['nama']}");
                }
            }

            // Bersihkan cache
            $this->debug('Membersihkan cache...');
            $this->cache->invalidateDataTableCache('sector_list');
            $this->cache->delete('sector_stats');

            $this->debug('Generate data sektor selesai');

        } catch (\Exception $e) {
            $this->debug('Error generate: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dapatkan array ID sektor yang telah dibuat
     */
    public function getSectorIds(): array {
        return self::$sector_ids;
    }
}

<?php
/**
 * Generator Data Demo untuk Service
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/ServiceDemoData.php
 */

namespace WPEquipment\Database\Demo;

defined('ABSPATH') || exit;

class ServiceDemoData extends AbstractDemoData {
    private static $service_ids = [];
    protected $services = [
        [
            'id' => 4,
            'singkatan' => 'DT',      // Destructive Testing
            'nama' => 'Pengujian Merusak (DT)',
            'keterangan' => 'Layanan pengujian dengan metode yang merusak spesimen uji',
            'status' => 'inactive'
        ],
        [
            'id' => 7,
            'singkatan' => 'PAA',
            'nama' => 'Pesawat Angkat dan Pesawat Angkut',
            'keterangan' => 'Layanan terkait alat angkat dan alat angkut',
            'status' => 'active'
        ],
        [
            'id' => 17,
            'singkatan' => 'ILP',
            'nama' => 'Listrik dan Instalasi Penyalur Petir',
            'keterangan' => 'Layanan kelistrikan dan sistem proteksi petir',
            'status' => 'active'
        ],
        [
            'id' => 23,
            'singkatan' => 'NDT',     // Non-Destructive Testing
            'nama' => 'Pengujian Tidak Merusak (NDT) Non Radiasi',
            'keterangan' => 'Layanan pengujian tanpa merusak dengan metode non-radiasi',
            'status' => 'inactive'
        ],
        [
            'id' => 24,
            'singkatan' => 'NDTR',    // Non-Destructive Testing Radiation
            'nama' => 'Pengujian Tidak Merusak (NDT) Radiasi',
            'keterangan' => 'Layanan pengujian tanpa merusak dengan metode radiasi',
            'status' => 'inactive'
        ],
        [
            'id' => 25,
            'singkatan' => 'IPK',
            'nama' => 'Sarana Proteksi Kebakaran',
            'keterangan' => 'Layanan terkait sistem pencegahan dan penanggulangan kebakaran',
            'status' => 'active'
        ],
        [
            'id' => 26,
            'singkatan' => 'KBG',     // Konstruksi dan BanGunan
            'nama' => 'Konstruksi dan Bangunan',
            'keterangan' => 'Layanan terkait konstruksi dan bangunan',
            'status' => 'inactive'
        ],
        [
            'id' => 29,
            'singkatan' => 'LK',      // Lingkungan Kerja
            'nama' => 'Lingkungan Kerja',
            'keterangan' => 'Layanan terkait keselamatan lingkungan kerja',
            'status' => 'inactive'
        ],
        [
            'id' => 30,
            'singkatan' => 'BB',      // Bahan Berbahaya
            'nama' => 'Bahan Berbahaya',
            'keterangan' => 'Layanan terkait penanganan bahan berbahaya',
            'status' => 'inactive'
        ],
        [
            'id' => 31,
            'singkatan' => 'AKR',     // AngKuR
            'nama' => 'Angkur',
            'keterangan' => 'Layanan terkait sistem pengangkuran',
            'status' => 'inactive'
        ],
        [
            'id' => 32,
            'singkatan' => 'APD',     // Alat Pelindung Diri
            'nama' => 'Alat Pelindung Diri dan Alat Penahan Jatuh Perorangan',
            'keterangan' => 'Layanan terkait APD dan alat penahan jatuh',
            'status' => 'inactive'
        ],
        [
            'id' => 34,
            'singkatan' => 'PTP',
            'nama' => 'Pesawat Tenaga dan Produksi',
            'keterangan' => 'Layanan terkait mesin tenaga dan peralatan produksi',
            'status' => 'active'
        ],
        [
            'id' => 35,
            'singkatan' => 'PUBT',
            'nama' => 'Pesawat Uap, Bejana Tekanan dan Tangki Timbun',
            'keterangan' => 'Layanan terkait peralatan bertekanan dan tangki penyimpanan',
            'status' => 'active'
        ],
        [
            'id' => 36,
            'singkatan' => 'LIE',
            'nama' => 'Elevator dan Eskalator',
            'keterangan' => 'Layanan terkait lift dan eskalator',
            'status' => 'active'
        ],
        [
            'id' => 66,
            'singkatan' => 'RTL',     // ReTester LPG
            'nama' => 'Retester Tabung LPG',
            'keterangan' => 'Layanan pengujian ulang tabung LPG',
            'status' => 'inactive'
        ]
    ];

    public function __construct() {
        parent::__construct();
        $this->debug('ServiceDemoData dibuat dengan ' . count($this->services) . ' sektor');
    }

    /**
     * Validasi data sebelum generate
     */
    protected function validate(): bool {
        try {
            $this->debug('Mulai validasi data sektor...');

            // 1. Cek ketersediaan data
            if (empty($this->services)) {
                $this->debug('Error: Data sektor kosong');
                return false;
            }

            // 2. Cek tabel
            $table = $this->wpdb->prefix . 'app_services';
            $table_exists = $this->wpdb->get_var(
                $this->wpdb->prepare("SHOW TABLES LIKE %s", $table)
            );
            
            if (!$table_exists) {
                $this->debug("Error: Tabel $table tidak ditemukan");
                return false;
            }

            // 3. Cek nama unik
            $names = array_column($this->services, 'nama');
            if (count($names) !== count(array_unique($names))) {
                $this->debug('Error: Ditemukan nama sektor duplikat');
                return false;
            }

            // 4. Validasi struktur kolom sesuai ServicesDB.php
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
            foreach ($this->services as $index => $service) {
                if (!isset($service['nama']) || empty($service['nama'])) {
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
                $this->wpdb->query("DELETE FROM {$this->wpdb->prefix}app_services WHERE id > 0");
                $this->wpdb->query("ALTER TABLE {$this->wpdb->prefix}app_services AUTO_INCREMENT = 1");
            }

            // Generate data baru
            foreach ($this->services as $service) {
                $this->debug("Memproses sektor: {$service['nama']}");
                
                // Cek duplikasi
                $existing = $this->wpdb->get_var($this->wpdb->prepare(
                    "SELECT id FROM {$this->wpdb->prefix}app_services WHERE nama = %s",
                    $service['nama']
                ));

                if ($existing) {
                    $this->debug("Bidang jasa {$service['nama']} sudah ada, melewati...");
                    continue;
                }

                // Insert data
                $current_user_id = get_current_user_id();
                $inserted = $this->wpdb->insert(
                    $this->wpdb->prefix . 'app_services',
                    [
                        'nama' => $service['nama'],
                        'singkatan' => $service['singkatan'],
                        'keterangan' => $service['keterangan'] ?? '',
                        'status' => 'active',
                        'created_by' => $current_user_id,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s',  '%s', '%s', '%d', '%s', '%s']
                );

                if ($inserted) {
                    $id = $this->wpdb->insert_id;
                    self::$service_ids[] = $id;
                    $this->debug("Berhasil membuat sektor {$service['nama']} dengan ID: $id");
                } else {
                    throw new \Exception("Gagal insert sektor {$service['nama']}");
                }
            }

            // Bersihkan cache
            $this->debug('Membersihkan cache...');
            $this->cache->invalidateDataTableCache('service_list');
            $this->cache->delete('service_stats');

            $this->debug('Generate data sektor selesai');

        } catch (\Exception $e) {
            $this->debug('Error generate: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dapatkan array ID sektor yang telah dibuat
     */
    public function getServiceIds(): array {
        return self::$service_ids;
    }
}

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
    protected $groups = [
        [
            'id' => 1,
            'service_id' => 2, // PAA - Pesawat Angkat dan Pesawat Angkut
            'nama' => 'Pesawat Angkat',
            'keterangan' => 'Peralatan untuk mengangkat beban secara vertikal dan memindahkannya secara horizontal dalam jarak tertentu',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 2,
            'service_id' => 2, // PAA - Pesawat Angkat dan Pesawat Angkut
            'nama' => 'Pesawat Angkut',
            'keterangan' => 'Peralatan yang digunakan untuk mengangkut material atau barang',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 3,
            'service_id' => 2, // PAA - Pesawat Angkat dan Pesawat Angkut
            'nama' => 'Alat Bantu Angkat Angkut',
            'keterangan' => 'Peralatan pendukung untuk operasi pengangkatan dan pengangkutan material',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 4,
            'service_id' => 13, // PUBT - Pesawat Uap, Bejana Tekanan dan Tangki Timbun
            'nama' => 'Tangki Timbun',
            'keterangan' => 'Wadah penyimpanan bahan bakar atau bahan kimia dengan kapasitas besar',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 5,
            'service_id' => 13, // PUBT - Pesawat Uap, Bejana Tekanan dan Tangki Timbun
            'nama' => 'Bejana Uap',
            'keterangan' => 'Bejana tertutup yang digunakan untuk menghasilkan uap dengan tekanan lebih besar dari tekanan atmosfer',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 6,
            'service_id' => 13, // PUBT - Pesawat Uap, Bejana Tekanan dan Tangki Timbun
            'nama' => 'Bejana Tekanan',
            'keterangan' => 'Wadah tertutup yang dirancang untuk menampung gas atau cairan pada tekanan yang berbeda dari tekanan ambien',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 7,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Penggerak Mula',
            'keterangan' => 'Mesin atau peralatan yang mengubah energi dari bentuk lain menjadi energi mekanik',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 8,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Mesin Konvensional',
            'keterangan' => 'Mesin atau peralatan produksi dengan pengoperasian manual atau semi-otomatis',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 9,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Mesin Terkomputerisasi',
            'keterangan' => 'Mesin atau peralatan produksi dengan sistem kontrol berbasis komputer',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 10,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Transmisi Tenaga Mekanik',
            'keterangan' => 'Sistem yang mentransmisikan daya dari satu lokasi ke lokasi lain',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 11,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Tanur',
            'keterangan' => 'Ruang tertutup yang digunakan untuk memanas bahan pada suhu tinggi',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 12,
            'service_id' => 3, // ILP - Listrik dan Instalasi Penyalur Petir
            'nama' => 'Instalasi Listrik',
            'keterangan' => 'Sistem pemasangan peralatan listrik yang saling terhubung untuk memenuhi tujuan tertentu',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 13,
            'service_id' => 3, // ILP - Listrik dan Instalasi Penyalur Petir
            'nama' => 'Penyalur Petir Konvensional',
            'keterangan' => 'Sistem proteksi petir dengan metode konvensional',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 14,
            'service_id' => 3, // ILP - Listrik dan Instalasi Penyalur Petir
            'nama' => 'Penyalur Petir Elektrostatik',
            'keterangan' => 'Sistem proteksi petir dengan teknologi elektrostatik',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 15,
            'service_id' => 6, // IPK - Sarana Proteksi Kebakaran
            'nama' => 'Instalasi Hydrant',
            'keterangan' => 'Sistem pemadam kebakaran yang menggunakan air bertekanan',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 16,
            'service_id' => 6, // IPK - Sarana Proteksi Kebakaran
            'nama' => 'Alarm Kebakaran Otomatik',
            'keterangan' => 'Sistem deteksi dan peringatan kebakaran yang bekerja secara otomatis',
            'dokumen_type' => 'docx'
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

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
            'service_id' => 2, // PAA - Pesawat Angkat dan Angkut
            'nama' => 'Forklift',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 2,
            'service_id' => 2, // PAA - Pesawat Angkat dan Angkut
            'nama' => 'Keran angkat',
            'keterangan' => 'keran angkat, terdiri atas overhead crane, overhead travelling crane, hoist crane, chain block, monorail crane, wall crane/jib crane, stacker crane, gantry crane, semi gantry crane, launcher gantry crane, roller gantry crane, rail mounted gantry crane, rubber tire gantry crane, ship unloader crane, gantry luffing crane, container lokomotif crane dan/atau railway crane, truck crane, tractor crane, side boom crane/crab crane, derrick crane, tower crane, pedestal crane, hidraulik drilling rig, pilling crane/mesin pancang dan peralatan lain yang scjenis crane, cargo crane, crawler crane, mobile crane,floating crane, floating derricks crane, floating shipderrick ship crane, dredging crane, ponton crane,crane, portal crane, ship crane, barge crane, dll',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 3,
            'service_id' => 13, // PUBT - Pesawat Uap, Bejana Tekanan dan Tangki Timbun
            'nama' => 'Tangki Timbun 10.000 Liter',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 4,
            'service_id' => 13, // PUBT
            'nama' => 'Ketel Uap 50 Ton/jam',
            'keterangan' => 'Kapasitas di bawah 50 ton uap/jam',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 5,
            'service_id' => 13, // PUBT
            'nama' => 'Ketel Uap 100 Ton/jam',
            'keterangan' => 'Kapasitas 50 sampai dengan 100 ton uap/jam',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 6,
            'service_id' => 13, // PUBT
            'nama' => 'Ketel Uap > 100 Ton/jam',
            'keterangan' => 'Kapasitas diatas 100 Ton uap/jam',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 7,
            'service_id' => 2, // PAA
            'nama' => 'Alat Pengatur Posisi Benda Kerja',
            'keterangan' => 'Rotator, Robotik, Takel dan peralatan lain yang sejenis',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 8,
            'service_id' => 2, // PAA
            'nama' => 'Personal Platform',
            'keterangan' => 'Passenger Hoist, Gondola dan peralatan lain yang sejenis',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 9,
            'service_id' => 12, // PTP - Pesawat Tenaga dan Produksi
            'nama' => 'Penggerak Mula',
            'keterangan' => 'Turbin air, Mesin uap, Motor bakar, Kincir angin dan sejenisnya',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 10,
            'service_id' => 2, // PAA
            'nama' => 'Kereta',
            'keterangan' => 'Kereta Gantung, Komidi Putar, Roller Coaster, Kereta Ayun, Lokomotif beserta rangkaiannya, dan peralatan lain yang sejenis',
            'dokumen_type' => 'docx'
        ],[
            'id' => 11,
            'service_id' => 2, // PAA
            'nama' => 'Conveyor',
            'keterangan' => 'Conveyor',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 12,
            'service_id' => 13, // PUBT
            'nama' => 'Tabung Gas',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 13,
            'service_id' => 2, // PAA
            'nama' => 'Personal Basket',
            'keterangan' => 'Manlift, Boomlift, Scissor lift, Hydraulic stairs dan peralatan lain yang sejenis',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 14,
            'service_id' => 2, // PAA
            'nama' => 'Truk',
            'keterangan' => 'Tractor, Truk pengangkut bahan berbahaya, Dump Truck, Cargo Truck lift, Trailer, Side Loader Truck, Module Transporter, Axle Transport, Car towing, dan peralatan lain yang sejenis',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 15,
            'service_id' => 2, // PAA
            'nama' => 'Robotik dan Konveyor',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 16,
            'service_id' => 2, // PAA
            'nama' => 'Batang Balok',
            'keterangan' => 'Sreader Bdrr, Balok Pengangkat lhfiinS Beaml, dan sejenisnya',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 17,
            'service_id' => 2, // PAA
            'nama' => 'Keranjang',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 18,
            'service_id' => 2, // PAA
            'nama' => 'Timba',
            'keterangan' => 'hrckeq, Konstruksi Bor (Drill), Pile Hammer,darr sejenisnya',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 19,
            'service_id' => 13, // PUBT
            'nama' => 'Bejana Penyimpanan Gas',
            'keterangan' => 'Bejana penyimpanan gas, bahan bakar gas yang digunakan sebagai bahan bakar kendaraan',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 20,
            'service_id' => 13, // PUBT
            'nama' => 'Bejanan Peyimpanan Bahan Bakar Gas',
            'keterangan' => 'Bejanan Peyimpanan bahan bakar gas yang digunakan sebagai bahan bakar kendaraan',
            'dokumen_type' => 'docx'
        ],[
            'id' => 21,
            'service_id' => 13, // PUBT
            'nama' => 'Bejana Proses',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 22,
            'service_id' => 13, // PUBT
            'nama' => 'Pesawat Pendingin',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 23,
            'service_id' => 13, // PUBT
            'nama' => 'Tangki Timbun 50.000 Liter',
            'keterangan' => 'Kapasitas diatas 10.000 sampai dengan 50.000 Liter',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 24,
            'service_id' => 13, // PUBT
            'nama' => 'Tangki Timbun > 50.000 Liter',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 25,
            'service_id' => 12, // PTP
            'nama' => 'Mesin Perkakas Konvensional',
            'keterangan' => 'Mesin perkakas jenis konvensional',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 26,
            'service_id' => 12, // PTP
            'nama' => 'Mesin Perkakas Terkomputerisasi',
            'keterangan' => 'Mesin perkakas jenis terkomputerisasi/ Computer Numerical Confrol (CNC)',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 27,
            'service_id' => 12, // PTP
            'nama' => 'Transmisi tenaga mekanik',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 28,
            'service_id' => 12, // PTP
            'nama' => 'Tanur',
            'keterangan' => 'Kiln, Reheating Furnace, Oven, blast furnace, basic oxygen furnace, electric arc furnace, reheater furnace, ladle, dsb',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 29,
            'service_id' => 3, // ILP
            'nama' => 'Instalasi Listrik',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 30,
            'service_id' => 3, // ILP
            'nama' => 'Penyalur Petir Konvensional',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 31,
            'service_id' => 3, // ILP
            'nama' => 'Penyalur Petir Elektrostatik',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 32,
            'service_id' => 6, // IPK
            'nama' => 'Instalasi Hydrant',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 33,
            'service_id' => 6, // IPK
            'nama' => 'Instalasi Alarm Kebakaran Otomatik',
            'keterangan' => null,
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 34,
            'service_id' => 12, // PTP
            'nama' => 'Mesin Produksi Konvensional',
            'keterangan' => 'Mesin Produksi Konvensional / manual',
            'dokumen_type' => 'docx'
        ],
        [
            'id' => 35,
            'service_id' => 12, // PTP
            'nama' => 'Mesin Produksi Terkomputerisasi',
            'keterangan' => 'Mesin Produksi Terkomputerisasi (CNC)',
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

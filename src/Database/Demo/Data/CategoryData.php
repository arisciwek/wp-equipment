<?php
/**
 * Category Demo Data
 *
 * @package     WP_Equipment
 * @subpackage  Database/Demo/Data
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Database/Demo/Data/CategoryData.php
 */

namespace WPEquipment\Database\Demo\Data;

class CategoryData {
    public static $data = [
        [
            'id' => 1,
            'code' => 'III',
            'name' => 'JASA PENERBITAN SURAT KETERANGAN LAYAK KESELAMATAN DAN KESEHATAN KERJA SERTA SERTIFIKASI KESELAMATAN DAN KESEHATAN KERJA',
            'description' => 'Layanan pengurusan dan penerbitan surat keterangan K3',
            'level' => 1,
            'parent_id' => null,
            'group_id' => null,
            'relation_id' => null,
            'sort_order' => 1,
            'unit' => null,
            'pnbp' => null,
            'status' => 'active'
        ],
        [
            'id' => 2,
            'code' => 'A',
            'name' => 'Penerbitan Surat Keterangan Layak Keselamatan dan Kesehatan Kerja',
            'description' => 'Penerbitan surat keterangan kelayakan K3',
            'level' => 2,
            'parent_id' => 1,
            'group_id' => null,
            'relation_id' => null,
            'sort_order' => 1,
            'unit' => null,
            'pnbp' => null,
            'status' => 'active'
        ],
        [
            'id' => 3,
            'code' => '1',
            'name' => 'Pemeriksaan dan Pengujian bidang Ergonomi, Lingkungan dan Kerja, Bahan Berbahaya dan Kesehatan Kerja',
            'description' => 'Layanan pemeriksaan dan pengujian ergonomi dan lingkungan kerja',
            'level' => 3,
            'parent_id' => 2,
            'group_id' => null,
            'relation_id' => null,
            'sort_order' => 1,
            'unit' => 'per dokumen',
            'pnbp' => 300000,
            'status' => 'active'
        ]
    ];
}

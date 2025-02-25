<?php
/**
 * SQL to PHP Category Data Converter (Fixed Version)
 * 
 * Script untuk mengkonversi data SQL ke format PHP array untuk CategoryData.php
 * 
 * Cara penggunaan:
 * 1. Simpan SQL dump di file terpisah (misalnya tarif_1.sql)
 * 2. Jalankan script ini dari command line: php convert_sql_to_category.php
 * 3. Copy hasil output ke CategoryData.php
 */

// Ambil konten SQL file
$sql_file = 'tarif_1.sql';
$sql_content = file_get_contents($sql_file);

// Aktifkan debugging
$debug = true;

// Fungsi debug log
function debug_log($message) {
    global $debug;
    if ($debug) {
        if (is_array($message) || is_object($message)) {
            echo "[DEBUG] " . print_r($message, true) . "\n";
        } else {
            echo "[DEBUG] $message\n";
        }
    }
}

// Cari INSERT statement dengan pattern yang lebih spesifik
if (preg_match('/INSERT INTO .*?`tarif_1`.*?\s*\(`id`,\s*`name`,\s*`group_id`,\s*`satuan`,\s*`mata_uang`,\s*`pnbp`,\s*`level`,\s*`parent_id`,\s*`status`\)\s*VALUES\s+(.*?);/s', $sql_content, $matches)) {
    debug_log("INSERT statement ditemukan");
    $values_part = $matches[1];
    
    // Tampilkan bagian values untuk debugging
    debug_log("VALUES part: " . substr($values_part, 0, 200) . "...");
    
    // Parse values dengan regex yang lebih spesifik untuk struktur SQL
    preg_match_all('/\((\d+),\s*\'([^\']*)\',\s*(NULL|\'[^\']*\'),\s*(NULL|\'[^\']*\'),\s*(NULL|\'[^\']*\'),\s*(NULL|\d+),\s*(\d+),\s*(\d+),\s*(\d+)\)/', $values_part, $entries, PREG_SET_ORDER);
    
    debug_log("Jumlah entries yang ditemukan: " . count($entries));
    
    if (count($entries) == 0) {
        debug_log("Tidak ada entries yang ditemukan. Mencoba pattern alternatif...");
        // Coba pattern alternatif yang lebih longgar
        preg_match_all('/\(([^)]+)\)/', $values_part, $alt_entries);
        debug_log("Alternatif pattern menemukan: " . count($alt_entries[0]) . " entries");
        
        $entries = [];
        foreach ($alt_entries[1] as $entry_str) {
            // Pecah string berdasarkan koma, tapi perhatikan nilai dalam quotes
            $entry = [];
            $current = '';
            $in_quotes = false;
            
            for ($i = 0; $i < strlen($entry_str); $i++) {
                $char = $entry_str[$i];
                
                if ($char === "'" && ($i == 0 || $entry_str[$i-1] !== '\\')) {
                    $in_quotes = !$in_quotes;
                    $current .= $char;
                } else if ($char === ',' && !$in_quotes) {
                    $entry[] = $current;
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
            
            if (!empty($current)) {
                $entry[] = $current;
            }
            
            // Bersihkan whitespace
            $entry = array_map('trim', $entry);
            
            $entries[] = $entry;
        }
    }
    
    // Tampilkan beberapa entries untuk debugging
    if ($debug) {
        debug_log("Sample entries:");
        for ($i = 0; $i < min(3, count($entries)); $i++) {
            debug_log("Entry $i: ");
            debug_log($entries[$i]);
        }
    }
    
    $categories = [];
    
    // Loop untuk setiap entry
    foreach ($entries as $entry_index => $entry) {
        // Jika menggunakan regex PREG_SET_ORDER
        if (isset($entry[0]) && is_string($entry[0]) && strpos($entry[0], '(') === 0) {
            // Entry dari regex yang spesifik - kita punya named capture groups
            $id = (int)$entry[1];
            $name = $entry[2];
            $group_id = ($entry[3] === 'NULL') ? null : trim($entry[3], "'");
            $satuan = ($entry[4] === 'NULL') ? null : trim($entry[4], "'");
            $mata_uang = ($entry[5] === 'NULL') ? null : trim($entry[5], "'");
            $pnbp = ($entry[6] === 'NULL') ? null : (float)$entry[6];
            $level = (int)$entry[7];
            $parent_id = (int)$entry[8];
            $status = ((int)$entry[9] === 1) ? 'active' : 'inactive';
        } else {
            // Entry dari pattern alternatif - harus diproses secara manual
            // Pastikan ada minimal 9 kolom
            if (count($entry) < 9) {
                debug_log("Entry $entry_index memiliki kurang dari 9 kolom - dilewati");
                continue;
            }
            
            // Ambil nilai-nilai untuk setiap kolom
            $id = (int)trim($entry[0], "'");
            $name = trim($entry[1], "'");
            $group_id = (trim($entry[2]) === 'NULL') ? null : trim($entry[2], "'");
            $satuan = (trim($entry[3]) === 'NULL') ? null : trim($entry[3], "'");
            $mata_uang = (trim($entry[4]) === 'NULL') ? null : trim($entry[4], "'");
            $pnbp = (trim($entry[5]) === 'NULL') ? null : (float)trim($entry[5], "'");
            $level = (int)trim($entry[6], "'");
            $parent_id = (trim($entry[7]) === '0') ? null : (int)trim($entry[7], "'");
            $status = ((int)trim($entry[8], "'") === 1) ? 'active' : 'inactive';
        }
        
        // Validasi
        if (empty($name)) {
            debug_log("Entry $entry_index memiliki nama kosong - dilewati");
            continue;
        }
        
        // Generate deskripsi default dari nama
        $description = "Layanan " . strtolower(trim(preg_replace('/^[0-9\.\s]+/', '', $name)));
        
        // Debug
        debug_log("Processing category: ID=$id, Name=$name, Level=$level, ParentID=" . ($parent_id ?: 'NULL') . ", PNBP=" . ($pnbp ?: 'NULL'));
        
        // Tambahkan ke array kategori
        $categories[] = [
            'id' => $id,
            'code' => '', // Akan diisi otomatis dengan kode alfanumerik acak
            'name' => $name,
            'description' => $description,
            'level' => $level,
            'parent_id' => $parent_id,
            'group_id' => null,
            'relation_id' => null,
            'sort_order' => $id, // Gunakan ID sebagai sort_order default
            'unit' => $satuan,
            'pnbp' => $pnbp,
            'status' => $status
        ];
    }
    
    debug_log("Total kategori yang diproses: " . count($categories));
    
    // Generate PHP code
    $output = "<?php\n/**\n * Category Demo Data from SQL\n *\n * @package     WP_Equipment\n * @subpackage  Database/Demo/Data\n * @version     1.0.0\n */\n\nnamespace WPEquipment\\Database\\Demo\\Data;\n\nclass CategoryData {\n    public static \$data = [\n";
    
    foreach ($categories as $category) {
        $output .= "        [\n";
        foreach ($category as $key => $value) {
            if (is_null($value)) {
                $output .= "            '$key' => null,\n";
            } elseif (is_numeric($value)) {
                $output .= "            '$key' => $value,\n";
            } else {
                // Escape single quotes in the value
                $value = str_replace("'", "\\'", $value);
                $output .= "            '$key' => '$value',\n";
            }
        }
        $output .= "        ],\n";
    }
    
    $output .= "    ];\n}\n";
    
    // Output hasil
    file_put_contents('CategoryData_generated.php', $output);
    echo "File CategoryData_generated.php telah dibuat dengan " . count($categories) . " kategori!\n";
    
} else {
    echo "Tidak dapat menemukan INSERT statement di file SQL.\n";
    // Tampilkan 200 karakter pertama untuk debugging
    debug_log("200 karakter pertama: " . substr($sql_content, 0, 200));
}

<?php
/**
 * Service Validator Class
 *
 * @package     WP_Equipment
 * @subpackage  Validators
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Validators/ServiceValidator.php
 *
 * Description: Validator untuk data sektor.
 *              Handles validasi input untuk operasi CRUD.
 *              Includes validasi untuk:
 *              - Unique name checking
 *              - Required fields
 *              - Format validation
 *              - Access control
 */

namespace WPEquipment\Validators;

use WPEquipment\Models\ServiceModel;

class ServiceValidator {
    private ServiceModel $model;

    public function __construct() {
        $this->model = new ServiceModel();
        add_action('wp_ajax_validate_service_access', [$this, 'validateAccess']);
    }

    public function validateAccess() {
        try {
            // Debug: log request yang masuk
            error_log('Validating service access. Request data: ' . print_r($_POST, true));

            // Cek nonce dengan debug
            $nonce_valid = check_ajax_referer('wp_equipment_nonce', 'nonce', false);
            error_log('Nonce validation result: ' . ($nonce_valid ? 'valid' : 'invalid'));
            
            if (!$nonce_valid) {
                throw new \Exception('Invalid security token');
            }

            // Cek permission dengan debug
            $has_permission = current_user_can('manage_options');
            error_log('Permission check result: ' . ($has_permission ? 'has permission' : 'no permission'));
            
            if (!$has_permission) {
                throw new \Exception('Insufficient permissions');
            }

            // Validasi ID
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            error_log('Validating service ID: ' . $id);
            
            if (!$id) {
                throw new \Exception('Invalid service ID');
            }

            // Cek keberadaan sektor
            $service = $this->model->find($id);
            error_log('Service exists: ' . ($service ? 'yes' : 'no'));
            
            if (!$service) {
                throw new \Exception('Service not found');
            }

            wp_send_json_success([
                'message' => 'Access validated successfully',
                'service_id' => $id
            ]);

        } catch (\Exception $e) {
            error_log('Service access validation error: ' . $e->getMessage());
            
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function validateCreate(array $data): array {
        $errors = [];

        // Validasi nama sektor (wajib dan unik)
        if (empty($data['nama'])) {
            $errors[] = __('Nama sektor wajib diisi', 'wp-equipment');
        } elseif (strlen($data['nama']) > 100) {
            $errors[] = __('Nama sektor maksimal 100 karakter', 'wp-equipment');
        } elseif ($this->model->existsByName($data['nama'])) {
            $errors[] = __('Nama sektor sudah digunakan', 'wp-equipment');
        }

        // Validasi keterangan (opsional)
        if (!empty($data['keterangan']) && strlen($data['keterangan']) > 255) {
            $errors[] = __('Keterangan maksimal 255 karakter', 'wp-equipment');
        }

        // Validasi status
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors[] = __('Status tidak valid', 'wp-equipment');
        }

        return $errors;
    }

    public function validateUpdate(array $data, int $id): array {
        $errors = [];

        // Validasi ID
        if (!$id) {
            $errors[] = __('ID sektor tidak valid', 'wp-equipment');
            return $errors;
        }

        // Cek keberadaan sektor
        $existing = $this->model->find($id);
        if (!$existing) {
            $errors[] = __('Bidag jasa tidak ditemukan', 'wp-equipment');
            return $errors;
        }

        // Validasi nama sektor (wajib dan unik, kecuali nama sama dengan yang sudah ada)
        if (empty($data['nama'])) {
            $errors[] = __('Nama sektor wajib diisi', 'wp-equipment');
        } elseif (strlen($data['nama']) > 100) {
            $errors[] = __('Nama sektor maksimal 100 karakter', 'wp-equipment');
        } elseif ($this->model->existsByName($data['nama']) && $data['nama'] !== $existing->nama) {
            $errors[] = __('Nama sektor sudah digunakan', 'wp-equipment');
        }

        // Validasi keterangan (opsional)
        if (!empty($data['keterangan']) && strlen($data['keterangan']) > 255) {
            $errors[] = __('Keterangan maksimal 255 karakter', 'wp-equipment');
        }

        // Validasi status
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors[] = __('Status tidak valid', 'wp-equipment');
        }

        return $errors;
    }

    public function validateDelete(int $id): array {
        $errors = [];

        // Validasi ID
        if (!$id) {
            $errors[] = __('ID sektor tidak valid', 'wp-equipment');
            return $errors;
        }

        // Cek keberadaan sektor
        $existing = $this->model->find($id);
        if (!$existing) {
            $errors[] = __('Sektor tidak ditemukan', 'wp-equipment');
            return $errors;
        }

        // Cek apakah sektor memiliki grup terkait
        if ($this->model->hasGroups($id)) {
            $errors[] = __('Tidak dapat menghapus sektor yang memiliki grup', 'wp-equipment');
        }

        return $errors;
    }

    /**
     * Validasi format data sektor
     */
    private function validateFormat(array $data): array {
        $errors = [];

        // Validasi format nama (hanya huruf, angka, dan beberapa karakter khusus)
        if (!empty($data['nama']) && !preg_match('/^[\w\s\-\&\.]+$/u', $data['nama'])) {
            $errors[] = __('Nama sektor hanya boleh berisi huruf, angka, spasi, dan karakter - & .', 'wp-equipment');
        }

        // Validasi format keterangan (mencegah HTML dan script)
        if (!empty($data['keterangan'])) {
            $clean_description = wp_kses($data['keterangan'], []);
            if ($clean_description !== $data['keterangan']) {
                $errors[] = __('Keterangan tidak boleh mengandung HTML atau script', 'wp-equipment');
            }
        }

        return $errors;
    }
}

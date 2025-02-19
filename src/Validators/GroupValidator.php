<?php
/**
 * Group Validator Class
 *
 * @package     WP_Equipment
 * @subpackage  Validators
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/src/Validators/GroupValidator.php
 *
 * Description: Validator untuk data grup.
 *              Handles validasi input untuk operasi CRUD.
 *              Includes validasi untuk:
 *              - Unique name checking per service
 *              - Required fields
 *              - Format validation
 *              - Document validation
 *              - Access control
 */

namespace WPEquipment\Validators;

use WPEquipment\Models\GroupModel;
use WPEquipment\Models\ServiceModel;

class GroupValidator {
    private GroupModel $model;
    private ServiceModel $serviceModel;
    private array $allowedDocTypes = ['docx', 'odt'];
    private int $maxFileSize = 5242880; // 5MB dalam bytes

    public function __construct() {
        $this->model = new GroupModel();
        $this->serviceModel = new ServiceModel();
        add_action('wp_ajax_validate_group_access', [$this, 'validateAccess']);
    }

    public function validateAccess() {
        try {
            // Debug: log request yang masuk
            error_log('Validating group access. Request data: ' . print_r($_POST, true));

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
            error_log('Validating group ID: ' . $id);
            
            if (!$id) {
                throw new \Exception('Invalid group ID');
            }

            // Cek keberadaan grup
            $group = $this->model->find($id);
            error_log('Group exists: ' . ($group ? 'yes' : 'no'));
            
            if (!$group) {
                throw new \Exception('Group not found');
            }

            wp_send_json_success([
                'message' => 'Access validated successfully',
                'group_id' => $id
            ]);

        } catch (\Exception $e) {
            error_log('Group access validation error: ' . $e->getMessage());
            
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function validateCreate(array $data): array {
        $errors = [];

        // Validasi service_id (wajib dan harus valid)
        if (empty($data['service_id'])) {
            $errors[] = __('Service ID wajib diisi', 'wp-equipment');
        } else {
            $service = $this->serviceModel->find($data['service_id']);
            if (!$service) {
                $errors[] = __('Service tidak valid', 'wp-equipment');
            } elseif ($service->status !== 'active') {
                $errors[] = __('Service tidak aktif', 'wp-equipment');
            }
        }

        // Validasi nama grup (wajib dan unik per service)
        if (empty($data['nama'])) {
            $errors[] = __('Nama grup wajib diisi', 'wp-equipment');
        } elseif (strlen($data['nama']) > 100) {
            $errors[] = __('Nama grup maksimal 100 karakter', 'wp-equipment');
        } elseif (!empty($data['service_id']) && $this->model->existsByNameInService($data['nama'], $data['service_id'])) {
            $errors[] = __('Nama grup sudah digunakan dalam service ini', 'wp-equipment');
        }

        // Validasi keterangan (opsional)
        if (!empty($data['keterangan']) && strlen($data['keterangan']) > 255) {
            $errors[] = __('Keterangan maksimal 255 karakter', 'wp-equipment');
        }

        // Validasi dokumen jika ada
        if (!empty($_FILES['dokumen'])) {
            $doc_errors = $this->validateDocument($_FILES['dokumen']);
            $errors = array_merge($errors, $doc_errors);
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
            $errors[] = __('ID grup tidak valid', 'wp-equipment');
            return $errors;
        }

        // Cek keberadaan grup
        $existing = $this->model->find($id);
        if (!$existing) {
            $errors[] = __('Grup tidak ditemukan', 'wp-equipment');
            return $errors;
        }

        // Validasi service_id (wajib dan harus valid)
        if (empty($data['service_id'])) {
            $errors[] = __('Service ID wajib diisi', 'wp-equipment');
        } else {
            $service = $this->serviceModel->find($data['service_id']);
            if (!$service) {
                $errors[] = __('Service tidak valid', 'wp-equipment');
            } elseif ($service->status !== 'active') {
                $errors[] = __('Service tidak aktif', 'wp-equipment');
            }
        }

        // Validasi nama grup (wajib dan unik per service, kecuali nama sama dengan yang sudah ada)
        if (empty($data['nama'])) {
            $errors[] = __('Nama grup wajib diisi', 'wp-equipment');
        } elseif (strlen($data['nama']) > 100) {
            $errors[] = __('Nama grup maksimal 100 karakter', 'wp-equipment');
        } elseif (!empty($data['service_id']) && 
                  $this->model->existsByNameInService($data['nama'], $data['service_id'], $id) &&
                  $data['nama'] !== $existing->nama) {
            $errors[] = __('Nama grup sudah digunakan dalam service ini', 'wp-equipment');
        }

        // Validasi keterangan (opsional)
        if (!empty($data['keterangan']) && strlen($data['keterangan']) > 255) {
            $errors[] = __('Keterangan maksimal 255 karakter', 'wp-equipment');
        }

        // Validasi dokumen jika ada
        if (!empty($_FILES['dokumen'])) {
            $doc_errors = $this->validateDocument($_FILES['dokumen']);
            $errors = array_merge($errors, $doc_errors);
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
            $errors[] = __('ID grup tidak valid', 'wp-equipment');
            return $errors;
        }

        // Cek keberadaan grup
        $existing = $this->model->find($id);
        if (!$existing) {
            $errors[] = __('Grup tidak ditemukan', 'wp-equipment');
            return $errors;
        }

        return $errors;
    }

    /**
     * Validasi dokumen yang diupload
     */
    private function validateDocument(array $file): array {
        $errors = [];

        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = __('Ukuran file melebihi batas maksimal', 'wp-equipment');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = __('File hanya terupload sebagian', 'wp-equipment');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = __('Tidak ada file yang diupload', 'wp-equipment');
                    break;
                default:
                    $errors[] = __('Terjadi kesalahan saat upload file', 'wp-equipment');
            }
            return $errors;
        }

        // Validasi ukuran file
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = sprintf(
                __('Ukuran file maksimal adalah %s MB', 'wp-equipment'),
                $this->maxFileSize / 1048576
            );
        }

        // Validasi tipe file
        $file_info = wp_check_filetype($file['name']);
        if (!in_array($file_info['ext'], $this->allowedDocTypes)) {
            $errors[] = sprintf(
                __('Tipe file tidak didukung. Tipe yang diizinkan: %s', 'wp-equipment'),
                implode(', ', $this->allowedDocTypes)
            );
        }

        return $errors;
    }

    /**
     * Validasi format data grup
     */
    private function validateFormat(array $data): array {
        $errors = [];

        // Validasi format nama (hanya huruf, angka, dan beberapa karakter khusus)
        if (!empty($data['nama']) && !preg_match('/^[\w\s\-\&\.]+$/u', $data['nama'])) {
            $errors[] = __('Nama grup hanya boleh berisi huruf, angka, spasi, dan karakter - & .', 'wp-equipment');
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

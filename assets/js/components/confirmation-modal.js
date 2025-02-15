/**
 * Modal Component Handler
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-equipment/assets/js/components/confirmation-modal.js
 *
 * Description: JavaScript handler untuk reusable modal component.
 *              Menangani show/hide, animasi, keyboard events,
 *              dan callback functions.
 *
 * API Usage:
 * WIModal.show({
 *   title: 'Konfirmasi',
 *   message: 'Yakin ingin melanjutkan?',
 *   icon: 'warning',
 *   type: 'danger',
 *   onConfirm: () => {},
 *   onCancel: () => {}
 * });
 *
 * Configuration Options:
 * - title: string            - Modal title
 * - message: string         - Modal message
 * - icon: string           - Icon type (warning/error/info/success)
 * - iconColor: string      - Custom icon color
 * - type: string          - Modal type (affects styling)
 * - size: string          - Modal size (small/medium/large)
 * - closeOnEsc: boolean   - Enable Esc to close
 * - closeOnClickOutside: boolean - Enable click outside to close
 * - buttons: object       - Custom button configuration
 *
 * Dependencies:
 * - jQuery 3.6+
 *
 * Changelog:
 * 1.0.0 - 2024-12-07
 * - Initial implementation
 * - Added core modal functionality
 * - Added accessibility features
 */
const WIModal = {
    modal: null,
    options: null,

    init() {
        this.modal = document.getElementById('confirmation-modal');
        if (!this.modal) {
            console.error('Modal element not found');
            return;
        }

        this.titleElement = document.getElementById('modal-title');
        this.messageElement = document.getElementById('modal-message');
        this.confirmBtn = document.getElementById('modal-confirm-btn');
        this.cancelBtn = document.getElementById('modal-cancel-btn');

        if (!this.titleElement || !this.messageElement || !this.confirmBtn || !this.cancelBtn) {
            console.error('Required modal elements not found');
            return;
        }

        this.bindEvents();
    },

    setContent(options) {
        if (!this.modal) return;

        // Set title dengan pengecekan null
        if (this.titleElement) {
            this.titleElement.textContent = options.title || '';
        }

        // Set message dengan pengecekan null
        if (this.messageElement) {
            this.messageElement.textContent = options.message || '';
        }

        // Set buttons dengan pengecekan null
        if (this.confirmBtn) {
            this.confirmBtn.textContent = options.confirmText || 'OK';
            this.confirmBtn.className = `button ${options.confirmClass || ''}`;
            this.confirmBtn.onclick = () => {
                if (options.onConfirm) options.onConfirm();
                this.hide();
            };
        }

        if (this.cancelBtn) {
            this.cancelBtn.textContent = options.cancelText || 'Cancel';
            this.cancelBtn.className = `button ${options.cancelClass || ''}`;
            this.cancelBtn.onclick = () => {
                if (options.onCancel) options.onCancel();
                this.hide();
            };
        }
    }
    // ... rest of the code
};

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
        if (!this.modal) return;

        this.bindEvents();
    },

    bindEvents() {
        // Close button clicks
        this.modal.querySelectorAll('[data-dismiss="modal"]')
            .forEach(button => {
                button.addEventListener('click', () => this.hide());
            });

        // ESC key press
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.options?.closeOnEsc) {
                this.hide();
            }
        });

        // Click outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal && this.options?.closeOnClickOutside) {
                this.hide();
            }
        });
    },

    show(options) {
        console.log('Modal options:', options);
        console.log('Modal element:', this.modal);
        
        this.options = {
            closeOnEsc: true,
            closeOnClickOutside: true,
            ...options
        };

        // Set modal content
        this.setContent(this.options);

        // Show modal
        this.modal.classList.add('active');

        // Focus management
        const confirmBtn = document.getElementById('modal-confirm-btn');
        if (confirmBtn) {
            confirmBtn.focus();
        }
    },

    hide() {
        this.modal.classList.remove('active');
        this.options = null;
    },

    setContent(options) {
        // Set title
        document.getElementById('modal-title').textContent = options.title || '';

        // Set message
        document.getElementById('modal-message').textContent = options.message || '';

        // Set icon if provided
        const iconElem = this.modal.querySelector('.modal-icon');
        if (options.icon) {
            iconElem.className = `modal-icon dashicons dashicons-${options.icon}`;
        }

        // Set modal type
        if (options.type) {
            this.modal.querySelector('.modal').className =
                `modal type-${options.type}`;
        }

        // Set buttons
        const confirmBtn = document.getElementById('modal-confirm-btn');
        const cancelBtn = document.getElementById('modal-cancel-btn');

        if (confirmBtn) {
            confirmBtn.textContent = options.confirmText || 'OK';
            confirmBtn.className = `button ${options.confirmClass || ''}`;
            confirmBtn.onclick = () => {
                if (options.onConfirm) options.onConfirm();
                this.hide();
            };
        }

        if (cancelBtn) {
            cancelBtn.textContent = options.cancelText || 'Cancel';
            cancelBtn.className = `button ${options.cancelClass || ''}`;
            cancelBtn.onclick = () => {
                if (options.onCancel) options.onCancel();
                this.hide();
            };
        }
    }
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => WIModal.init());

// Make it globally available
window.WIModal = WIModal;

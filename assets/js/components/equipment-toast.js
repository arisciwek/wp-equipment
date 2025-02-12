/**
 * Equipment Toast Component
 *
 * @package     WP_Equipment
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: assets/js/components/equipment-toast.js
 *
 * Description: Komponen toast notification khusus untuk manajemen equipment.
 *              Menangani feedback untuk operasi CRUD equipment.
 *              Support queue system untuk multiple notifications.
 *              Includes custom styling dan animations.
 */
 /**
  * Equipment Toast Component
  *
  * @package     WP_Equipment
  * @subpackage  Assets/JS/Components
  * @version     1.1.0
  * @author      arisciwek
  */

 const EquipmentToast = {
     container: null,
     queue: [],
     isProcessing: false,
     defaultDuration: 3000,

     init() {
         if (!this.container) {
             this.container = document.createElement('div');
             this.container.id = 'equipment-toast-container';
             this.container.style.cssText = `
                 position: fixed;
                 top: 32px;
                 right: 20px;
                 z-index: 999999;
                 display: flex;
                 flex-direction: column;
                 gap: 10px;
                 max-width: 100%;
                 pointer-events: none;
             `;
             document.body.appendChild(this.container);
         }
     },

     show(message, type = 'info', duration = this.defaultDuration) {
         this.init();

         // Allow array of messages
         const messages = Array.isArray(message) ? message : [message];

         // Add to queue
         this.queue.push({ messages, type, duration });

         if (!this.isProcessing) {
             this.processQueue();
         }
     },

     async processQueue() {
         if (this.queue.length === 0) {
             this.isProcessing = false;
             return;
         }

         this.isProcessing = true;
         const { messages, type, duration } = this.queue.shift();

         // Create toast element
         const toast = document.createElement('div');
         toast.className = `equipment-toast equipment-toast-${type}`;
         toast.style.cssText = this.getToastStyles(type);

         // Add messages
         messages.forEach(msg => {
             const p = document.createElement('p');
             p.textContent = msg;
             p.style.margin = '5px 0';
             toast.appendChild(p);
         });

         // Add close button
         const closeBtn = document.createElement('button');
         closeBtn.innerHTML = '&times;';
         closeBtn.style.cssText = `
             position: absolute;
             right: 8px;
             top: 8px;
             background: none;
             border: none;
             color: inherit;
             font-size: 18px;
             cursor: pointer;
             opacity: 0.7;
             padding: 0;
             width: 20px;
             height: 20px;
             display: flex;
             align-items: center;
             justify-content: center;
             pointer-events: auto;
         `;
         closeBtn.onclick = () => this.removeToast(toast);
         toast.appendChild(closeBtn);

         // Add to container with animation
         this.container.appendChild(toast);
         await new Promise(resolve => setTimeout(resolve, 50));
         toast.style.opacity = '1';
         toast.style.transform = 'translateX(0)';

         // Auto remove after duration
         const timeoutId = setTimeout(() => this.removeToast(toast), duration);
         toast.dataset.timeoutId = timeoutId;
     },

     async removeToast(toast) {
         if (!toast.isRemoving) {
             toast.isRemoving = true;

             // Clear timeout if exists
             if (toast.dataset.timeoutId) {
                 clearTimeout(parseInt(toast.dataset.timeoutId));
             }

             // Animate out
             toast.style.opacity = '0';
             toast.style.transform = 'translateX(100%)';

             await new Promise(resolve => setTimeout(resolve, 300));
             if (toast.parentElement) {
                 toast.parentElement.removeChild(toast);
             }

             this.processQueue();
         }
     },

     getToastStyles(type) {
         const baseStyles = `
             position: relative;
             padding: 12px 35px 12px 15px;
             border-radius: 4px;
             color: #fff;
             font-size: 14px;
             min-width: 250px;
             max-width: 400px;
             box-shadow: 0 2px 5px rgba(0,0,0,0.2);
             margin: 0;
             opacity: 0;
             transform: translateX(100%);
             transition: all 0.3s ease;
             pointer-events: auto;
         `;

         const colors = {
             success: '#00a32a', // WordPress success green
             error: '#d63638',   // WordPress error red
             warning: '#dba617', // WordPress warning yellow
             info: '#72aee6'     // WordPress info blue
         };

         return `${baseStyles}background-color: ${colors[type] || colors.info};`;
     },

     // Main notification methods
     success(message, duration) {
         this.show(message, 'success', duration);
     },

     error(message, duration) {
         this.show(message, 'error', duration);
     },

     warning(message, duration) {
         this.show(message, 'warning', duration);
     },

     info(message, duration) {
         this.show(message, 'info', duration);
     },

     // Equipment-specific message methods
     showValidationErrors(errors) {
         if (typeof errors === 'string') {
             this.error(errors);
         } else if (Array.isArray(errors)) {
             this.error(errors);
         } else if (typeof errors === 'object') {
             this.error(Object.values(errors));
         }
     },

     showSuccessWithWarnings(message, warnings) {
         // Show success first
         this.success(message);

         // Show warnings after a short delay if they exist
         if (warnings && warnings.length) {
             setTimeout(() => {
                 this.warning(warnings);
             }, 500);
         }
     },
 };

 // Expose for global use
 window.EquipmentToast = EquipmentToast;

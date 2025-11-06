/**
 * Toast Notification Component
 * casAhorro Design System
 * 
 * Queue-based toast system with:
 * - aria-live="polite" for screen readers
 * - Auto-dismiss (3-4 seconds configurable)
 * - Manual dismiss
 * - Position variants
 * - Icon support
 * 
 * Sample UI copy (es-CL):
 * - "Copiado al portapapeles"
 * - "Producto agregado a comparación"
 * - "Filtros aplicados"
 * 
 * No analytics. Respects reduced motion.
 * 
 * @package HelloElementorChild
 */

(function() {
	'use strict';
	
	/* ========================================
	   TOAST CLASS
	   ======================================== */
	
	class Toast {
		constructor(options = {}) {
			this.options = {
				message: '',
				type: 'neutral', // neutral, success, error, warning, info
				duration: 3000, // milliseconds (0 = no auto-dismiss)
				position: 'bottom-center', // bottom-center, top-center, etc.
				dismissible: true,
				icon: null,
				action: null, // { label: 'Undo', callback: fn }
				...options
			};
			
			this.element = null;
			this.container = null;
			this.dismissTimer = null;
			this.progressBar = null;
		}
		
		/**
		 * Create toast element
		 */
		create() {
			// Create toast element
			this.element = document.createElement('div');
			this.element.className = `toast toast--${this.options.type}`;
			this.element.setAttribute('role', 'status');
			this.element.setAttribute('aria-live', 'polite');
			this.element.setAttribute('aria-atomic', 'true');
			
			// Build content
			let html = '';
			
			// Icon
			if (this.options.icon) {
				html += `
					<span class="toast__icon" aria-hidden="true">
						${this.options.icon}
					</span>
				`;
			}
			
			// Message
			html += `
				<span class="toast__message">${this.escapeHtml(this.options.message)}</span>
			`;
			
			// Action button
			if (this.options.action) {
				html += `
					<button 
						type="button" 
						class="toast__action"
						aria-label="${this.escapeHtml(this.options.action.label)}"
					>
						${this.escapeHtml(this.options.action.label)}
					</button>
				`;
			}
			
			// Dismiss button
			if (this.options.dismissible) {
				html += `
					<button 
						type="button" 
						class="toast__close"
						aria-label="Cerrar notificación"
					>
						<span class="toast__close-icon" aria-hidden="true">×</span>
					</button>
				`;
			}
			
			// Progress bar (if auto-dismiss)
			if (this.options.duration > 0) {
				const slowClass = this.options.duration > 3500 ? ' toast--slow' : '';
				html += `
					<div class="toast__progress${slowClass}">
						<div class="toast__progress-bar"></div>
					</div>
				`;
			}
			
			this.element.innerHTML = html;
			
			// Setup event listeners
			this.setupEventListeners();
			
			return this.element;
		}
		
		/**
		 * Setup event listeners for toast
		 */
		setupEventListeners() {
			// Dismiss button
			const closeButton = this.element.querySelector('.toast__close');
			if (closeButton) {
				closeButton.addEventListener('click', () => {
					this.dismiss();
				});
			}
			
			// Action button
			const actionButton = this.element.querySelector('.toast__action');
			if (actionButton && this.options.action && this.options.action.callback) {
				actionButton.addEventListener('click', () => {
					this.options.action.callback();
					this.dismiss();
				});
			}
			
			// Pause auto-dismiss on hover
			if (this.options.duration > 0) {
				this.element.addEventListener('mouseenter', () => {
					this.pauseDismiss();
				});
				
				this.element.addEventListener('mouseleave', () => {
					this.resumeDismiss();
				});
			}
		}
		
		/**
		 * Show toast
		 */
		show() {
			// Get or create container
			this.container = ToastManager.getContainer(this.options.position);
			
			// Create element if not exists
			if (!this.element) {
				this.create();
			}
			
			// Add to container
			this.container.appendChild(this.element);
			
			// Trigger animation (next frame)
			requestAnimationFrame(() => {
				this.element.style.opacity = '1';
				this.element.style.transform = 'translateY(0)';
			});
			
			// Auto-dismiss
			if (this.options.duration > 0) {
				this.startDismissTimer();
			}
			
			// Dispatch event
			this.element.dispatchEvent(new CustomEvent('toast:shown', {
				detail: { toast: this }
			}));
			
			return this;
		}
		
		/**
		 * Dismiss toast
		 */
		dismiss() {
			if (!this.element || !this.element.parentNode) {
				return;
			}
			
			// Cancel auto-dismiss timer
			if (this.dismissTimer) {
				clearTimeout(this.dismissTimer);
				this.dismissTimer = null;
			}
			
			// Add exit class
			this.element.classList.add('is-exiting');
			
			// Dispatch event
			this.element.dispatchEvent(new CustomEvent('toast:dismissing', {
				detail: { toast: this }
			}));
			
			// Wait for animation
			const duration = window.CasA11y?.getSafeDuration(220) || 220;
			setTimeout(() => {
				if (this.element && this.element.parentNode) {
					this.element.parentNode.removeChild(this.element);
				}
				
				// Dispatch dismissed event
				if (this.element) {
					this.element.dispatchEvent(new CustomEvent('toast:dismissed', {
						detail: { toast: this }
					}));
				}
			}, duration);
		}
		
		/**
		 * Start auto-dismiss timer
		 */
		startDismissTimer() {
			if (this.dismissTimer) {
				clearTimeout(this.dismissTimer);
			}
			
			this.dismissTimer = setTimeout(() => {
				this.dismiss();
			}, this.options.duration);
		}
		
		/**
		 * Pause auto-dismiss (on hover)
		 */
		pauseDismiss() {
			if (this.dismissTimer) {
				clearTimeout(this.dismissTimer);
			}
			
			// Pause progress bar animation
			this.progressBar = this.element.querySelector('.toast__progress-bar');
			if (this.progressBar) {
				this.progressBar.style.animationPlayState = 'paused';
			}
		}
		
		/**
		 * Resume auto-dismiss (on mouse leave)
		 */
		resumeDismiss() {
			// Resume progress bar
			if (this.progressBar) {
				this.progressBar.style.animationPlayState = 'running';
			}
			
			// Restart timer (simplified - doesn't track exact remaining time)
			this.startDismissTimer();
		}
		
		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}
	}
	
	/* ========================================
	   TOAST MANAGER
	   ======================================== */
	
	const ToastManager = {
		containers: {},
		queue: [],
		maxVisible: 3,
		
		/**
		 * Get or create container for position
		 */
		getContainer(position = 'bottom-center') {
			if (this.containers[position]) {
				return this.containers[position];
			}
			
			const container = document.createElement('div');
			container.className = `toast-container toast-container--${position}`;
			container.setAttribute('aria-live', 'polite');
			container.setAttribute('aria-atomic', 'false');
			document.body.appendChild(container);
			
			this.containers[position] = container;
			return container;
		},
		
		/**
		 * Show toast
		 */
		show(options) {
			const toast = new Toast(options);
			
			// Check if max visible reached
			const container = this.getContainer(options.position || 'bottom-center');
			const visibleToasts = container.querySelectorAll('.toast:not(.is-exiting)');
			
			if (visibleToasts.length >= this.maxVisible) {
				// Queue it
				this.queue.push(toast);
			} else {
				// Show immediately
				toast.show();
				
				// Listen for dismiss to show queued toasts
				toast.element.addEventListener('toast:dismissed', () => {
					this.processQueue();
				});
			}
			
			return toast;
		},
		
		/**
		 * Process queued toasts
		 */
		processQueue() {
			if (this.queue.length === 0) {
				return;
			}
			
			const toast = this.queue.shift();
			toast.show();
			
			toast.element.addEventListener('toast:dismissed', () => {
				this.processQueue();
			});
		},
		
		/**
		 * Dismiss all visible toasts
		 */
		dismissAll() {
			Object.values(this.containers).forEach(container => {
				const toasts = container.querySelectorAll('.toast');
				toasts.forEach(toastElement => {
					if (toastElement._toastInstance) {
						toastElement._toastInstance.dismiss();
					}
				});
			});
			
			this.queue = [];
		}
	};
	
	/* ========================================
	   CONVENIENCE METHODS
	   ======================================== */
	
	function showToast(message, options = {}) {
		return ToastManager.show({
			message,
			...options
		});
	}
	
	function showSuccess(message, options = {}) {
		return ToastManager.show({
			message,
			type: 'success',
			icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
			...options
		});
	}
	
	function showError(message, options = {}) {
		return ToastManager.show({
			message,
			type: 'error',
			icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
			duration: 5000, // Errors persist longer
			...options
		});
	}
	
	function showWarning(message, options = {}) {
		return ToastManager.show({
			message,
			type: 'warning',
			icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
			duration: 4000,
			...options
		});
	}
	
	function showInfo(message, options = {}) {
		return ToastManager.show({
			message,
			type: 'info',
			icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
			...options
		});
	}
	
	/* ========================================
	   EXPORT API
	   ======================================== */
	
	window.CasToast = {
		Toast: Toast,
		show: showToast,
		success: showSuccess,
		error: showError,
		warning: showWarning,
		info: showInfo,
		dismissAll: () => ToastManager.dismissAll()
	};
	
})();

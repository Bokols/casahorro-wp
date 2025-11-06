/**
 * Drawer Component
 * casAhorro Design System
 * 
 * Accessible drawer/side panel with:
 * - Open/close API with custom events
 * - Escape key to close
 * - Focus trap when open
 * - Restore focus on close
 * - Scroll lock management
 * - ARIA dialog pattern
 * 
 * Sample UI copy (es-CL): "Filtros", "Cerrar filtros"
 * No analytics. Respects reduced motion.
 * Depends on: a11y.js
 * 
 * @package HelloElementorChild
 */

(function() {
	'use strict';
	
	// Check if a11y utilities are available
	if (typeof window.CasA11y === 'undefined') {
		console.error('[Drawer] CasA11y utilities not found. Load a11y.js first.');
		return;
	}
	
	/* ========================================
	   DRAWER CLASS
	   ======================================== */
	
	class Drawer {
		constructor(element, options = {}) {
			if (!element) {
				throw new Error('[Drawer] Element is required');
			}
			
			this.element = element;
			this.overlay = null;
			this.isOpen = false;
			this.focusTrap = null;
			this.focusReturn = window.CasA11y.createFocusReturn();
			this.escapeKeyCleanup = null;
			
			// Options
			this.options = {
				closeOnOverlayClick: true,
				closeOnEscape: true,
				lockScroll: true,
				setInert: true,
				restoreFocus: true,
				...options
			};
			
			this.init();
		}
		
		/**
		 * Initialize drawer
		 */
		init() {
			// Set ARIA attributes
			this.element.setAttribute('role', 'dialog');
			this.element.setAttribute('aria-modal', 'true');
			
			if (!this.element.hasAttribute('aria-labelledby') && !this.element.hasAttribute('aria-label')) {
				console.warn('[Drawer] Add aria-labelledby or aria-label for accessibility');
			}
			
			// Create or find overlay
			this.setupOverlay();
			
			// Create focus trap
			this.focusTrap = window.CasA11y.createFocusTrap(this.element);
			
			// Find and setup close buttons
			this.setupCloseButtons();
			
			// Initial state
			this.element.classList.remove('is-open');
			if (this.overlay) {
				this.overlay.classList.remove('is-open');
			}
		}
		
		/**
		 * Setup overlay element
		 */
		setupOverlay() {
			const overlayId = this.element.getAttribute('data-overlay-id');
			
			if (overlayId) {
				this.overlay = document.getElementById(overlayId);
			}
			
			if (!this.overlay) {
				// Create overlay
				this.overlay = document.createElement('div');
				this.overlay.className = 'drawer-overlay';
				this.overlay.setAttribute('aria-hidden', 'true');
				document.body.appendChild(this.overlay);
			}
			
			// Handle overlay click
			if (this.options.closeOnOverlayClick) {
				this.overlay.addEventListener('click', () => {
					this.close();
				});
			}
		}
		
		/**
		 * Setup close buttons within drawer
		 */
		setupCloseButtons() {
			const closeButtons = this.element.querySelectorAll('[data-drawer-close]');
			
			closeButtons.forEach(button => {
				// Ensure accessibility
				if (!button.hasAttribute('aria-label')) {
					button.setAttribute('aria-label', 'Cerrar');
				}
				
				button.addEventListener('click', () => {
					this.close();
				});
			});
		}
		
		/**
		 * Open drawer
		 * @param {Object} eventData - Optional data to include in custom event
		 */
		open(eventData = {}) {
			if (this.isOpen) return;
			
			// Dispatch opening event (can be cancelled)
			const openingEvent = new CustomEvent('drawer:opening', {
				detail: eventData,
				cancelable: true
			});
			this.element.dispatchEvent(openingEvent);
			
			if (openingEvent.defaultPrevented) {
				return;
			}
			
			// Save current focus
			if (this.options.restoreFocus) {
				this.focusReturn.save();
			}
			
			// Lock scroll
			if (this.options.lockScroll) {
				window.CasA11y.lockScroll();
				document.body.classList.add('drawer-open');
			}
			
			// Set inert
			if (this.options.setInert) {
				window.CasA11y.setInert(this.element);
			}
			
			// Show overlay
			if (this.overlay) {
				this.overlay.classList.add('is-open');
			}
			
			// Show drawer
			this.element.classList.add('is-open');
			
			// Activate focus trap (after transition)
			const duration = window.CasA11y.getSafeDuration(220);
			setTimeout(() => {
				if (this.focusTrap) {
					this.focusTrap.activate();
				}
			}, duration);
			
			// Setup escape key
			if (this.options.closeOnEscape) {
				this.escapeKeyCleanup = window.CasA11y.onEscapeKey(() => {
					this.close();
				});
			}
			
			this.isOpen = true;
			
			// Dispatch opened event
			this.element.dispatchEvent(new CustomEvent('drawer:opened', {
				detail: eventData
			}));
			
			// Announce to screen readers
			window.CasA11y.announce('Panel abierto', 'polite');
		}
		
		/**
		 * Close drawer
		 * @param {Object} eventData - Optional data to include in custom event
		 */
		close(eventData = {}) {
			if (!this.isOpen) return;
			
			// Dispatch closing event (can be cancelled)
			const closingEvent = new CustomEvent('drawer:closing', {
				detail: eventData,
				cancelable: true
			});
			this.element.dispatchEvent(closingEvent);
			
			if (closingEvent.defaultPrevented) {
				return;
			}
			
			// Deactivate focus trap
			if (this.focusTrap) {
				this.focusTrap.deactivate();
			}
			
			// Hide drawer
			this.element.classList.remove('is-open');
			
			// Hide overlay
			if (this.overlay) {
				this.overlay.classList.remove('is-open');
			}
			
			// Remove inert
			if (this.options.setInert) {
				window.CasA11y.removeInert();
			}
			
			// Unlock scroll
			if (this.options.lockScroll) {
				window.CasA11y.unlockScroll();
				document.body.classList.remove('drawer-open');
			}
			
			// Restore focus
			if (this.options.restoreFocus) {
				this.focusReturn.restore();
			}
			
			// Cleanup escape key listener
			if (this.escapeKeyCleanup) {
				this.escapeKeyCleanup();
				this.escapeKeyCleanup = null;
			}
			
			this.isOpen = false;
			
			// Dispatch closed event
			this.element.dispatchEvent(new CustomEvent('drawer:closed', {
				detail: eventData
			}));
			
			// Announce to screen readers
			window.CasA11y.announce('Panel cerrado', 'polite');
		}
		
		/**
		 * Toggle drawer open/close
		 */
		toggle() {
			if (this.isOpen) {
				this.close();
			} else {
				this.open();
			}
		}
		
		/**
		 * Destroy drawer instance
		 */
		destroy() {
			// Close if open
			if (this.isOpen) {
				this.close();
			}
			
			// Remove overlay if created by this instance
			if (this.overlay && !this.element.hasAttribute('data-overlay-id')) {
				this.overlay.remove();
			}
			
			// Cleanup
			if (this.focusTrap) {
				this.focusTrap.deactivate();
			}
			
			if (this.escapeKeyCleanup) {
				this.escapeKeyCleanup();
			}
		}
	}
	
	/* ========================================
	   AUTO-INITIALIZATION
	   ======================================== */
	
	/**
	 * Initialize all drawers with data-drawer attribute
	 */
	function initDrawers() {
		const drawerElements = document.querySelectorAll('[data-drawer]');
		
		drawerElements.forEach(element => {
			// Skip if already initialized
			if (element._drawerInstance) {
				return;
			}
			
			// Parse options from data attributes
			const options = {
				closeOnOverlayClick: element.getAttribute('data-close-on-overlay') !== 'false',
				closeOnEscape: element.getAttribute('data-close-on-escape') !== 'false',
				lockScroll: element.getAttribute('data-lock-scroll') !== 'false',
				setInert: element.getAttribute('data-set-inert') !== 'false',
				restoreFocus: element.getAttribute('data-restore-focus') !== 'false'
			};
			
			// Create instance
			const drawer = new Drawer(element, options);
			element._drawerInstance = drawer;
			
			// Setup trigger buttons
			const triggerId = element.id;
			if (triggerId) {
				const triggers = document.querySelectorAll(`[data-drawer-open="${triggerId}"]`);
				triggers.forEach(trigger => {
					trigger.addEventListener('click', (event) => {
						event.preventDefault();
						drawer.open();
					});
				});
			}
		});
	}
	
	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initDrawers);
	} else {
		initDrawers();
	}
	
	/* ========================================
	   EXPORT API
	   ======================================== */
	
	window.CasDrawer = {
		Drawer: Drawer,
		init: initDrawers
	};
	
})();

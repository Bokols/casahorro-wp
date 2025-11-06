/**
 * Accessibility Utilities
 * casAhorro Design System
 * 
 * Provides:
 * - Focus trap for modals/drawers
 * - Inert/scroll-lock management
 * - Return focus after dialog close
 * - Reduced motion detection
 * 
 * ARIA-compliant focus management
 * es-CL UI copy where applicable
 * No analytics. No network calls.
 * 
 * @package HelloElementorChild
 */

(function() {
	'use strict';
	
	/* ========================================
	   FOCUS TRAP
	   ======================================== */
	
	/**
	 * Create a focus trap within an element
	 * Keeps keyboard focus within dialog/drawer
	 * 
	 * @param {HTMLElement} element - Container element
	 * @return {Object} Focus trap instance with activate/deactivate methods
	 */
	function createFocusTrap(element) {
		if (!element) {
			console.warn('[A11y] Focus trap requires valid element');
			return null;
		}
		
		let previousActiveElement = null;
		let isActive = false;
		
		// Get all focusable elements within container
		function getFocusableElements() {
			const selector = [
				'a[href]',
				'button:not([disabled])',
				'input:not([disabled])',
				'select:not([disabled])',
				'textarea:not([disabled])',
				'[tabindex]:not([tabindex="-1"])',
				'[contenteditable="true"]'
			].join(', ');
			
			return Array.from(element.querySelectorAll(selector))
				.filter(el => !el.hasAttribute('inert'));
		}
		
		// Handle Tab key to trap focus
		function handleKeyDown(event) {
			if (!isActive || event.key !== 'Tab') {
				return;
			}
			
			const focusableElements = getFocusableElements();
			if (focusableElements.length === 0) {
				event.preventDefault();
				return;
			}
			
			const firstElement = focusableElements[0];
			const lastElement = focusableElements[focusableElements.length - 1];
			
			// Shift + Tab on first element → focus last
			if (event.shiftKey && document.activeElement === firstElement) {
				event.preventDefault();
				lastElement.focus();
			}
			// Tab on last element → focus first
			else if (!event.shiftKey && document.activeElement === lastElement) {
				event.preventDefault();
				firstElement.focus();
			}
		}
		
		return {
			/**
			 * Activate focus trap
			 * Saves current focus and moves to first focusable element
			 */
			activate: function() {
				if (isActive) return;
				
				// Save current focus
				previousActiveElement = document.activeElement;
				
				// Add event listener
				document.addEventListener('keydown', handleKeyDown);
				
				// Focus first element
				const focusableElements = getFocusableElements();
				if (focusableElements.length > 0) {
					focusableElements[0].focus();
				}
				
				isActive = true;
			},
			
			/**
			 * Deactivate focus trap
			 * Restores previous focus
			 */
			deactivate: function() {
				if (!isActive) return;
				
				// Remove event listener
				document.removeEventListener('keydown', handleKeyDown);
				
				// Restore previous focus
				if (previousActiveElement && previousActiveElement.focus) {
					previousActiveElement.focus();
				}
				
				isActive = false;
				previousActiveElement = null;
			},
			
			/**
			 * Check if trap is active
			 */
			isActive: function() {
				return isActive;
			}
		};
	}
	
	/* ========================================
	   INERT MANAGEMENT
	   ======================================== */
	
	/**
	 * Make elements inert (non-interactive) outside of active dialog
	 * Prevents keyboard/screen reader access to background content
	 * 
	 * @param {HTMLElement} activeElement - Element that should remain interactive
	 */
	function setInert(activeElement) {
		const rootElements = Array.from(document.body.children);
		
		rootElements.forEach(element => {
			if (element !== activeElement && !activeElement.contains(element)) {
				element.setAttribute('inert', '');
				element.setAttribute('aria-hidden', 'true');
			}
		});
	}
	
	/**
	 * Remove inert state from all elements
	 */
	function removeInert() {
		const inertElements = document.querySelectorAll('[inert]');
		inertElements.forEach(element => {
			element.removeAttribute('inert');
			element.removeAttribute('aria-hidden');
		});
	}
	
	/* ========================================
	   SCROLL LOCK
	   ======================================== */
	
	let scrollPosition = 0;
	let scrollLockCount = 0;
	
	/**
	 * Prevent body scroll (for modals/drawers)
	 * Maintains scroll position when re-enabled
	 */
	function lockScroll() {
		if (scrollLockCount === 0) {
			// Save current scroll position
			scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
			
			// Apply lock
			document.body.style.overflow = 'hidden';
			document.body.style.position = 'fixed';
			document.body.style.top = `-${scrollPosition}px`;
			document.body.style.width = '100%';
			
			// Add class for CSS targeting
			document.body.classList.add('scroll-locked');
		}
		
		scrollLockCount++;
	}
	
	/**
	 * Re-enable body scroll
	 * Restores previous scroll position
	 */
	function unlockScroll() {
		scrollLockCount = Math.max(0, scrollLockCount - 1);
		
		if (scrollLockCount === 0) {
			// Remove lock
			document.body.style.overflow = '';
			document.body.style.position = '';
			document.body.style.top = '';
			document.body.style.width = '';
			
			// Restore scroll position
			window.scrollTo(0, scrollPosition);
			
			// Remove class
			document.body.classList.remove('scroll-locked');
		}
	}
	
	/**
	 * Check if scroll is currently locked
	 */
	function isScrollLocked() {
		return scrollLockCount > 0;
	}
	
	/* ========================================
	   RETURN FOCUS
	   ======================================== */
	
	/**
	 * Save current focus and return to it later
	 * Useful for dialogs that need to restore focus on close
	 */
	function createFocusReturn() {
		let savedElement = null;
		
		return {
			/**
			 * Save current focused element
			 */
			save: function() {
				savedElement = document.activeElement;
			},
			
			/**
			 * Restore focus to saved element
			 */
			restore: function() {
				if (savedElement && savedElement.focus) {
					// Small delay to ensure DOM is ready
					setTimeout(() => {
						savedElement.focus();
					}, 10);
				}
			},
			
			/**
			 * Clear saved element
			 */
			clear: function() {
				savedElement = null;
			}
		};
	}
	
	/* ========================================
	   REDUCED MOTION DETECTION
	   ======================================== */
	
	/**
	 * Check if user prefers reduced motion
	 * @return {boolean}
	 */
	function prefersReducedMotion() {
		const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
		return mediaQuery.matches;
	}
	
	/**
	 * Execute callback when motion preference changes
	 * @param {Function} callback - Called with boolean indicating reduced motion preference
	 */
	function onMotionPreferenceChange(callback) {
		if (typeof callback !== 'function') {
			return;
		}
		
		const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
		
		// Initial call
		callback(mediaQuery.matches);
		
		// Listen for changes
		if (mediaQuery.addEventListener) {
			mediaQuery.addEventListener('change', (event) => {
				callback(event.matches);
			});
		} else if (mediaQuery.addListener) {
			// Legacy Safari support
			mediaQuery.addListener((event) => {
				callback(event.matches);
			});
		}
	}
	
	/**
	 * Get safe animation duration based on motion preference
	 * @param {number} duration - Desired duration in milliseconds
	 * @return {number} - Adjusted duration (0.01ms if reduced motion preferred)
	 */
	function getSafeDuration(duration) {
		return prefersReducedMotion() ? 0.01 : duration;
	}
	
	/* ========================================
	   KEYBOARD HELPERS
	   ======================================== */
	
	/**
	 * Handle Escape key to close dialogs
	 * @param {Function} callback - Called when Escape is pressed
	 * @return {Function} - Cleanup function to remove listener
	 */
	function onEscapeKey(callback) {
		function handleKeyDown(event) {
			if (event.key === 'Escape' || event.key === 'Esc') {
				callback(event);
			}
		}
		
		document.addEventListener('keydown', handleKeyDown);
		
		// Return cleanup function
		return function cleanup() {
			document.removeEventListener('keydown', handleKeyDown);
		};
	}
	
	/* ========================================
	   ARIA LIVE ANNOUNCER
	   ======================================== */
	
	let announcer = null;
	
	/**
	 * Announce message to screen readers
	 * Creates hidden aria-live region if needed
	 * 
	 * @param {string} message - Message to announce (es-CL)
	 * @param {string} priority - 'polite' or 'assertive'
	 */
	function announce(message, priority = 'polite') {
		if (!announcer) {
			announcer = document.createElement('div');
			announcer.setAttribute('role', 'status');
			announcer.setAttribute('aria-live', priority);
			announcer.setAttribute('aria-atomic', 'true');
			announcer.className = 'sr-only';
			announcer.style.cssText = `
				position: absolute;
				width: 1px;
				height: 1px;
				padding: 0;
				margin: -1px;
				overflow: hidden;
				clip: rect(0, 0, 0, 0);
				white-space: nowrap;
				border: 0;
			`;
			document.body.appendChild(announcer);
		}
		
		// Update priority if changed
		announcer.setAttribute('aria-live', priority);
		
		// Clear and announce
		announcer.textContent = '';
		setTimeout(() => {
			announcer.textContent = message;
		}, 100);
	}
	
	/* ========================================
	   EXPORT API
	   ======================================== */
	
	window.CasA11y = {
		// Focus management
		createFocusTrap: createFocusTrap,
		createFocusReturn: createFocusReturn,
		
		// Inert management
		setInert: setInert,
		removeInert: removeInert,
		
		// Scroll lock
		lockScroll: lockScroll,
		unlockScroll: unlockScroll,
		isScrollLocked: isScrollLocked,
		
		// Motion preferences
		prefersReducedMotion: prefersReducedMotion,
		onMotionPreferenceChange: onMotionPreferenceChange,
		getSafeDuration: getSafeDuration,
		
		// Keyboard helpers
		onEscapeKey: onEscapeKey,
		
		// Screen reader announcements
		announce: announce
	};
	
})();

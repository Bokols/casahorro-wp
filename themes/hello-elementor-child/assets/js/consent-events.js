/**
 * Consent-Aware UI Event Stubs
 * casAhorro Design System
 * 
 * CookieYes-gated event tracking for UI interactions.
 * Pre-consent: All functions are no-ops (silent).
 * Post-consent: Named event stubs fire (no network calls, no SDK).
 * 
 * UI Events:
 * - cas_ui_button_click: Button interaction
 * - cas_ui_chip_toggle: Chip selected/deselected
 * - cas_ui_chip_remove: Active filter chip removed
 * - cas_ui_fab_open: FAB (compare) opened
 * - cas_ui_drawer_open: Drawer (filters/compare) opened
 * - cas_ui_drawer_close: Drawer closed
 * - cas_ui_toast_impression: Toast notification shown
 * - cas_disclosure_view: Disclosure/legal notice viewed
 * - cas_disclosure_click: Disclosure link clicked
 * 
 * Sample UI copy (es-CL): "Aplicar filtros", "Ver comparación", "Cerrar"
 * No analytics SDKs. No fetch/XHR. Footprint: <2KB minified.
 * 
 * @package HelloElementorChild
 */

(function() {
	'use strict';
	
	/**
	 * Check if analytics consent granted via CookieYes
	 * Checks in order: CookieYes API → JSON cookie → simple cookie
	 * 
	 * @return {boolean} True if "Analíticas" category accepted
	 */
	function isAnalyticsAllowed() {
		// Method 1: CookieYes global API
		if (typeof window.CookieYes !== 'undefined' && window.CookieYes.consent) {
			return window.CookieYes.consent.analytics === true;
		}
		
		// Method 2: cookieyes-consent JSON cookie
		var cookieMatch = document.cookie.match(/cookieyes-consent=([^;]+)/);
		if (cookieMatch) {
			try {
				var consent = JSON.parse(decodeURIComponent(cookieMatch[1]));
				return consent.analytics === 'yes';
			} catch (e) {
				// Invalid JSON, continue to fallback
			}
		}
		
		// Method 3: cookieyes-analytics simple cookie
		var analyticsMatch = document.cookie.match(/cookieyes-analytics=([^;]+)/);
		if (analyticsMatch) {
			return analyticsMatch[1] === 'yes';
		}
		
		// Default: No consent
		return false;
	}
	
	/**
	 * Dispatch custom event (only if consent granted)
	 * Silent no-op before consent. No network calls. No logging.
	 * 
	 * @param {string} eventName - Event name (e.g., "cas_ui_button_click")
	 * @param {Object} data - Event payload
	 */
	function dispatchEvent(eventName, data) {
		if (!isAnalyticsAllowed()) {
			return; // Silent no-op
		}
		
		// Fire custom event on window
		var event = new CustomEvent(eventName, {
			detail: data || {},
			bubbles: true,
			cancelable: false
		});
		window.dispatchEvent(event);
	}
	
	/* ========================================
	   EVENT STUBS
	   ======================================== */
	
	/**
	 * Track button click
	 * Sample: "Aplicar filtros", "Ver oferta →"
	 * 
	 * @param {Object} params
	 * @param {string} params.label - Button label (es-CL)
	 * @param {string} params.variant - Button variant (primary, secondary, outline, ghost)
	 * @param {string} [params.location] - UI location (header, drawer, fab, etc.)
	 */
	function trackButtonClick(params) {
		dispatchEvent('cas_ui_button_click', {
			label: params.label || '',
			variant: params.variant || 'primary',
			location: params.location || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track chip toggle (select/deselect)
	 * Sample: "Refrigeradores", "Samsung"
	 * 
	 * @param {Object} params
	 * @param {string} params.label - Chip label (es-CL)
	 * @param {boolean} params.selected - New selected state
	 * @param {string} [params.category] - Chip category (brand, price, stock)
	 */
	function trackChipToggle(params) {
		dispatchEvent('cas_ui_chip_toggle', {
			label: params.label || '',
			selected: params.selected === true,
			category: params.category || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track active filter chip removal
	 * Sample: "Precio: $100.000 - $500.000"
	 * 
	 * @param {Object} params
	 * @param {string} params.label - Filter label (es-CL)
	 * @param {string} [params.type] - Filter type (category, brand, price, stock)
	 */
	function trackChipRemove(params) {
		dispatchEvent('cas_ui_chip_remove', {
			label: params.label || '',
			type: params.type || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track FAB (compare) opened
	 * Sample: "Ver comparación (3 productos)"
	 * 
	 * @param {Object} params
	 * @param {number} [params.item_count] - Number of items in FAB
	 */
	function trackFabOpen(params) {
		dispatchEvent('cas_ui_fab_open', {
			item_count: params.item_count || 0,
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track drawer opened
	 * Sample: "Filtros" drawer, "Comparar productos" drawer
	 * 
	 * @param {Object} params
	 * @param {string} params.drawer_id - Drawer identifier
	 * @param {string} [params.title] - Drawer title (es-CL)
	 */
	function trackDrawerOpen(params) {
		dispatchEvent('cas_ui_drawer_open', {
			drawer_id: params.drawer_id || '',
			title: params.title || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track drawer closed
	 * 
	 * @param {Object} params
	 * @param {string} params.drawer_id - Drawer identifier
	 * @param {string} [params.method] - Close method (button, overlay, escape)
	 */
	function trackDrawerClose(params) {
		dispatchEvent('cas_ui_drawer_close', {
			drawer_id: params.drawer_id || '',
			method: params.method || 'button',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track toast notification impression
	 * Sample: "Producto agregado a comparación", "Filtros aplicados"
	 * 
	 * @param {Object} params
	 * @param {string} params.message - Toast message (es-CL)
	 * @param {string} [params.type] - Toast type (success, error, warning, info, neutral)
	 */
	function trackToastImpression(params) {
		dispatchEvent('cas_ui_toast_impression', {
			message: params.message || '',
			type: params.type || 'neutral',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track disclosure/legal notice viewed
	 * Sample: "Precios sujetos a variación sin previo aviso"
	 * 
	 * @param {Object} params
	 * @param {string} params.disclosure_id - Disclosure identifier
	 * @param {string} [params.text] - Disclosure text snippet (es-CL)
	 */
	function trackDisclosureView(params) {
		dispatchEvent('cas_disclosure_view', {
			disclosure_id: params.disclosure_id || '',
			text: params.text || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track disclosure link clicked
	 * Sample: "Ver términos y condiciones"
	 * 
	 * @param {Object} params
	 * @param {string} params.disclosure_id - Disclosure identifier
	 * @param {string} [params.link_text] - Link text (es-CL)
	 * @param {string} [params.url] - Destination URL
	 */
	function trackDisclosureClick(params) {
		dispatchEvent('cas_disclosure_click', {
			disclosure_id: params.disclosure_id || '',
			link_text: params.link_text || '',
			url: params.url || '',
			timestamp: Date.now()
		});
	}
	
	/* ========================================
	   PLP ANALYTICS STUBS
	   ======================================== */
	
	/**
	 * Track PLP page view
	 * Fires on page load (after consent check)
	 * 
	 * @param {Object} params
	 * @param {string} [params.category] - Category slug (empty for /productos)
	 */
	function trackPlpView(params) {
		dispatchEvent('cas_plp_view', {
			category: params.category || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track filters drawer opened
	 * Fires when "Filtros" button clicked
	 */
	function trackFiltersOpen() {
		dispatchEvent('cas_filters_open', {
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track filters drawer closed
	 * Fires when drawer closed (overlay, button, escape)
	 * 
	 * @param {Object} [params]
	 * @param {string} [params.method] - Close method (overlay, button, escape)
	 */
	function trackFiltersClose(params) {
		params = params || {};
		dispatchEvent('cas_filters_close', {
			method: params.method || 'button',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track sort change
	 * Fires when sort dropdown value changes
	 * 
	 * @param {Object} params
	 * @param {string} params.orderby - Sort value (relevance, price_asc, price_desc, date_desc)
	 */
	function trackSortChange(params) {
		dispatchEvent('cas_sort_change', {
			orderby: params.orderby || '',
			timestamp: Date.now()
		});
	}
	
	/**
	 * Track product click on PLP
	 * Fires when product card clicked
	 * 
	 * @param {Object} params
	 * @param {number} params.id - Product ID
	 * @param {number} params.position - Position in grid (1-indexed)
	 * @param {string} [params.name] - Product name
	 */
	function trackPlpProductClick(params) {
		dispatchEvent('cas_plp_product_click', {
			id: params.id || 0,
			position: params.position || 0,
			name: params.name || '',
			timestamp: Date.now()
		});
	}
	
	/* ========================================
	   EXPORT API
	   ======================================== */
	
	window.CasEvents = {
		// Consent check
		isAnalyticsAllowed: isAnalyticsAllowed,
		
		// UI interaction stubs
		trackButtonClick: trackButtonClick,
		trackChipToggle: trackChipToggle,
		trackChipRemove: trackChipRemove,
		trackFabOpen: trackFabOpen,
		trackDrawerOpen: trackDrawerOpen,
		trackDrawerClose: trackDrawerClose,
		trackToastImpression: trackToastImpression,
		trackDisclosureView: trackDisclosureView,
		trackDisclosureClick: trackDisclosureClick,
		
		// PLP analytics stubs
		trackPlpView: trackPlpView,
		trackFiltersOpen: trackFiltersOpen,
		trackFiltersClose: trackFiltersClose,
		trackSortChange: trackSortChange,
		trackPlpProductClick: trackPlpProductClick
	};
	
})();

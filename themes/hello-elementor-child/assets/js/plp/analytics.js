/**
 * PLP Analytics Tracker
 * casAhorro Design System
 * 
 * Privacy-safe analytics events for Product Listing Pages.
 * All events are consent-gated, local-only (no network calls).
 * 
 * Events emitted:
 * - cas_plp_view: Page load with category slug
 * - cas_plp_product_click: Product card click with ID and position
 * 
 * Acceptance criteria:
 * - Without consent: Silent no-ops (nothing fired)
 * - With consent: Local CustomEvents only
 * - Network tab: Clean (no XHR/fetch from this file)
 * 
 * @package HelloElementorChild
 */

(function() {
	'use strict';
	
	/**
	 * Track PLP page view on load
	 * Fires once per page load, after consent check
	 */
	function trackPageView() {
		if (!window.CasEvents || !window.CasEvents.isAnalyticsAllowed()) {
			return; // Silent no-op without consent
		}
		
		// Extract category from body class
		const bodyClasses = document.body.className;
		const categoryMatch = bodyClasses.match(/tax-product_cat-([^\s]+)/);
		const category = categoryMatch ? categoryMatch[1] : '';
		
		// Fire local event
		window.CasEvents.trackPlpView({ category: category });
	}
	
	/**
	 * Track product card clicks
	 * Captures product ID and grid position
	 */
	function trackProductClicks() {
		// Wait for product grid to render
		const productGrid = document.querySelector('.plp-grid__container');
		if (!productGrid) {
			return;
		}
		
		// Get all product cards
		const productCards = productGrid.querySelectorAll('.product-card');
		
		productCards.forEach((card, index) => {
			// Extract product ID from classes (e.g., "post-123")
			const postIdMatch = card.className.match(/post-(\d+)/);
			const productId = postIdMatch ? parseInt(postIdMatch[1], 10) : 0;
			
			// Extract product name from title link
			const titleLink = card.querySelector('.product-card__title-link');
			const productName = titleLink ? titleLink.textContent.trim() : '';
			
			// Track clicks on title link
			if (titleLink) {
				titleLink.addEventListener('click', function() {
					if (!window.CasEvents || !window.CasEvents.isAnalyticsAllowed()) {
						return; // Silent no-op without consent
					}
					
					window.CasEvents.trackPlpProductClick({
						id: productId,
						position: index + 1, // 1-indexed position
						name: productName
					});
				});
			}
			
			// Track clicks on image link (if different from title)
			const imageLink = card.querySelector('.product-card__image-link');
			if (imageLink && imageLink !== titleLink) {
				imageLink.addEventListener('click', function() {
					if (!window.CasEvents || !window.CasEvents.isAnalyticsAllowed()) {
						return; // Silent no-op without consent
					}
					
					window.CasEvents.trackPlpProductClick({
						id: productId,
						position: index + 1,
						name: productName
					});
				});
			}
			
			// Track clicks on "Ver oferta" / "Agregar al carro" button
			const button = card.querySelector('.product-card__button');
			if (button) {
				button.addEventListener('click', function() {
					if (!window.CasEvents || !window.CasEvents.isAnalyticsAllowed()) {
						return; // Silent no-op without consent
					}
					
					window.CasEvents.trackPlpProductClick({
						id: productId,
						position: index + 1,
						name: productName
					});
				});
			}
		});
	}
	
	/**
	 * Initialize PLP analytics
	 * Runs after DOM ready
	 */
	function init() {
		// Track page view immediately
		trackPageView();
		
		// Set up product click tracking
		trackProductClicks();
	}
	
	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
	
})();

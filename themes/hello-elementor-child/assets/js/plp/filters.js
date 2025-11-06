/**
 * PLP Filters - Accessible Drawer with Focus Trap
 *
 * Handles drawer open/close, focus management, filter interactions,
 * URL state management, dynamic AJAX filtering, and consent-gated analytics.
 *
 * @package HelloElementorChild
 * @since 0.2.0
 */

(function () {
	'use strict';

	// Early exit if required elements don't exist
	if (!document.querySelector('#plp-filters-drawer')) {
		return;
	}

	/**
	 * Drawer state and elements
	 */
	const drawer = document.getElementById('plp-filters-drawer');
	const drawerPanel = drawer.querySelector('.plp-filters-drawer__panel');
	const drawerTitle = document.getElementById('plp-filters-title');
	const drawerForm = document.getElementById('plp-filters-form');
	const openButtons = document.querySelectorAll('[data-drawer-open]');
	const closeButtons = drawer.querySelectorAll('[data-drawer-close]');
	const clearButton = drawer.querySelector('[data-filter-clear]');
	const clearAllButtons = document.querySelectorAll('[data-filter-clear-all]');
	const sortControl = document.querySelector('[data-sort-control]');
	const productGrid = document.querySelector('.plp-grid__container');
	const toolbar = document.querySelector('.plp-toolbar__count');

	let lastFocusedElement = null;
	let focusableElements = [];
	let firstFocusableElement = null;
	let lastFocusableElement = null;
	let isFiltering = false;

	/**
	 * Price Slider State
	 */
	let priceSlider = {
		track: drawer.querySelector('.plp-price-slider__track'),
		range: drawer.querySelector('[data-slider-range]'),
		minThumb: drawer.querySelector('[data-slider-thumb="min"]'),
		maxThumb: drawer.querySelector('[data-slider-thumb="max"]'),
		minInput: drawer.querySelector('[data-price-input="min"]'),
		maxInput: drawer.querySelector('[data-price-input="max"]'),
		min: 0,
		max: 1000000,
		currentMin: 0,
		currentMax: 1000000,
		isDragging: false,
		activeThumb: null
	};

	// Initialize slider values from inputs
	if (priceSlider.minThumb && priceSlider.maxThumb) {
		priceSlider.min = parseInt(priceSlider.minThumb.getAttribute('aria-valuemin')) || 0;
		priceSlider.max = parseInt(priceSlider.minThumb.getAttribute('aria-valuemax')) || 1000000;
		priceSlider.currentMin = parseInt(priceSlider.minThumb.getAttribute('aria-valuenow')) || priceSlider.min;
		priceSlider.currentMax = parseInt(priceSlider.maxThumb.getAttribute('aria-valuenow')) || priceSlider.max;
	}

	/**
	 * Update price slider visual position
	 */
	function updateSliderPosition() {
		if (!priceSlider.track || !priceSlider.range || !priceSlider.minThumb || !priceSlider.maxThumb) {
			return;
		}

		const totalRange = priceSlider.max - priceSlider.min;
		const minPercent = ((priceSlider.currentMin - priceSlider.min) / totalRange) * 100;
		const maxPercent = ((priceSlider.currentMax - priceSlider.min) / totalRange) * 100;

		priceSlider.minThumb.style.left = minPercent + '%';
		priceSlider.maxThumb.style.left = maxPercent + '%';
		priceSlider.range.style.left = minPercent + '%';
		priceSlider.range.style.width = (maxPercent - minPercent) + '%';

		// Update ARIA values
		priceSlider.minThumb.setAttribute('aria-valuenow', priceSlider.currentMin);
		priceSlider.maxThumb.setAttribute('aria-valuenow', priceSlider.currentMax);

		// Update inputs
		if (priceSlider.minInput) {
			priceSlider.minInput.value = priceSlider.currentMin;
		}
		if (priceSlider.maxInput) {
			priceSlider.maxInput.value = priceSlider.currentMax;
		}
	}

	/**
	 * Handle slider thumb drag
	 */
	function handleSliderDrag(event) {
		if (!priceSlider.isDragging || !priceSlider.activeThumb || !priceSlider.track) {
			return;
		}

		event.preventDefault();

		const rect = priceSlider.track.getBoundingClientRect();
		const clientX = event.type.includes('touch') ? event.touches[0].clientX : event.clientX;
		const offsetX = clientX - rect.left;
		const percent = Math.max(0, Math.min(1, offsetX / rect.width));
		const value = Math.round(priceSlider.min + (percent * (priceSlider.max - priceSlider.min)));

		if (priceSlider.activeThumb === priceSlider.minThumb) {
			priceSlider.currentMin = Math.min(value, priceSlider.currentMax - 1000);
		} else {
			priceSlider.currentMax = Math.max(value, priceSlider.currentMin + 1000);
		}

		updateSliderPosition();
	}

	/**
	 * Start slider drag
	 */
	function startSliderDrag(thumb) {
		priceSlider.isDragging = true;
		priceSlider.activeThumb = thumb;
		document.body.style.userSelect = 'none';
	}

	/**
	 * End slider drag
	 */
	function endSliderDrag() {
		if (priceSlider.isDragging) {
			priceSlider.isDragging = false;
			priceSlider.activeThumb = null;
			document.body.style.userSelect = '';
			
			// Apply filters after drag ends
			applyFiltersDebounced();
		}
	}

	/**
	 * Handle keyboard navigation on slider
	 */
	function handleSliderKeyboard(event, thumb) {
		const step = event.shiftKey ? 10000 : 1000;
		let newValue;

		if (thumb === priceSlider.minThumb) {
			newValue = priceSlider.currentMin;
		} else {
			newValue = priceSlider.currentMax;
		}

		switch (event.key) {
			case 'ArrowLeft':
			case 'ArrowDown':
				event.preventDefault();
				newValue -= step;
				break;
			case 'ArrowRight':
			case 'ArrowUp':
				event.preventDefault();
				newValue += step;
				break;
			case 'Home':
				event.preventDefault();
				newValue = priceSlider.min;
				break;
			case 'End':
				event.preventDefault();
				newValue = priceSlider.max;
				break;
			default:
				return;
		}

		if (thumb === priceSlider.minThumb) {
			priceSlider.currentMin = Math.max(priceSlider.min, Math.min(newValue, priceSlider.currentMax - 1000));
		} else {
			priceSlider.currentMax = Math.min(priceSlider.max, Math.max(newValue, priceSlider.currentMin + 1000));
		}

		updateSliderPosition();
		applyFiltersDebounced();
	}

	/**
	 * Apply filters dynamically via AJAX
	 */
	function applyFilters() {
		if (isFiltering) {
			return;
		}

		isFiltering = true;

		// Build filter data from form
		const formData = new FormData(drawerForm);
		const params = new URLSearchParams();

		// Add all form fields
		for (const [key, value] of formData.entries()) {
			if (value) {
				params.append(key, value);
			}
		}

		// Update URL without reload
		const newUrl = new URL(window.location.href);
		const baseUrl = newUrl.origin + newUrl.pathname;
		newUrl.search = params.toString();
		window.history.pushState({}, '', newUrl);

		// Show loading state
		if (productGrid) {
			productGrid.style.opacity = '0.5';
			productGrid.style.pointerEvents = 'none';
		}

		// Make AJAX request
		fetch(baseUrl + '?' + params.toString(), {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(response => response.text())
		.then(html => {
			const parser = new DOMParser();
			const doc = parser.parseFromString(html, 'text/html');

			// Update product grid
			const newGrid = doc.querySelector('.plp-grid__container');
			if (productGrid && newGrid) {
				productGrid.innerHTML = newGrid.innerHTML;
			}

			// Update toolbar count
			const newToolbar = doc.querySelector('.plp-toolbar__count');
			if (toolbar && newToolbar) {
				toolbar.innerHTML = newToolbar.innerHTML;
			}

			// Update pagination
			const currentPagination = document.querySelector('.plp-pagination');
			const newPagination = doc.querySelector('.plp-pagination');
			if (currentPagination && newPagination) {
				currentPagination.innerHTML = newPagination.innerHTML;
			}

			// Update filter counts dynamically
			updateFilterCounts(doc);

			// Update summary chips
			const currentSummary = document.querySelector('.plp-filter-summary');
			const newSummary = doc.querySelector('.plp-filter-summary');
			if (newSummary) {
				if (currentSummary) {
					currentSummary.innerHTML = newSummary.innerHTML;
					// Re-attach chip listeners
					document.querySelectorAll('.plp-summary-chip:not(.plp-summary-chip--clear-all)').forEach(chip => {
						chip.addEventListener('click', removeSummaryChip);
					});
				} else {
					// Insert summary chips if they don't exist
					const parentChips = document.querySelector('.plp-parent-chips');
					if (parentChips) {
						parentChips.insertAdjacentHTML('afterend', newSummary.outerHTML);
					}
				}
			} else if (currentSummary) {
				currentSummary.remove();
			}

			// Restore grid state
			if (productGrid) {
				productGrid.style.opacity = '1';
				productGrid.style.pointerEvents = '';
			}

			// Emit filters:changed event for downstream listeners
			const changedEvent = new CustomEvent('filters:changed', {
				bubbles: true,
				detail: { params: params.toString() }
			});
			document.dispatchEvent(changedEvent);

			// Track filter application
			trackFilterApply();

			isFiltering = false;
		})
		.catch(error => {
			console.error('Filter error:', error);
			
			// Fallback to page reload
			window.location.href = newUrl;
			
			isFiltering = false;
		});
	}

	/**
	 * Update filter option counts based on current selection
	 */
	function updateFilterCounts(doc) {
		// Update all count elements
		const newDrawer = doc.querySelector('#plp-filters-drawer');
		if (!newDrawer) return;

		// Update brand counts
		const newBrands = newDrawer.querySelectorAll('[data-count-for^="marca-"]');
		newBrands.forEach(newCount => {
			const countFor = newCount.getAttribute('data-count-for');
			const currentCount = drawer.querySelector(`[data-count-for="${countFor}"]`);
			if (currentCount) {
				currentCount.textContent = newCount.textContent;
				
				// Disable checkbox if count is 0
				const checkbox = currentCount.closest('.plp-filter-checkbox')?.querySelector('input[type="checkbox"]');
				const count = parseInt(newCount.textContent.replace(/[^\d]/g, ''));
				if (checkbox && !checkbox.checked) {
					checkbox.disabled = count === 0;
					if (count === 0) {
						checkbox.closest('.plp-filter-checkbox')?.style.setProperty('opacity', '0.5');
					} else {
						checkbox.closest('.plp-filter-checkbox')?.style.removeProperty('opacity');
					}
				}
			}
		});

		// Update attribute counts
		drawer.querySelectorAll('[data-count-for]').forEach(currentCount => {
			const countFor = currentCount.getAttribute('data-count-for');
			if (!countFor.startsWith('marca-')) {
				const newCount = newDrawer.querySelector(`[data-count-for="${countFor}"]`);
				if (newCount) {
					currentCount.textContent = newCount.textContent;
					
					// Disable checkbox if count is 0
					const checkbox = currentCount.closest('.plp-filter-checkbox')?.querySelector('input[type="checkbox"]');
					const count = parseInt(newCount.textContent.replace(/[^\d]/g, ''));
					if (checkbox && !checkbox.checked) {
						checkbox.disabled = count === 0;
						if (count === 0) {
							checkbox.closest('.plp-filter-checkbox')?.style.setProperty('opacity', '0.5');
						} else {
							checkbox.closest('.plp-filter-checkbox')?.style.removeProperty('opacity');
						}
					}
				}
			}
		});

		// Update subcategory counts
		const newSubcategories = newDrawer.querySelectorAll('.plp-filter-section:not(.plp-filter-section--collapsible) .plp-filter-checkbox__count');
		const currentSubcategories = drawer.querySelectorAll('.plp-filter-section:not(.plp-filter-section--collapsible) .plp-filter-checkbox__count');
		newSubcategories.forEach((newCount, index) => {
			if (currentSubcategories[index]) {
				currentSubcategories[index].textContent = newCount.textContent;
			}
		});
	}

	/**
	 * Toggle collapsible filter section
	 */
	function toggleFilterSection(button) {
		const isExpanded = button.getAttribute('aria-expanded') === 'true';
		const targetId = button.getAttribute('data-filter-toggle');
		const content = drawer.querySelector(`[data-filter-content="${targetId}"]`);

		if (!content) return;

		if (isExpanded) {
			button.setAttribute('aria-expanded', 'false');
			content.hidden = true;
		} else {
			button.setAttribute('aria-expanded', 'true');
			content.hidden = false;
		}
	}

	/**
	 * Debounced apply filters
	 */
	const applyFiltersDebounced = debounce(applyFilters, 500);

	/**
	 * Get all focusable elements within the drawer
	 */
	function updateFocusableElements() {
		focusableElements = Array.from(
			drawerPanel.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)
		);
		firstFocusableElement = focusableElements[0];
		lastFocusableElement = focusableElements[focusableElements.length - 1];
	}

	/**
	 * Open drawer with focus trap
	 */
	function openDrawer() {
		lastFocusedElement = document.activeElement;

		drawer.removeAttribute('hidden');
		drawer.setAttribute('aria-hidden', 'false');

		// Update trigger button state
		openButtons.forEach(button => {
			button.setAttribute('aria-expanded', 'true');
		});

		updateFocusableElements();

		// Focus first interactive element or title
		requestAnimationFrame(() => {
			const firstInput = drawer.querySelector('input, select, button');
			if (firstInput) {
				firstInput.focus();
			} else if (drawerTitle) {
				drawerTitle.focus();
			}
		});

		// Don't prevent body scroll - allow background scrolling
		// document.body.style.overflow = 'hidden';

		// Track analytics (consent-gated)
		trackDrawerOpen();
	}

	/**
	 * Close drawer and restore focus
	 */
	function closeDrawer() {
		drawer.setAttribute('aria-hidden', 'true');

		// Update trigger button state
		openButtons.forEach(button => {
			button.setAttribute('aria-expanded', 'false');
		});

		// Wait for transition, then hide
		setTimeout(() => {
			if (drawer.getAttribute('aria-hidden') === 'true') {
				drawer.setAttribute('hidden', '');
			}
		}, 300);

		// Restore focus
		if (lastFocusedElement) {
			lastFocusedElement.focus();
		}

		// Restore body scroll (no longer preventing it)
		// document.body.style.overflow = '';
		
		// Track drawer close (local event only)
		trackDrawerClose();
	}

	/**
	 * Focus trap handler
	 */
	function trapFocus(event) {
		if (event.key !== 'Tab' || drawer.getAttribute('aria-hidden') === 'true') {
			return;
		}

		if (event.shiftKey) {
			// Shift + Tab (backwards)
			if (document.activeElement === firstFocusableElement) {
				event.preventDefault();
				lastFocusableElement.focus();
			}
		} else {
			// Tab (forwards)
			if (document.activeElement === lastFocusableElement) {
				event.preventDefault();
				firstFocusableElement.focus();
			}
		}
	}

	/**
	 * Handle Escape key to close drawer
	 */
	function handleEscape(event) {
		if (event.key === 'Escape' && drawer.getAttribute('aria-hidden') === 'false') {
			closeDrawer();
		}
	}

	/**
	 * Clear all filters - Single Source of Truth
	 * 
	 * Used by:
	 * - Toolbar "Limpiar todos" chip (.plp-summary-chip--clear-all)
	 * - Drawer "Limpiar todos los filtros" button ([data-filter-clear-all])
	 * - Empty state button ([data-filter-clear-all])
	 * 
	 * Ensures:
	 * - All inputs unchecked/cleared
	 * - Active chips removed
	 * - Counts/badges reset
	 * - Drawer closed if open
	 * - Pagination/sort reset
	 * - URL params stripped
	 * - filters:reset event emitted
	 * - Grid refreshed
	 */
	function clearAllFilters(event) {
		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}

		// 1. Uncheck all filter inputs
		drawer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
			checkbox.checked = false;
			checkbox.disabled = false; // Re-enable all options
			const filterCheckbox = checkbox.closest('.plp-filter-checkbox');
			if (filterCheckbox) {
				filterCheckbox.style.removeProperty('opacity');
			}
		});

		// 2. Clear price slider inputs
		drawer.querySelectorAll('input[type="number"]').forEach(input => {
			input.value = '';
		});

		// Reset price slider visual state
		if (priceSlider.minThumb && priceSlider.maxThumb) {
			priceSlider.currentMin = priceSlider.min;
			priceSlider.currentMax = priceSlider.max;
			updateSliderPosition();
		}

		// 3. Remove all filter summary chips from DOM
		const summaryContainer = document.querySelector('.plp-filter-summary');
		if (summaryContainer) {
			summaryContainer.remove();
		}

		// 4. Close drawer if open
		if (drawer.getAttribute('aria-hidden') === 'false') {
			closeDrawer();
		}

		// 5. Build clean URL (strip all filter params)
		const baseUrl = window.location.pathname;
		const url = new URL(baseUrl, window.location.origin);
		
		// Preserve non-filter params (search, orderby if default)
		const currentParams = new URLSearchParams(window.location.search);
		const preserveParams = ['s', 'post_type']; // Search query, post type
		
		preserveParams.forEach(param => {
			if (currentParams.has(param)) {
				url.searchParams.set(param, currentParams.get(param));
			}
		});

		// 6. Update URL without reload (reset pagination, sort)
		window.history.pushState({}, '', url.toString());

		// 7. Emit filters:reset event for downstream listeners
		const resetEvent = new CustomEvent('filters:reset', {
			bubbles: true,
			detail: { timestamp: Date.now() }
		});
		document.dispatchEvent(resetEvent);

		// 8. Update disabled states for both clear all triggers
		updateClearAllState();

		// 9. Track analytics (consent-gated)
		if (window.CasEvents && window.CasEvents.trackFiltersClear) {
			window.CasEvents.trackFiltersClear();
		}

		// 10. Refresh product grid - navigate to clean URL
		window.location.href = url.toString();
	}

	/**
	 * Update disabled state for "Clear all" buttons/chips
	 * 
	 * Checks if any filters are active:
	 * - Checked checkboxes
	 * - Price range set
	 * - URL has filter params
	 * 
	 * Disables/enables all [data-filter-clear-all] and .plp-summary-chip--clear-all
	 */
	function updateClearAllState() {
		const hasActiveFilters = checkIfFiltersActive();
		
		// Update all clear all triggers
		const clearAllTriggers = document.querySelectorAll('[data-filter-clear-all], .plp-summary-chip--clear-all');
		
		clearAllTriggers.forEach(trigger => {
			if (hasActiveFilters) {
				trigger.removeAttribute('disabled');
				trigger.removeAttribute('aria-disabled');
				trigger.style.pointerEvents = '';
				trigger.style.opacity = '';
			} else {
				trigger.setAttribute('disabled', 'disabled');
				trigger.setAttribute('aria-disabled', 'true');
				trigger.style.pointerEvents = 'none';
				trigger.style.opacity = '0.5';
			}
		});
	}

	/**
	 * Check if any filters are currently active
	 * 
	 * @returns {boolean} True if any filter is active
	 */
	function checkIfFiltersActive() {
		// Check for checked checkboxes
		const hasCheckedBoxes = drawer.querySelector('input[type="checkbox"]:checked') !== null;
		
		// Check for price range
		const minPriceInput = drawer.querySelector('[data-price-input="min"]');
		const maxPriceInput = drawer.querySelector('[data-price-input="max"]');
		const hasPriceRange = (minPriceInput && minPriceInput.value) || (maxPriceInput && maxPriceInput.value);
		
		// Check URL params (subcategory[], marca[], pa_*, min_price, max_price, in_stock)
		const url = new URLSearchParams(window.location.search);
		const filterParams = [
			'subcategory[]', 'marca[]', 'min_price', 'max_price', 'in_stock'
		];
		
		const hasUrlParams = Array.from(url.keys()).some(key => {
			return filterParams.includes(key) || 
				   key.startsWith('pa_') || 
				   key.endsWith('[]');
		});
		
		return hasCheckedBoxes || hasPriceRange || hasUrlParams;
	}

	/**
	 * Remove individual filter chip
	 */
	function removeSummaryChip(event) {
		const chip = event.target.closest('.plp-summary-chip:not(.plp-summary-chip--clear-all)');
		if (!chip) return;

		event.preventDefault();
		event.stopPropagation();

		const param = chip.getAttribute('data-filter-param');
		const value = chip.getAttribute('data-filter-value');

		if (!param || !value) return;

		const url = new URL(window.location.href);

		// Handle different filter types
		if (param === 'price') {
			url.searchParams.delete('min_price');
			url.searchParams.delete('max_price');
		} else if (param === 'in_stock') {
			url.searchParams.delete('in_stock');
		} else {
			// Handle array parameters (subcategory, marca, pa_*)
			const existingValues = url.searchParams.getAll(param + '[]');
			url.searchParams.delete(param + '[]');

			existingValues.forEach(existingValue => {
				if (existingValue !== value) {
					url.searchParams.append(param + '[]', existingValue);
				}
			});
		}

		// Track removal (consent-gated)
		trackFilterRemove(param, value);

		// Navigate to updated URL
		window.location.href = url.toString();
	}

	/**
	 * Handle sort dropdown change
	 */
	function handleSortChange(event) {
		const orderby = event.target.value;
		const url = new URL(window.location.href);

		url.searchParams.set('orderby', orderby);
		url.searchParams.delete('paged'); // Reset to page 1

		// Track sort (consent-gated)
		trackSort(orderby);

		window.location.href = url.toString();
	}

	/**
	 * Debounce helper for INP optimization
	 */
	function debounce(func, wait) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}

	/**
	 * Analytics tracking (consent-gated, local events only)
	 */
	function trackDrawerOpen() {
		// Use local event stub (no network call)
		if (window.CasEvents && window.CasEvents.trackFiltersOpen) {
			window.CasEvents.trackFiltersOpen();
		}
	}

	function trackDrawerClose() {
		// Use local event stub (no network call)
		if (window.CasEvents && window.CasEvents.trackFiltersClose) {
			window.CasEvents.trackFiltersClose({ method: 'button' });
		}
	}

	function trackFilterApply() {
		// Use local event stub (no network call)
		if (window.CasEvents && window.CasEvents.trackFilterApply) {
			window.CasEvents.trackFilterApply();
		}
	}

	function trackFilterRemove(param, value) {
		// Use local event stub (no network call)
		if (window.CasEvents && window.CasEvents.trackFilterRemove) {
			window.CasEvents.trackFilterRemove({ param: param, value: value });
		}
	}

	function trackSort(orderby) {
		// Use local event stub (no network call)
		if (window.CasEvents && window.CasEvents.trackSortChange) {
			window.CasEvents.trackSortChange({ orderby: orderby });
		}
	}

	/**
	 * Initialize event listeners
	 */
	function init() {
		// Open drawer
		openButtons.forEach(button => {
			button.addEventListener('click', openDrawer);
		});

		// Close drawer
		closeButtons.forEach(button => {
			button.addEventListener('click', closeDrawer);
		});

		// Clear all filters - Delegated event handling
		// Handles both toolbar chip and drawer/empty state buttons
		// Survives AJAX DOM swaps (attached to document)
		document.addEventListener('click', function(event) {
			const clearAllTrigger = event.target.closest('[data-filter-clear-all], .plp-summary-chip--clear-all');
			if (clearAllTrigger) {
				// Check if disabled
				if (clearAllTrigger.hasAttribute('disabled') || 
					clearAllTrigger.getAttribute('aria-disabled') === 'true') {
					event.preventDefault();
					return;
				}
				clearAllFilters(event);
			}
		});

		// Keyboard support for clear all triggers (Enter/Space)
		document.addEventListener('keydown', function(event) {
			if (event.key !== 'Enter' && event.key !== ' ') {
				return;
			}

			const clearAllTrigger = event.target.closest('[data-filter-clear-all], .plp-summary-chip--clear-all');
			if (clearAllTrigger) {
				// Check if disabled
				if (clearAllTrigger.hasAttribute('disabled') || 
					clearAllTrigger.getAttribute('aria-disabled') === 'true') {
					event.preventDefault();
					return;
				}
				clearAllFilters(event);
			}
		});

		// Legacy clear button (if exists, deprecated)
		if (clearButton) {
			clearButton.addEventListener('click', clearAllFilters);
		}

		// Remove summary chips - Delegated
		document.addEventListener('click', function(event) {
			const chip = event.target.closest('.plp-summary-chip:not(.plp-summary-chip--clear-all)');
			if (chip) {
				removeSummaryChip(event);
			}
		});

		// Sort control
		if (sortControl) {
			sortControl.addEventListener('change', handleSortChange);
		}

		// Keyboard handlers
		document.addEventListener('keydown', handleEscape);
		drawer.addEventListener('keydown', trapFocus);

		// Backdrop click to close
		const backdrop = drawer.querySelector('.plp-filters-drawer__backdrop');
		if (backdrop) {
			backdrop.addEventListener('click', closeDrawer);
		}

		// Collapsible filter sections
		drawer.querySelectorAll('[data-filter-toggle]').forEach(button => {
			button.addEventListener('click', function() {
				toggleFilterSection(this);
			});
		});

		// Price slider initialization
		if (priceSlider.minThumb && priceSlider.maxThumb) {
			updateSliderPosition();

			// Mouse/touch events for thumbs
			priceSlider.minThumb.addEventListener('mousedown', () => startSliderDrag(priceSlider.minThumb));
			priceSlider.minThumb.addEventListener('touchstart', () => startSliderDrag(priceSlider.minThumb));
			priceSlider.maxThumb.addEventListener('mousedown', () => startSliderDrag(priceSlider.maxThumb));
			priceSlider.maxThumb.addEventListener('touchstart', () => startSliderDrag(priceSlider.maxThumb));

			// Keyboard navigation
			priceSlider.minThumb.addEventListener('keydown', (e) => handleSliderKeyboard(e, priceSlider.minThumb));
			priceSlider.maxThumb.addEventListener('keydown', (e) => handleSliderKeyboard(e, priceSlider.maxThumb));

			// Global drag handlers
			document.addEventListener('mousemove', handleSliderDrag);
			document.addEventListener('touchmove', handleSliderDrag, { passive: false });
			document.addEventListener('mouseup', endSliderDrag);
			document.addEventListener('touchend', endSliderDrag);
		}

		// Price input changes
		if (priceSlider.minInput) {
			priceSlider.minInput.addEventListener('change', function() {
				priceSlider.currentMin = parseInt(this.value) || priceSlider.min;
				updateSliderPosition();
				applyFiltersDebounced();
			});
		}
		if (priceSlider.maxInput) {
			priceSlider.maxInput.addEventListener('change', function() {
				priceSlider.currentMax = parseInt(this.value) || priceSlider.max;
				updateSliderPosition();
				applyFiltersDebounced();
			});
		}

		// Dynamic filter changes (checkboxes, toggles)
		drawer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
			checkbox.addEventListener('change', function() {
				applyFiltersDebounced();
				// Update clear all state when filters change
				setTimeout(updateClearAllState, 600); // After debounce + AJAX
			});
		});

		// Form submission (apply filters button)
		if (drawerForm) {
			drawerForm.addEventListener('submit', function (event) {
				event.preventDefault();
				applyFilters();
				closeDrawer();
			});
		}

		// Initialize clear all button states on page load
		updateClearAllState();

		// Update clear all state after AJAX filter updates
		document.addEventListener('filters:changed', function() {
			updateClearAllState();
		});
	}

	/**
	 * Initialize on DOM ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

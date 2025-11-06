/**
 * Home Hero - Ribbon Detection Utility
 * 
 * Adds .has-ribbon class to <body> when ribbon is visible (>640px)
 * Ensures hero section maintains correct top padding/clearance
 * Prevents CLS when ribbon appears/disappears on resize
 * 
 * Performance: <1KB, runs once on load + throttled resize
 * Target: INP ≤200ms (non-blocking, lightweight)
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * Check if ribbon element exists and is visible
     * @returns {boolean} True if ribbon is present and visible
     */
    function isRibbonVisible() {
        // Look for ribbon element (adjust selector to match your ribbon)
        const ribbon = document.querySelector('.cas-ribbon, .site-ribbon, [class*="ribbon"]');
        
        if (!ribbon) {
            return false; // No ribbon element found
        }

        // Check if ribbon is visible (not display:none, not hidden)
        const styles = window.getComputedStyle(ribbon);
        const isHidden = styles.display === 'none' || 
                        styles.visibility === 'hidden' ||
                        styles.opacity === '0' ||
                        ribbon.offsetHeight === 0;

        // Ribbon is visible if viewport width >640px AND element is visible
        const isWideViewport = window.innerWidth > 640;

        return isWideViewport && !isHidden;
    }

    /**
     * Update body class based on ribbon visibility
     * Adds/removes .has-ribbon class
     */
    function updateRibbonClass() {
        const hasRibbon = isRibbonVisible();

        if (hasRibbon) {
            document.body.classList.add('has-ribbon');
        } else {
            document.body.classList.remove('has-ribbon');
        }

        // Debug log (development only)
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('localwp')) {
            console.log('[Hero] Ribbon detection:', {
                visible: hasRibbon,
                viewport: window.innerWidth + 'px',
                bodyClass: document.body.classList.contains('has-ribbon')
            });
        }
    }

    /**
     * Throttled resize handler
     * Prevents excessive recalculations during resize
     */
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateRibbonClass, 150); // 150ms throttle
    }

    /**
     * Initialize ribbon detection
     * Runs on DOMContentLoaded and resize
     */
    function init() {
        // Initial check
        updateRibbonClass();

        // Re-check on window resize (throttled)
        window.addEventListener('resize', handleResize);

        // Re-check on orientation change (mobile)
        window.addEventListener('orientationchange', function() {
            setTimeout(updateRibbonClass, 100);
        });

        // Optional: Re-check if ribbon element loads late (AJAX, Elementor)
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                // Only re-check if ribbon-related elements changed
                const ribbonChanged = mutations.some(function(mutation) {
                    return Array.from(mutation.addedNodes).some(function(node) {
                        return node.nodeType === 1 && 
                               (node.matches('.cas-ribbon, .site-ribbon, [class*="ribbon"]') ||
                                node.querySelector('.cas-ribbon, .site-ribbon, [class*="ribbon"]'));
                    });
                });

                if (ribbonChanged) {
                    updateRibbonClass();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        // Debug log
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('localwp')) {
            console.log('[Hero] Ribbon detector initialized');
        }
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already loaded
        init();
    }

})();

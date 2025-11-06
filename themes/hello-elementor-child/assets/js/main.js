/**
 * Main JavaScript for Hello Elementor Child theme
 * Minimal, non-invasive stub. Loaded deferred.
 *
 * Keep this file small; add additional modules under assets/js/ as needed.
 */

( function () {
    'use strict';

    // Feature-detect: only run in browsers
    if ( typeof window === 'undefined' ) {
        return;
    }

    // Example: add a class when JS is available
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');

    // Placeholder for DOM-ready actions
    function onReady( fn ) {
        if ( document.readyState !== 'loading' ) {
            fn();
        } else {
            document.addEventListener( 'DOMContentLoaded', fn );
        }
    }

    /**
     * Sticky Toolbar Height Calculation
     * Sets CSS variables for header/ribbon/admin-bar heights
     * to enable proper sticky positioning without overlaps
     */
    function initStickyToolbar() {
        var root = document.documentElement;
        var headerWrapper = document.querySelector('.header-wrapper');
        var ribbon = document.querySelector('.cas-ribbon');
        var adminBar = document.querySelector('#wpadminbar');

        // Guard: skip if critical elements missing
        if (!headerWrapper) {
            return;
        }

        /**
         * Measure and set CSS variables
         */
        function updateHeightVariables() {
            // Admin bar height (WordPress)
            var adminBarHeight = 0;
            if (adminBar && window.getComputedStyle(adminBar).display !== 'none') {
                adminBarHeight = adminBar.offsetHeight;
            }
            root.style.setProperty('--wp-admin-bar-height', adminBarHeight + 'px');

            // Header wrapper height
            var headerHeight = headerWrapper.offsetHeight;
            root.style.setProperty('--site-header-height', headerHeight + 'px');

            // Ribbon height (if present)
            var ribbonHeight = 0;
            if (ribbon && window.getComputedStyle(ribbon).display !== 'none') {
                ribbonHeight = ribbon.offsetHeight;
            }
            root.style.setProperty('--ribbon-height', ribbonHeight + 'px');
        }

        // Initial measurement - run ASAP to prevent layout shift
        updateHeightVariables();

        // Use ResizeObserver for dynamic height changes (preferred)
        if ('ResizeObserver' in window) {
            var resizeObserver = new ResizeObserver(function(entries) {
                // Throttle updates using requestAnimationFrame
                if (!updateHeightVariables.ticking) {
                    window.requestAnimationFrame(function() {
                        updateHeightVariables();
                        updateHeightVariables.ticking = false;
                    });
                    updateHeightVariables.ticking = true;
                }
            });

            // Observe header for height changes
            resizeObserver.observe(headerWrapper);

            // Observe ribbon if present
            if (ribbon) {
                resizeObserver.observe(ribbon);
            }

            // Observe admin bar if present
            if (adminBar) {
                resizeObserver.observe(adminBar);
            }
        } else {
            // Fallback: throttled window resize listener
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(updateHeightVariables, 150);
            });
        }

        // Also update on orientationchange (mobile)
        window.addEventListener('orientationchange', function() {
            setTimeout(updateHeightVariables, 100);
        });
    }

    onReady( function () {
        // Initialize sticky toolbar height calculation
        initStickyToolbar();
    } );
} )();

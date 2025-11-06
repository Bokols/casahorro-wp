/* Header toggler (vanilla JS)
 * - Toggles mobile menu open/close
 * - Updates aria-expanded on the toggle button
 * - Adds/removes `.is-open` class on header and primary nav
 * - Closes on Escape and on outside click
 * - Makes header sticky on scroll
 */
(function () {
    'use strict';

    function el(id) {
        return document.getElementById(id);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = el('cas-menu-toggle');
        var nav = el('cas-nav-primary');
        var header = el('cas-header');
        var headerWrapper = document.querySelector('.header-wrapper');

        if (!toggle || !nav || !header) {
            return;
        }

        var OPEN_CLASS = 'is-open';
        var SCROLLED_CLASS = 'is-scrolled';
        var scrollThreshold = 10; // pixels scrolled before sticky

        function isOpen() {
            return toggle.getAttribute('aria-expanded') === 'true';
        }

        function open() {
            toggle.setAttribute('aria-expanded', 'true');
            header.classList.add(OPEN_CLASS);
            nav.classList.add(OPEN_CLASS);
        }

        function close() {
            toggle.setAttribute('aria-expanded', 'false');
            header.classList.remove(OPEN_CLASS);
            nav.classList.remove(OPEN_CLASS);
        }

        toggle.addEventListener('click', function (ev) {
            ev.preventDefault();
            (isOpen() ? close : open)();
        });

        // Close with Escape and return focus to toggle button
        document.addEventListener('keydown', function (ev) {
            if ((ev.key === 'Escape' || ev.key === 'Esc') && isOpen()) {
                close();
                toggle.focus(); // Return focus to hamburger button
            }
        });

        // Close when clicking outside the header while open
        document.addEventListener('click', function (ev) {
            if (!isOpen()) return;
            if (!header.contains(ev.target)) {
                close();
            }
        });

        // Sticky header on scroll
        if (headerWrapper) {
            var lastScroll = 0;
            
            function handleScroll() {
                var currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                if (currentScroll > scrollThreshold) {
                    headerWrapper.classList.add(SCROLLED_CLASS);
                } else {
                    headerWrapper.classList.remove(SCROLLED_CLASS);
                }
                
                lastScroll = currentScroll;
            }

            // Throttle scroll handler for performance
            var ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        handleScroll();
                        ticking = false;
                    });
                    ticking = true;
                }
            });
            
            // Check initial state
            handleScroll();
        }
    });
})();

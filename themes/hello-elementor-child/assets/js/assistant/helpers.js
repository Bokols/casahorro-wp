/**
 * Assistant Helpers - Shared Utilities
 * 
 * Responsibilities:
 * - Consent checking (CookieYes analytics gate)
 * - Shared helper functions
 * 
 * Dependencies: None
 * Exports: analyticsEnabled()
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';

    window.casAssistantHelpers = {
        /**
         * Check if analytics consent granted via CookieYes
         * 
         * Checks in order:
         * 1. CookieYes global API (window.CookieYes.consent.analytics)
         * 2. cookieyes-consent JSON cookie
         * 3. cookieyes-analytics simple cookie
         * 
         * @return {boolean} True if "Analíticas" category accepted
         */
        analyticsEnabled: function() {
            // Method 1: CookieYes global API
            if (typeof window.CookieYes !== 'undefined' && window.CookieYes.consent) {
                return window.CookieYes.consent.analytics === true;
            }
            
            // Method 2: cookieyes-consent JSON cookie
            const cookieMatch = document.cookie.match(/cookieyes-consent=([^;]+)/);
            if (cookieMatch) {
                try {
                    const consent = JSON.parse(decodeURIComponent(cookieMatch[1]));
                    return consent.analytics === 'yes';
                } catch (e) {
                    // Invalid JSON, continue to fallback
                }
            }
            
            // Method 3: cookieyes-analytics simple cookie
            const analyticsMatch = document.cookie.match(/cookieyes-analytics=([^;]+)/);
            if (analyticsMatch) {
                return analyticsMatch[1] === 'yes';
            }
            
            // Default: No consent
            return false;
        }
    };

})();

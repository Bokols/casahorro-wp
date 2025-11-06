/**
 * Assistant Boot - Initialization & Public API
 * 
 * Responsibilities:
 * - Initialize all assistant modules (Core, UI, API)
 * - Wire up event listeners between modules
 * - Expose public API (window.casAssistant.open/close)
 * - Handle .js-open-assistant click events
 * - Consent-gated analytics tracking
 * - Graceful degradation if modules missing
 * 
 * Dependencies: Core, UI, API, CasEvents (consent)
 * Load Order: core.js → ui.js → api.js → boot.js
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Early guard to prevent re-init
    if (window.__casAssistantBooted) {
        console.log('[Assistant Boot] skip re-init');
        return;
    }
    window.__casAssistantBooted = true;

    function removeClassicUiTags() {
        document.querySelectorAll('script[src*="/assets/js/assistant/ui.js"]').forEach(s => {
            if ((s.type || s.getAttribute('type')) !== 'module') {
                s.parentNode.removeChild(s);
            }
        });
    }

    async function ensureUiLoaded() {
        if (window.casAssistantUI?.open) return true;
        removeClassicUiTags();
        const base =
            (typeof window.casThemeAssistantBase === 'string' && window.casThemeAssistantBase) ||
            (typeof import.meta !== 'undefined' && import.meta.url
                ? new URL('./', import.meta.url).toString()
                : (document.currentScript?.src.replace(/\/boot\.js.*/, '/') ||
                   '/wp-content/themes/hello-elementor-child/assets/js/assistant/'));
        // Add a tiny cache-buster so a previous classic-script parse error doesn't poison the load
        const url = base + 'ui.js?v=esm_' + Date.now();
        try {
            const mod = await import(url);
            window.casAssistantUI = window.casAssistantUI || mod;
            return !!window.casAssistantUI?.open;
        } catch (e) {
            console.warn('[Assistant Boot] dynamic import failed, inline module fallback', e);
            const s = document.createElement('script');
            s.type = 'module';
            s.textContent = `
                import * as ui from '${url}';
                window.casAssistantUI = ui;
            `;
            document.head.appendChild(s);
            await new Promise(r => setTimeout(r, 60));
            return !!window.casAssistantUI?.open;
        }
    }

    let isInitialized = false;
    let ui = null;
    let api = null;

    function init() {
        if (isInitialized) {
            console.warn('[Assistant Boot] Already initialized. Skipping duplicate initialization.');
            return;
        }

        if (!window.casAssistant) {
            console.error('[Assistant Boot] Core module not loaded.');
            return;
        }

        if (!window.casAssistantUI || typeof window.casAssistantUI.initUI !== 'function') {
            console.error('[Assistant Boot] UI module not loaded or invalid.');
            return;
        }

        if (!window.casAssistantAPI || typeof window.casAssistantAPI.initAPI !== 'function') {
            console.error('[Assistant Boot] API module not loaded or invalid.');
            return;
        }

        ui = window.casAssistantUI.initUI(window.casAssistant);
        api = window.casAssistantAPI.initAPI();

        if (!ui || !api) {
            console.error('[Assistant Boot] Failed to initialize UI or API.');
            return;
        }

        wireEvents();
        attachClickDelegation();

        isInitialized = true;

        console.log('[Assistant Boot] Initialized successfully.');
    }

    function wireEvents() {
        window.casAssistant.on('open', ({ source, trigger }) => {
            if (ui) {
                ui.open();
            }

            if (api && !api.isReady()) {
                api.start();
            }

            document.documentElement.classList.add('is-assistant-open');

            trackOpen(source);
        });

        window.casAssistant.on('close', () => {
            if (ui) {
                ui.close();
            }

            if (api && api.isReady()) {
                api.stop();
            }

            document.documentElement.classList.remove('is-assistant-open');
        });
    }

    function attachClickDelegation() {
        // Bubble phase: delegation listener
        document.addEventListener('click', async (event) => {
            const trigger = event.target.closest('.js-open-assistant');
            if (!trigger) return;

            event.preventDefault();
            event.stopImmediatePropagation();

            // Early return if already open
            if (window.casAssistant?.isOpen?.()) return;

            // Throttle re-entry (300ms)
            if (window.__casAssistLastOpen && Date.now() - window.__casAssistLastOpen < 300) return;
            window.__casAssistLastOpen = Date.now();

            // Neutralize parent link if exists
            const parentLink = trigger.closest('a');
            if (parentLink) {
                parentLink.setAttribute('href', '#');
            }

            // Set default data-source if missing
            if (!trigger.dataset.source) {
                trigger.dataset.source = 'hero';
            }

            const source = trigger.dataset.source;
            
            await ensureUiLoaded();
            window.casAssistant.open(source, trigger);
            console.info('[Assistant Boot] Triggered by', trigger);
            
            // Fallback: if UI didn't open within 50ms, call UI.open() directly
            setTimeout(() => {
                const isOpen = document.documentElement.classList.contains('is-assistant-open');
                if (!isOpen && window.casAssistantUI?.open) {
                    console.warn('[Assistant Boot] Fallback -> UI.open()');
                    window.casAssistantUI.open();
                }
            }, 50);
        });
    }

    function trackOpen(source) {
        if (!window.CasEvents || typeof window.CasEvents.isAnalyticsAllowed !== 'function') {
            return;
        }

        if (!window.CasEvents.isAnalyticsAllowed()) {
            return;
        }

        window.CasEvents.trackButtonClick({
            label: 'Abrir Asistente',
            variant: 'primary',
            location: source || 'unknown'
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

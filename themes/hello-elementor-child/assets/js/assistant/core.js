/**
 * Assistant Core - State Management & Event Bus
 * 
 * Responsibilities:
 * - Global state management (isOpen, isLoading, currentView)
 * - Event bus for inter-module communication
 * - Session storage for conversation history
 * - Error boundary and recovery
 * - Lifecycle hooks (beforeOpen, afterClose, onError)
 * 
 * Dependencies: None (standalone module)
 * Exports: window.casAssistant (singleton)
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';

    if (window.casAssistant) {
        console.warn('[Assistant Core] Already initialized. Skipping duplicate initialization.');
        return;
    }

    const state = {
        isOpen: false,
        isLoading: false,
        currentView: 'idle',
        conversationId: null,
        lastTrigger: null
    };

    const listeners = {};

    function open(source, triggerElement) {
        if (state.isOpen) {
            console.warn('[Assistant Core] Already open.');
            return;
        }

        state.isOpen = true;
        state.lastTrigger = triggerElement || null;

        emit('open', { source, trigger: triggerElement });

        // Track analytics event (consent-gated)
        if (window.casAssistantHelpers?.analyticsEnabled()) {
            const eventSource = (triggerElement && triggerElement.dataset && triggerElement.dataset.source) 
                || source 
                || 'unknown';
            
            const analyticsEvent = new CustomEvent('cas_ai_open', {
                detail: { source: eventSource },
                bubbles: true,
                cancelable: false
            });
            window.dispatchEvent(analyticsEvent);
        }
    }

    function close() {
        if (!state.isOpen) {
            return;
        }

        state.isOpen = false;
        emit('close', {});
    }

    function isOpen() {
        return state.isOpen;
    }

    function on(event, callback) {
        if (typeof callback !== 'function') {
            console.error('[Assistant Core] Callback must be a function.');
            return;
        }

        if (!listeners[event]) {
            listeners[event] = [];
        }

        listeners[event].push(callback);
    }

    function off(event, callback) {
        if (!listeners[event]) {
            return;
        }

        if (callback) {
            listeners[event] = listeners[event].filter(cb => cb !== callback);
        } else {
            listeners[event] = [];
        }
    }

    function emit(event, payload) {
        if (!listeners[event] || listeners[event].length === 0) {
            return;
        }

        listeners[event].forEach(callback => {
            try {
                callback(payload || {});
            } catch (error) {
                console.error(`[Assistant Core] Error in "${event}" listener:`, error);
            }
        });
    }

    function setState(updates) {
        Object.assign(state, updates);
        emit('stateChange', { state: { ...state } });
    }

    function getState() {
        return { ...state };
    }

    window.casAssistant = {
        open,
        close,
        isOpen,
        on,
        off,
        emit,
        setState,
        getState
    };

    console.log('[Assistant Core] Initialized.');

})();

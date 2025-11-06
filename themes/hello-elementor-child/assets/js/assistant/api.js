/**
 * Assistant API - Backend Communication Layer
 * 
 * Responsibilities:
 * - Send user messages to backend endpoint
 * - Receive and parse assistant responses
 * - Handle API errors and retries
 * - Rate limiting and request debouncing
 * - Response streaming (if supported)
 * - Conversation ID management
 * 
 * Dependencies: Core (state, events)
 * Exports: window.casAssistantAPI.initAPI()
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    'use strict';

    window.casAssistantAPI = {
        initAPI: function() {
            let isActive = false;
            let conversationId = null;

            function start() {
                if (isActive) {
                    console.warn('[Assistant API] Already started.');
                    return;
                }

                isActive = true;
                conversationId = 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                console.log('[Assistant API] Started. Conversation ID:', conversationId);
            }

            function stop() {
                if (!isActive) {
                    return;
                }

                isActive = false;
                conversationId = null;

                console.log('[Assistant API] Stopped.');
            }

            function sendPrompt(text) {
                return new Promise((resolve, reject) => {
                    if (!isActive) {
                        reject(new Error('API not started. Call start() first.'));
                        return;
                    }

                    if (!text || typeof text !== 'string' || text.trim() === '') {
                        reject(new Error('Invalid prompt. Text must be a non-empty string.'));
                        return;
                    }

                    // Offline detection: return helpful tip instead of failing
                    if (!navigator.onLine) {
                        const offlineTip = 'Parece que no tienes conexión. Mientras tanto, puedes explorar nuestras categorías o guardar esta página para consultarla más tarde.';
                        resolve({
                            success: true,
                            text: offlineTip,
                            offline: true,
                            conversationId: conversationId,
                            timestamp: Date.now()
                        });
                        return;
                    }

                    // Get REST config from localized script
                    if (!window.casAssistantConfig || !window.casAssistantConfig.restUrl) {
                        reject(new Error('REST configuration not available.'));
                        return;
                    }

                    const { restUrl, nonce } = window.casAssistantConfig;

                    fetch(restUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': nonce
                        },
                        body: JSON.stringify({
                            message: text.trim()
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 401 || response.status === 403) {
                                throw new Error('No se pudo procesar tu consulta. Inténtalo nuevamente.');
                            }
                            if (response.status >= 500) {
                                throw new Error('No se pudo procesar tu consulta. Inténtalo nuevamente.');
                            }
                            throw new Error('No se pudo procesar tu consulta. Inténtalo nuevamente.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const response = {
                            success: true,
                            text: data.reply || 'Sin respuesta',
                            conversationId: conversationId,
                            timestamp: Date.now()
                        };

                        // Track analytics event (consent-gated)
                        if (window.casAssistantHelpers?.analyticsEnabled()) {
                            const analyticsEvent = new CustomEvent('cas_ai_message', {
                                detail: { 
                                    source: 'assistant',
                                    len: response.text.length
                                },
                                bubbles: true,
                                cancelable: false
                            });
                            window.dispatchEvent(analyticsEvent);
                        }

                        resolve(response);
                    })
                    .catch(error => {
                        console.error('[Assistant API] Request failed:', error);
                        reject({
                            error: true,
                            message: error.message || 'No se pudo procesar tu consulta. Inténtalo nuevamente.',
                            keepFocus: true
                        });
                    });
                });
            }

            function getConversationId() {
                return conversationId;
            }

            function isReady() {
                return isActive;
            }

            return {
                start,
                stop,
                sendPrompt,
                getConversationId,
                isReady
            };
        }
    };

})();

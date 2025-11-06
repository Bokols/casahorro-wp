/**
 * Assistant Bundle - All-in-one ES5 IIFE
 * Combines: Core, UI, API, Boot
 * No imports/exports, no dynamic imports
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

(function() {
    if (window.__CAS_ASSISTANT_BUNDLE_LOADED) {
        console.info('[Assistant] Bundle already loaded — skipping duplicate init');
        return;
    }
    window.__CAS_ASSISTANT_BUNDLE_LOADED = true;

    'use strict';

    console.log('[Assistant] Bundle loaded');

    // ============================================================
    // DOM State Shim - Source of Truth
    // ============================================================
    var __assistantOpenFlag = false;
    var __assistantPrevFocus = null;
    
    function __getDrawer() {
        return document.getElementById('cas-assistant-drawer');
    }
    
    function __domIsOpen() {
        var d = __getDrawer();
        var docHas = document.documentElement.classList.contains('is-assistant-open');
        var drawerVisible = !!(d && !d.hasAttribute('hidden') && d.getAttribute('aria-hidden') !== 'true');
        return docHas || drawerVisible;
    }
    
    function __safeFocus(el) {
        if (!el || typeof el.focus !== 'function') return;
        var hadTabindex = el.hasAttribute('tabindex');
        if (!hadTabindex) el.setAttribute('tabindex', '-1');
        try {
            el.focus({ preventScroll: true });
        } catch (e) {}
        if (!hadTabindex) el.removeAttribute('tabindex');
    }

    // ============================================================
    // CORE: State Management & Event Bus
    // ============================================================
    var state = {
        isOpen: false,
        isLoading: false,
        currentView: 'idle',
        conversationId: null,
        lastTrigger: null
    };

    var listeners = {};

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

    function emit(event, payload) {
        if (!listeners[event] || listeners[event].length === 0) {
            return;
        }
        listeners[event].forEach(function(callback) {
            try {
                callback(payload || {});
            } catch (error) {
                console.error('[Assistant Core] Error in "' + event + '" listener:', error);
            }
        });
    }

    function coreOpen(source, triggerElement) {
        // If core *thinks* we're open but DOM says closed, force a state reset
        var domOpen = document.documentElement.classList.contains('is-assistant-open');
        if (typeof state !== 'undefined' && state.isOpen === true && !domOpen) {
            console.warn('[Assistant] Desync detected; forcing state reset');
            state.isOpen = false;
        }
        
        if (coreIsOpen()) {
            console.warn('[Assistant Core] Already open.');
            return;
        }
        __assistantOpenFlag = true;
        state.isOpen = true;
        state.lastTrigger = triggerElement || null;
        emit('open', { source: source, trigger: triggerElement });
    }

    function coreClose() {
        if (!coreIsOpen()) {
            return;
        }
        __assistantOpenFlag = false;
        state.isOpen = false;
        
        // Track close timestamp for cool-down
        window.__casAssistantClosedAt = Date.now();
        
        emit('close', {});
    }

    function coreIsOpen() {
        // Trust the DOM over any stale flag
        if (document.documentElement.classList.contains('is-assistant-open')) return true;
        var el = document.getElementById('cas-assistant-drawer');
        if (el && el.isConnected && el.getAttribute('aria-hidden') === 'false' && !el.hasAttribute('hidden')) return true;
        return false;
    }

    // ============================================================
    // API: Backend Communication
    // ============================================================
    var apiActive = false;
    var apiConversationId = null;

    function apiStart() {
        if (apiActive) {
            console.warn('[Assistant API] Already started.');
            return;
        }
        apiActive = true;
        apiConversationId = 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        console.log('[Assistant API] Started. Conversation ID:', apiConversationId);
    }

    function apiSendPrompt(text) {
        return new Promise(function(resolve, reject) {
            if (!apiActive) {
                reject(new Error('API not started.'));
                return;
            }

            if (!text || typeof text !== 'string' || text.trim() === '') {
                reject(new Error('Invalid prompt.'));
                return;
            }

            // Offline detection
            if (!navigator.onLine) {
                resolve({
                    success: true,
                    text: 'Parece que no tienes conexión. Mientras tanto, puedes explorar nuestras categorías.',
                    offline: true,
                    conversationId: apiConversationId,
                    timestamp: Date.now()
                });
                return;
            }

            if (!window.casAssistantConfig || !window.casAssistantConfig.restUrl) {
                reject(new Error('REST configuration not available.'));
                return;
            }

            var config = window.casAssistantConfig;
            fetch(config.restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify({ message: text.trim() })
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('No se pudo procesar tu consulta. Inténtalo nuevamente.');
                }
                return response.json();
            })
            .then(function(data) {
                resolve({
                    success: true,
                    text: data.reply || 'Sin respuesta',
                    conversationId: apiConversationId,
                    timestamp: Date.now()
                });
            })
            .catch(function(error) {
                console.error('[Assistant API] Request failed:', error);
                reject({
                    error: true,
                    message: error.message || 'No se pudo procesar tu consulta. Inténtalo nuevamente.'
                });
            });
        });
    }

    // ============================================================
    // UI: Drawer Component & DOM Management
    // ============================================================
    
    // Helper: Ensure drawer is mounted (idempotent)
    function ensureDrawerMounted() {
        var drawer = document.getElementById('cas-assistant-drawer');
        if (!drawer) {
            // Create container
            drawer = document.createElement('div');
            drawer.id = 'cas-assistant-drawer';
            drawer.className = 'cas-assistant-drawer';
            drawer.setAttribute('role', 'dialog');
            drawer.setAttribute('aria-modal', 'true');
            drawer.setAttribute('aria-labelledby', 'assistant-title');
            drawer.setAttribute('hidden', ''); // will be removed on open
            // Minimal template (safe to replace later by your template builder)
            drawer.innerHTML =
                '<div class="cas-assistant-drawer__overlay" data-ui-close></div>' +
                '<div class="cas-assistant-drawer__panel">' +
                    '<header class="cas-assistant-drawer__header">' +
                        '<h2 id="assistant-title" class="cas-assistant-drawer__title">Diseñador de Interiores (beta)</h2>' +
                        '<button type="button" class="cas-assistant-drawer__close" data-ui-close aria-label="Cerrar asistente"><span aria-hidden="true">×</span></button>' +
                    '</header>' +
                    '<div class="cas-assistant-drawer__content" role="main">' +
                        '<div class="assistant-status" role="status" aria-live="polite"></div>' +
                        '<div class="assistant-messages"></div>' +
                        '<div class="assistant-chips">' +
                            '<button type="button" class="cas-assistant-drawer__chip">Necesito una cama bajo $2.000.000</button>' +
                            '<button type="button" class="cas-assistant-drawer__chip">Sofá 3 cuerpos resistente</button>' +
                            '<button type="button" class="cas-assistant-drawer__chip">Mesa de comedor 6 personas</button>' +
                        '</div>' +
                        '<div class="assistant-composer">' +
                            '<textarea class="cas-assistant-drawer__input" placeholder="Escribe tu consulta (Shift+Enter para nueva línea)" aria-label="Escribe tu consulta"></textarea>' +
                            '<button type="button" class="send cas-assistant-drawer__send">Enviar</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(drawer);
            // Wire core close
            var closes = drawer.querySelectorAll('[data-ui-close]');
            for (var i = 0; i < closes.length; i++) {
                closes[i].addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.casAssistant && window.casAssistant.close) {
                        window.casAssistant.close();
                    }
                });
            }
        }
        return drawer;
    }
    
    var drawer = null;
    var overlay = null;
    var panel = null;
    var statusRegion = null;
    var messagesEl = null;
    var chipsEl = null;
    var textarea = null;
    var sendBtn = null;
    var closeButton = null;
    var isInitialized = false;
    var focusableElements = [];
    var firstFocusable = null;
    var lastFocusable = null;

    function mount() {
        console.info('[Assistant UI] mount()');

        if (drawer && drawer.isConnected) {
            return true;
        }

        drawer = document.getElementById('cas-assistant-drawer');
        if (!drawer) {
            drawer = document.createElement('div');
            drawer.id = 'cas-assistant-drawer';
            drawer.className = 'cas-assistant-drawer';
            drawer.setAttribute('role', 'dialog');
            drawer.setAttribute('aria-modal', 'true');
            drawer.setAttribute('aria-labelledby', 'assistant-title');
            drawer.setAttribute('hidden', '');
            document.body.appendChild(drawer);
        }

        drawer.innerHTML = 
            '<div class="cas-assistant-drawer__overlay" data-ui-close></div>' +
            '<div class="cas-assistant-drawer__panel">' +
                '<header class="cas-assistant-drawer__header">' +
                    '<h2 id="assistant-title" class="cas-assistant-drawer__title">Diseñador de Interiores (beta)</h2>' +
                    '<button type="button" class="cas-assistant-drawer__close" data-ui-close aria-label="Cerrar asistente">' +
                        '<span aria-hidden="true">×</span>' +
                    '</button>' +
                '</header>' +
                '<div class="cas-assistant-drawer__content" role="main">' +
                    '<div class="assistant-status" role="status" aria-live="polite"></div>' +
                    '<div class="assistant-messages"></div>' +
                    '<div class="assistant-chips">' +
                        '<button type="button" class="cas-assistant-drawer__chip">Necesito una cama bajo $2.000.000</button>' +
                        '<button type="button" class="cas-assistant-drawer__chip">Sofá 3 cuerpos resistente</button>' +
                        '<button type="button" class="cas-assistant-drawer__chip">Mesa de comedor 6 personas</button>' +
                    '</div>' +
                    '<div class="assistant-composer">' +
                        '<textarea class="cas-assistant-drawer__input" placeholder="Escribe tu consulta (Shift+Enter para nueva línea)" aria-label="Escribe tu consulta"></textarea>' +
                        '<button type="button" class="send cas-assistant-drawer__send">Enviar</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        panel = drawer.querySelector('.cas-assistant-drawer__panel');
        if (!panel) {
            throw new Error('[Assistant UI] panel not found');
        }

        overlay = drawer.querySelector('.cas-assistant-drawer__overlay');
        statusRegion = drawer.querySelector('.assistant-status');
        messagesEl = drawer.querySelector('.assistant-messages');
        chipsEl = drawer.querySelector('.assistant-chips');
        textarea = drawer.querySelector('.assistant-composer textarea');
        sendBtn = drawer.querySelector('.assistant-composer .send');
        closeButton = drawer.querySelector('.cas-assistant-drawer__close');

        if (!statusRegion || !messagesEl || !chipsEl || !textarea || !sendBtn) {
            console.error('[Assistant UI] selector missing:', { statusRegion: statusRegion, messagesEl: messagesEl, chipsEl: chipsEl, textarea: textarea, sendBtn: sendBtn });
        }

        attachListeners();
        isInitialized = true;
        console.info('[Assistant UI] Mounted.');
        return true;
    }

    function attachListeners() {
        var closeElements = drawer.querySelectorAll('[data-ui-close]');
        for (var i = 0; i < closeElements.length; i++) {
            closeElements[i].addEventListener('click', handleClose);
        }

        overlay.addEventListener('click', handleClose);
        drawer.addEventListener('keydown', handleKeydown);

        if (chipsEl) {
            chipsEl.addEventListener('click', function(e) {
                var btn = e.target.closest('button');
                if (!btn) return;
                if (textarea) textarea.value = btn.textContent.trim();
                sendCurrentMessage();
            });
        }

        if (textarea) {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendCurrentMessage();
                }
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', sendCurrentMessage);
        }
    }

    function sendCurrentMessage() {
        if (!textarea) return;
        var text = textarea.value.trim();
        if (!text) return;

        // Add user message to UI
        appendMessage('user', text);
        textarea.value = '';
        updateStatus('Buscando...');

        // Start API if not active
        if (!apiActive) {
            apiStart();
        }

        // Send to backend
        apiSendPrompt(text)
            .then(function(response) {
                appendMessage('assistant', response.text);
                updateStatus('');
            })
            .catch(function(error) {
                var errorMsg = error.message || 'No se pudo procesar tu consulta. Inténtalo nuevamente.';
                appendMessage('error', errorMsg);
                updateStatus('');
            });
    }

    function appendMessage(role, text) {
        if (!messagesEl) return;
        var messageEl = document.createElement('div');
        messageEl.className = 'assistant-message assistant-message--' + role;
        messageEl.textContent = text;
        messagesEl.appendChild(messageEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function handleClose(event) {
        event.preventDefault();
        coreClose();
    }

    function handleKeydown(event) {
        if (event.key === 'Escape' && coreIsOpen()) {
            event.preventDefault();
            coreClose();
            return;
        }

        if (event.key === 'Tab' && coreIsOpen()) {
            if (event.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    event.preventDefault();
                    if (lastFocusable) lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    event.preventDefault();
                    if (firstFocusable) firstFocusable.focus();
                }
            }
        }
    }

    function updateFocusableElements() {
        var selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        focusableElements = Array.from(panel.querySelectorAll(selector));
        firstFocusable = focusableElements[0] || null;
        lastFocusable = focusableElements[focusableElements.length - 1] || null;
    }

    function uiOpen() {
        // Silent idempotent guard
        if (__assistantOpenFlag) return;
        
        try {
            var drawer = ensureDrawerMounted();
            if (!drawer) {
                console.error('[Assistant] ensureDrawerMounted() failed');
                return;
            }

            // If DOM already open, just ensure attributes are correct and return
            if (__domIsOpen()) {
                drawer.removeAttribute('hidden');
                drawer.setAttribute('aria-hidden', 'false');
                document.documentElement.classList.add('is-assistant-open');
                document.body.style.overflow = 'hidden';
                __assistantOpenFlag = true;
                return;
            }

            // Apply open toggles
            drawer.removeAttribute('hidden');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            document.documentElement.classList.add('is-assistant-open');

            __assistantOpenFlag = true;

            var pageElement = document.getElementById('page');
            if (pageElement) pageElement.inert = true;

            // Remember previously focused element
            __assistantPrevFocus = document.activeElement && document.activeElement !== document.body ? document.activeElement : null;

            var closeBtn = drawer.querySelector('.cas-assistant-drawer__close');
            if (closeBtn) setTimeout(function() { __safeFocus(closeBtn); }, 0);

            // Minimal chip wiring (idempotent)
            var chips = drawer.querySelector('.assistant-chips');
            var textarea = drawer.querySelector('.assistant-composer textarea');
            var sendBtn = drawer.querySelector('.assistant-composer .send');
            
            if (chips && textarea) {
                if (!chips.__wired) {
                    chips.addEventListener('click', function(e) {
                        var b = e.target && e.target.closest('button');
                        if (!b) return;
                        textarea.value = (b.textContent || '').trim();
                        if (sendBtn) sendBtn.click();
                    });
                    chips.__wired = true;
                }
            }
            
            if (sendBtn && textarea) {
                if (!sendBtn.__wired) {
                    sendBtn.addEventListener('click', function() {
                        var text = (textarea.value || '').trim();
                        if (!text) return;
                        
                        // Add user message to UI
                        var messagesEl = drawer.querySelector('.assistant-messages');
                        if (messagesEl) {
                            var messageEl = document.createElement('div');
                            messageEl.className = 'assistant-message assistant-message--user';
                            messageEl.textContent = text;
                            messagesEl.appendChild(messageEl);
                            messagesEl.scrollTop = messagesEl.scrollHeight;
                        }
                        
                        console.info('[Assistant] sending:', text);
                        textarea.value = '';
                        
                        // Update status
                        var statusRegion = drawer.querySelector('.assistant-status');
                        if (statusRegion) statusRegion.textContent = 'Buscando...';
                        
                        // Start API if not active
                        if (!apiActive) {
                            apiStart();
                        }
                        
                        // Send to backend
                        apiSendPrompt(text)
                            .then(function(response) {
                                if (messagesEl) {
                                    var botMessageEl = document.createElement('div');
                                    botMessageEl.className = 'assistant-message assistant-message--assistant';
                                    botMessageEl.textContent = response.text;
                                    messagesEl.appendChild(botMessageEl);
                                    messagesEl.scrollTop = messagesEl.scrollHeight;
                                }
                                if (statusRegion) statusRegion.textContent = '';
                            })
                            .catch(function(error) {
                                var errorMsg = error.message || 'No se pudo procesar tu consulta. Inténtalo nuevamente.';
                                if (messagesEl) {
                                    var errorMessageEl = document.createElement('div');
                                    errorMessageEl.className = 'assistant-message assistant-message--error';
                                    errorMessageEl.textContent = errorMsg;
                                    messagesEl.appendChild(errorMessageEl);
                                    messagesEl.scrollTop = messagesEl.scrollHeight;
                                }
                                if (statusRegion) statusRegion.textContent = '';
                            });
                    });
                    sendBtn.__wired = true;
                }
                
                if (!textarea.__wired) {
                    textarea.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            sendBtn.click();
                        }
                    });
                    textarea.__wired = true;
                }
            }

            console.info('[Assistant] open() mounted & visible');
        } catch (e) {
            console.error('[Assistant] open() error', e);
        }
    }

    function uiClose() {
        try {
            var drawer = __getDrawer();

            // 1) Move focus out of the drawer first (to previous focus, #page, or body)
            var target = __assistantPrevFocus || document.getElementById('page') || document.body;
            __safeFocus(target);
            __assistantPrevFocus = null;

            // 2) Now it's safe to hide the drawer
            document.documentElement.classList.remove('is-assistant-open');
            if (drawer) {
                drawer.setAttribute('aria-hidden', 'true');
                drawer.setAttribute('hidden', '');
            }
            document.body.style.overflow = '';

            // 3) Bookkeeping
            __assistantOpenFlag = false;

            // 4) Remove inert from main content
            var pageElement = document.getElementById('page');
            if (pageElement) pageElement.inert = false;
            
            // 5) Track close timestamp to prevent bounce re-opens
            window.__casAssistantClosedAt = Date.now();
        } catch (e) {
            console.error('[Assistant] close() error', e);
        }
    }

    function updateStatus(message) {
        if (!statusRegion) return;
        statusRegion.textContent = message;
    }

    // Wire core event listeners
    on('open', uiOpen);
    on('close', uiClose);

    // ============================================================
    // BOOT: Click Delegation & Public API
    // ============================================================
    
    // Sync flag with DOM on first run
    __assistantOpenFlag = __domIsOpen();
    
    // Boot guard: prevent duplicate initialization
    if (window.__casAssistantBooted) {
        console.info('[Assistant Boot] already booted');
    } else {
        window.__casAssistantBooted = true;
    }
    
    // Store handler ref so we don't reattach
    if (!window.__casAssistantDelegator) {
        window.__casAssistantDelegator = function onAssistantTriggerClick(event) {
            // Only react to user-initiated clicks (allow synthetic if flag set for testing)
            if (!event.isTrusted && !window.__CAS_ALLOW_SYNTHETIC) return;

            // Must originate from a .js-open-assistant (button or inside)
            var trigger = event.target.closest('.js-open-assistant');
            if (!trigger) return;

            // Respect left-click or keyboard "click"
            if (event.detail === 0) {
                // keyboard; ok
            } else if (event.button !== 0) {
                return;
            }

            // Cool-down: ignore opens right after a close
            if (window.__casAssistantClosedAt && (Date.now() - window.__casAssistantClosedAt) < 400) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation ? event.stopImmediatePropagation() : event.stopPropagation();

            // Source tagging
            if (!trigger.dataset.source) trigger.dataset.source = 'hero';

            console.log('[Assistant Boot] open() called');
            if (window.casAssistant && window.casAssistant.open) {
                window.casAssistant.open(trigger.dataset.source, trigger);
            }
        };

        // Attach ONCE (bubble)
        document.addEventListener('click', window.__casAssistantDelegator, false);
    }

    // ============================================================
    // EXPOSE PUBLIC API
    // ============================================================
    window.casAssistant = {
        open: coreOpen,
        close: coreClose,
        isOpen: coreIsOpen,
        __hardReset: function() {
            try {
                var d = document.getElementById('cas-assistant-drawer');
                if (d) {
                    d.setAttribute('aria-hidden', 'true');
                    d.setAttribute('hidden', '');
                }
                document.documentElement.classList.remove('is-assistant-open');
                document.body.style.overflow = '';
                if (typeof state !== 'undefined') state.isOpen = false;
                console.info('[Assistant] hardReset complete');
            } catch (e) {
                console.warn('hardReset error', e);
            }
        }
    };

    window.casAssistantUI = {
        open: uiOpen,
        close: uiClose,
        mount: mount
    };

})();

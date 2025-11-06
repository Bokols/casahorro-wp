/**
 * Assistant UI - Drawer Component & DOM Management
 * 
 * Responsibilities:
 * - Render drawer HTML structure
 * - Open/close animations with ARIA state management
 * - Focus trap and keyboard navigation (Escape, Tab)
 * - Focus restoration to trigger element
 * - Loading spinner during API calls
 * - Message rendering (user input, assistant response)
 * - Scroll management (auto-scroll to bottom)
 * 
 * Dependencies: Core (state, events)
 * Exports: open, close, mount
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

import * as core from './core.js';

console.info('[Assistant UI] module loaded');

let drawer = null;
let overlay = null;
let panel = null;
let contentArea = null;
let statusRegion = null;
let messagesEl = null;
let chipsEl = null;
let textarea = null;
let sendBtn = null;
let errorRegion = null;
let closeButton = null;
let isInitialized = false;
let focusableElements = [];
let firstFocusable = null;
let lastFocusable = null;

            function mount() {
                console.info('[Assistant UI] mount()');

                // Early return if already mounted and connected
                if (drawer && drawer.isConnected) {
                    return true;
                }

                // Get or create drawer element
                drawer = document.getElementById('cas-assistant-drawer');
                if (!drawer) {
                    drawer = document.createElement('div');
                    drawer.id = 'cas-assistant-drawer';
                    drawer.className = 'cas-assistant-drawer';
                    // Set required ARIA attributes before inserting
                    drawer.setAttribute('role', 'dialog');
                    drawer.setAttribute('aria-modal', 'true');
                    drawer.setAttribute('aria-labelledby', 'assistant-title');
                    drawer.setAttribute('hidden', '');
                    document.body.appendChild(drawer);
                }

                // Inject template
                drawer.innerHTML = `
                    <div class="cas-assistant-drawer__overlay" data-ui-close></div>
                    <div class="cas-assistant-drawer__panel">
                        <header class="cas-assistant-drawer__header">
                            <h2 id="assistant-title" class="cas-assistant-drawer__title">Diseñador de Interiores (beta)</h2>
                            <button 
                                type="button" 
                                class="cas-assistant-drawer__close" 
                                data-ui-close 
                                aria-label="Cerrar asistente"
                            >
                                <span aria-hidden="true">×</span>
                            </button>
                        </header>
                        <div class="cas-assistant-drawer__content" role="main">
                            <div 
                                class="assistant-status" 
                                role="status" 
                                aria-live="polite"
                            ></div>
                            <div class="assistant-messages"></div>
                            <div class="assistant-chips">
                                <button type="button" class="cas-assistant-drawer__chip">
                                    Necesito una cama bajo $2.000.000
                                </button>
                                <button type="button" class="cas-assistant-drawer__chip">
                                    Sofá 3 cuerpos resistente
                                </button>
                                <button type="button" class="cas-assistant-drawer__chip">
                                    Mesa de comedor 6 personas
                                </button>
                            </div>
                            <div class="assistant-composer">
                                <textarea 
                                    class="cas-assistant-drawer__input" 
                                    placeholder="Escribe tu consulta (Shift+Enter para nueva línea)" 
                                    aria-label="Escribe tu consulta"
                                ></textarea>
                                <button type="button" class="send cas-assistant-drawer__send">Enviar</button>
                            </div>
                        </div>
                    </div>
                `;

                // Verify panel exists
                panel = drawer.querySelector('.cas-assistant-drawer__panel');
                if (!panel) {
                    throw new Error('[Assistant UI] panel not found');
                }

                overlay = drawer.querySelector('.cas-assistant-drawer__overlay');
                contentArea = drawer.querySelector('.cas-assistant-drawer__content');
                statusRegion = drawer.querySelector('.assistant-status');
                messagesEl = drawer.querySelector('.assistant-messages');
                chipsEl = drawer.querySelector('.assistant-chips');
                textarea = drawer.querySelector('.assistant-composer textarea');
                sendBtn = drawer.querySelector('.assistant-composer .send');
                closeButton = drawer.querySelector('.cas-assistant-drawer__close');
                errorRegion = null; // Removed error region

                // Validate required elements
                if (!statusRegion || !messagesEl || !chipsEl || !textarea || !sendBtn) {
                    console.error('[Assistant UI] selector missing:', { statusRegion, messagesEl, chipsEl, textarea, sendBtn });
                }

                attachListeners();
                isInitialized = true;

                console.info('[Assistant UI] Mounted.');
                return true;
            }

            function attachListeners() {
                drawer.querySelectorAll('[data-ui-close]').forEach(el => {
                    el.addEventListener('click', handleClose);
                });

                overlay.addEventListener('click', handleClose);
                drawer.addEventListener('keydown', handleKeydown);

                // Wire chip quick actions
                if (chipsEl) {
                    chipsEl.addEventListener('click', e => {
                        const btn = e.target.closest('button');
                        if (!btn) return;
                        if (textarea) textarea.value = btn.textContent.trim();
                        sendCurrentMessage();
                    });
                }

                // Textarea Enter key handling
                if (textarea) {
                    textarea.addEventListener('keydown', e => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            sendCurrentMessage();
                        }
                    });
                }

                // Send button
                if (sendBtn) {
                    sendBtn.addEventListener('click', sendCurrentMessage);
                }
            }

            function sendCurrentMessage() {
                if (!textarea) return;
                const text = textarea.value.trim();
                if (!text) return;
                core.emit('send_message', { text });
                textarea.value = '';
            }

            function handleClose(event) {
                event.preventDefault();
                core.close();
            }

            function handleKeydown(event) {
                if (event.key === 'Escape' && core.isOpen()) {
                    event.preventDefault();
                    core.close();
                    return;
                }

                // Focus trap
                if (event.key === 'Tab' && core.isOpen()) {
                    if (event.shiftKey) {
                        if (document.activeElement === firstFocusable) {
                            event.preventDefault();
                            lastFocusable?.focus();
                        }
                    } else {
                        if (document.activeElement === lastFocusable) {
                            event.preventDefault();
                            firstFocusable?.focus();
                        }
                    }
                }
            }

            function updateFocusableElements() {
                const selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
                focusableElements = Array.from(panel.querySelectorAll(selector));
                firstFocusable = focusableElements[0] || null;
                lastFocusable = focusableElements[focusableElements.length - 1] || null;
            }

            function open() {
                // Early return if already open
                if (core.isOpen()) {
                    return;
                }

                if (!isInitialized) {
                    mount();
                }

                // Re-mount if drawer disconnected
                if (!drawer || !drawer.isConnected) {
                    mount();
                }

                // Re-query panel and verify
                const panel = drawer.querySelector('.cas-assistant-drawer__panel');
                if (!panel) {
                    console.error('[Assistant UI] panel missing on open');
                    return;
                }

                drawer.removeAttribute('hidden');
                drawer.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                document.documentElement.classList.add('is-assistant-open');

                // Set main content inert
                const pageElement = document.getElementById('page');
                if (pageElement) {
                    pageElement.inert = true;
                }

                updateFocusableElements();

                requestAnimationFrame(() => {
                    if (closeButton) {
                        closeButton.focus();
                    }
                });

                updateStatus('Asistente abierto');
            }

            function close() {
                if (!isInitialized || !core.isOpen()) {
                    return;
                }

                drawer.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                document.documentElement.classList.remove('is-assistant-open');

                // Remove inert from main content
                const pageElement = document.getElementById('page');
                if (pageElement) {
                    pageElement.inert = false;
                }

                setTimeout(() => {
                    drawer.setAttribute('hidden', '');
                    
                    const state = core.getState();
                    if (state.lastTrigger && state.lastTrigger.focus) {
                        state.lastTrigger.focus();
                    }
                }, 300);

            updateStatus('Asistente cerrado');
        }

        function updateStatus(message) {
            if (!statusRegion) return;
            statusRegion.textContent = message;
            // Clear error when status updates
            if (errorRegion) {
                errorRegion.textContent = '';
            }
        }

        function updateError(message) {
            if (!errorRegion) return;
            errorRegion.textContent = message;
        }

        function setContent(html) {
            if (!contentArea) return;
            contentArea.innerHTML = html;
        }

        function appendContent(html) {
            if (!contentArea) return;
            contentArea.insertAdjacentHTML('beforeend', html);
        }

// Wire core event listeners
core.on('open', open);
core.on('close', close);
core.on('send_message', ({ text }) => {
    if (!isInitialized) mount();
    // TODO: hook to API send later; for now just status ping
    updateStatus(`Buscando: ${text}`);
});

// Lightweight debug handle
window.casAssistantUI = { open, close, mount, isInitialized: () => isInitialized };

export { open, close, mount, updateStatus, updateError, setContent, appendContent };
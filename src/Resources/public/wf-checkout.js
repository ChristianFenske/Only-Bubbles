/**
 * W&F – kleine Helfer für Konto, Warenkorb & Checkout (nur geladen, wenn das neue Design aktiv ist)
 *
 * - Bestellnummer kopieren (Abschlussseite, [data-wf-copy-text])
 * - Anrede/Kontotyp als Pill-Auswahl (Registrierung, Konto, Checkout, Adress-Formulare)
 * - Auge-Button an Passwortfeldern (Anmelden, Registrierung, Konto)
 */
(function () {
    'use strict';

    var SCOPE = '.account-register, .wf-checkout, .wf-account, .address-manager-modal, .account-address-form';
    var SEGMENTED_SELECTOR = 'select.contact-select, select[name="salutationId"], select[name$="[salutationId]"]';
    var isGerman = (document.documentElement.lang || '').toLowerCase().indexOf('de') === 0;

    /* --- Bestellnummer kopieren ------------------------------------------ */
    function initCopy(button) {
        if (button.dataset.wfCopyReady === 'true') {
            return;
        }
        button.dataset.wfCopyReady = 'true';

        var options = {};
        try {
            options = JSON.parse(button.getAttribute('data-wf-copy-text-options') || '{}');
        } catch (e) {
            options = {};
        }

        var label = button.querySelector('.wf-copy-label');
        var defaultLabel = label ? label.textContent : '';
        var timeout = null;

        function fallbackCopy(value) {
            var helper = document.createElement('textarea');
            helper.value = value;
            helper.setAttribute('readonly', '');
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            helper.select();
            document.execCommand('copy');
            helper.remove();
        }

        function done() {
            button.classList.add('is-copied');
            if (label) {
                label.textContent = options.copiedLabel || (isGerman ? 'Kopiert' : 'Copied');
            }

            clearTimeout(timeout);
            timeout = setTimeout(function () {
                button.classList.remove('is-copied');
                if (label) {
                    label.textContent = defaultLabel;
                }
            }, 2000);
        }

        button.addEventListener('click', function () {
            var value = String(options.value || '');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(done, function () {
                    fallbackCopy(value);
                    done();
                });
            } else {
                fallbackCopy(value);
                done();
            }
        });
    }

    /* --- Pill-Auswahl statt kleinem Select --------------------------------
       Das Original-Select bleibt im Formular (Name, Validierung, Firma-Felder
       ein-/ausblenden) und wird nur visuell versteckt. */
    function initSegmented(select) {
        var options = Array.prototype.filter.call(select.options, function (option) {
            return option.value !== '' && !option.disabled;
        });

        if (select.multiple || options.length < 2 || options.length > 4 || select.dataset.wfSegmentedReady === 'true') {
            return;
        }
        select.dataset.wfSegmentedReady = 'true';

        var wrapper = document.createElement('div');
        wrapper.className = 'wf-segmented';
        wrapper.setAttribute('role', 'radiogroup');

        if (select.labels && select.labels[0]) {
            if (!select.labels[0].id) {
                select.labels[0].id = select.id + '-label';
            }
            wrapper.setAttribute('aria-labelledby', select.labels[0].id);
        }

        var buttons = options.map(function (option) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'wf-segmented-item';
            button.setAttribute('role', 'radio');
            button.dataset.value = option.value;
            button.textContent = option.textContent.trim();
            button.disabled = select.disabled;
            button.addEventListener('click', function () {
                if (select.value === option.value) {
                    return;
                }
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
            wrapper.appendChild(button);

            return button;
        });

        function sync() {
            buttons.forEach(function (button) {
                var active = button.dataset.value === select.value;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-checked', active ? 'true' : 'false');
                button.tabIndex = active || !select.value ? 0 : -1;
            });
        }

        // Kontotyp ohne Auswahl -> "Privat" vorauswählen
        var isAccountType = select.classList.contains('contact-select');
        var hasPrivate = options.some(function (option) { return option.value === 'private'; });
        if (isAccountType && !select.value && hasPrivate) {
            select.value = 'private';
            // "change" erst nach der Initialisierung der Shopware-Plugins
            window.setTimeout(function () {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }, 0);
        }

        select.classList.add('wf-segmented-source');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
        select.insertAdjacentElement('afterend', wrapper);
        select.addEventListener('change', sync);

        sync();
    }

    /* --- Passwort anzeigen/verbergen -------------------------------------- */
    function initPassword(input) {
        if (input.dataset.wfPasswordReady === 'true') {
            return;
        }
        input.dataset.wfPasswordReady = 'true';

        var showLabel = isGerman ? 'Passwort anzeigen' : 'Show password';
        var hideLabel = isGerman ? 'Passwort verbergen' : 'Hide password';

        var wrap = document.createElement('div');
        wrap.className = 'wf-password-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'wf-password-toggle';
        button.setAttribute('aria-controls', input.id);
        button.innerHTML =
            '<svg class="wf-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>' +
            '<svg class="wf-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a17.6 17.6 0 0 1-2.2 3.2M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 8 10 8a9.7 9.7 0 0 0 5.4-1.6"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="m2 2 20 20"/></svg>';
        wrap.appendChild(button);

        function update() {
            var visible = input.type === 'text';
            button.classList.toggle('is-visible', visible);
            button.setAttribute('aria-pressed', visible ? 'true' : 'false');
            button.setAttribute('aria-label', visible ? hideLabel : showLabel);
        }

        button.addEventListener('click', function () {
            input.type = input.type === 'password' ? 'text' : 'password';
            update();
            input.focus();
        });

        update();
    }

    function scan(root) {
        root.querySelectorAll('[data-wf-copy-text]').forEach(initCopy);

        root.querySelectorAll(SEGMENTED_SELECTOR).forEach(function (select) {
            if (select.closest(SCOPE)) {
                initSegmented(select);
            }
        });

        root.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (input.closest(SCOPE)) {
                initPassword(input);
            }
        });
    }

    function start() {
        scan(document);

        // Nachgeladene Inhalte (z. B. Adress-Dialog im Checkout)
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        scan(node);
                    }
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

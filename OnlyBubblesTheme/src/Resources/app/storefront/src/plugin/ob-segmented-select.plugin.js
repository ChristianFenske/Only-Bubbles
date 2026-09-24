const { PluginBaseClass } = window;

/**
 * Macht aus einem kleinen <select> (Kontotyp, Anrede) eine Segmented-Pill-Auswahl.
 * Das Original-Select bleibt im Formular (Name, Validierung, FormFieldToggle) und
 * wird nur visuell versteckt; ein Klick auf eine Pill setzt dessen Wert und löst
 * "change" aus, damit Shopware-Logik (z. B. Firma-Felder einblenden) weiter greift.
 */
export default class ObSegmentedSelectPlugin extends PluginBaseClass {
    static options = {
        maxOptions: 4,
        // Kontotyp ohne Auswahl (Login-Seite) -> "Privat" vorauswählen
        defaultAccountType: 'private',
        wrapperClass: 'ob-segmented',
        itemClass: 'ob-segmented-item',
        activeClass: 'is-active',
    };

    init() {
        const select = this.el;
        const options = Array.from(select.options).filter((option) => option.value !== '' && !option.disabled);

        if (select.multiple || options.length < 2 || options.length > this.options.maxOptions) {
            return;
        }

        if (select.dataset.obSegmentedReady === 'true') {
            return;
        }
        select.dataset.obSegmentedReady = 'true';

        this._wrapper = document.createElement('div');
        this._wrapper.className = this.options.wrapperClass;
        this._wrapper.setAttribute('role', 'radiogroup');

        if (select.labels && select.labels[0]) {
            if (!select.labels[0].id) {
                select.labels[0].id = `${select.id}-label`;
            }
            this._wrapper.setAttribute('aria-labelledby', select.labels[0].id);
        }

        this._buttons = options.map((option) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = this.options.itemClass;
            button.setAttribute('role', 'radio');
            button.dataset.value = option.value;
            button.textContent = option.textContent.trim();
            button.disabled = select.disabled;
            button.addEventListener('click', this._onClick.bind(this, option.value));
            this._wrapper.appendChild(button);

            return button;
        });

        this._applyDefault(options);

        select.classList.add('ob-segmented-source');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
        select.insertAdjacentElement('afterend', this._wrapper);
        select.addEventListener('change', this._sync.bind(this));

        this._sync();
    }

    _applyDefault(options) {
        const select = this.el;
        const isAccountType = select.classList.contains('contact-select');
        const hasDefault = options.some((option) => option.value === this.options.defaultAccountType);

        if (!isAccountType || select.value || !hasDefault) {
            return;
        }

        select.value = this.options.defaultAccountType;
        // "change" erst nach der Initialisierung der Shopware-Plugins (FormFieldToggle)
        window.setTimeout(() => select.dispatchEvent(new Event('change', { bubbles: true })), 0);
    }

    _onClick(value) {
        if (this.el.value === value) {
            return;
        }

        this.el.value = value;
        this.el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    _sync() {
        this._buttons.forEach((button) => {
            const active = button.dataset.value === this.el.value;
            button.classList.toggle(this.options.activeClass, active);
            button.setAttribute('aria-checked', active ? 'true' : 'false');
            button.tabIndex = active || !this.el.value ? 0 : -1;
        });
    }
}

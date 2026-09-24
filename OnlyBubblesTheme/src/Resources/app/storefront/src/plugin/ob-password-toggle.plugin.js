const { PluginBaseClass } = window;

/**
 * Ergänzt Passwortfelder um einen "Auge"-Button zum Ein-/Ausblenden.
 */
export default class ObPasswordTogglePlugin extends PluginBaseClass {
    static options = {
        labels: {
            de: { show: 'Passwort anzeigen', hide: 'Passwort verbergen' },
            en: { show: 'Show password', hide: 'Hide password' },
        },
    };

    init() {
        const input = this.el;

        if (input.dataset.obPasswordReady === 'true') {
            return;
        }
        input.dataset.obPasswordReady = 'true';

        const lang = (document.documentElement.lang || '').toLowerCase().startsWith('de') ? 'de' : 'en';
        this._showLabel = this.options.labels[lang].show;
        this._hideLabel = this.options.labels[lang].hide;

        const wrap = document.createElement('div');
        wrap.className = 'ob-password-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        this._button = document.createElement('button');
        this._button.type = 'button';
        this._button.className = 'ob-password-toggle';
        this._button.setAttribute('aria-controls', input.id);
        this._button.innerHTML = `
            <svg class="ob-eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="ob-eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a17.6 17.6 0 0 1-2.2 3.2M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 8 10 8a9.7 9.7 0 0 0 5.4-1.6"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="m2 2 20 20"/></svg>`;
        wrap.appendChild(this._button);

        this._button.addEventListener('click', this._toggle.bind(this));
        this._update();
    }

    _toggle() {
        this.el.type = this.el.type === 'password' ? 'text' : 'password';
        this._update();
        this.el.focus();
    }

    _update() {
        const visible = this.el.type === 'text';
        this._button.classList.toggle('is-visible', visible);
        this._button.setAttribute('aria-pressed', visible ? 'true' : 'false');
        this._button.setAttribute('aria-label', visible ? this._hideLabel : this._showLabel);
    }
}

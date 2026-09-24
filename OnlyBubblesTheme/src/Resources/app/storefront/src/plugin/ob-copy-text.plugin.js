const { PluginBaseClass } = window;

/**
 * Kopiert data-ob-copy-text-options.value in die Zwischenablage (z. B. Bestellnummer).
 */
export default class ObCopyTextPlugin extends PluginBaseClass {
    static options = {
        value: '',
        copiedLabel: 'Kopiert',
        labelSelector: '.ob-copy-label',
        resetDelay: 2000,
    };

    init() {
        this._label = this.el.querySelector(this.options.labelSelector);
        this._defaultLabel = this._label ? this._label.textContent : '';
        this.el.addEventListener('click', this._onClick.bind(this));
    }

    async _onClick() {
        try {
            await navigator.clipboard.writeText(String(this.options.value));
        } catch (e) {
            const helper = document.createElement('textarea');
            helper.value = String(this.options.value);
            helper.setAttribute('readonly', '');
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            helper.select();
            document.execCommand('copy');
            helper.remove();
        }

        this.el.classList.add('is-copied');
        if (this._label) {
            this._label.textContent = this.options.copiedLabel;
        }

        clearTimeout(this._timeout);
        this._timeout = setTimeout(() => {
            this.el.classList.remove('is-copied');
            if (this._label) {
                this._label.textContent = this._defaultLabel;
            }
        }, this.options.resetDelay);
    }
}

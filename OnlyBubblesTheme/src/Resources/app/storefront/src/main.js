// Only Bubbles — Storefront JS-Einstiegspunkt.
// Kleine Plugins für Konto & Checkout (Segmented Pills, Passwort-Auge, Kopieren).
import ObSegmentedSelectPlugin from './plugin/ob-segmented-select.plugin';
import ObPasswordTogglePlugin from './plugin/ob-password-toggle.plugin';
import ObCopyTextPlugin from './plugin/ob-copy-text.plugin';

const PluginManager = window.PluginManager;

// Kontotyp + Anrede als Segmented Pills (Registrierung, Checkout, Profil, Adressformulare)
PluginManager.register(
    'ObSegmentedSelect',
    ObSegmentedSelectPlugin,
    'select.contact-select, select[name="salutationId"], select[name$="[salutationId]"]'
);

// Auge-Toggle an allen Passwortfeldern
PluginManager.register('ObPasswordToggle', ObPasswordTogglePlugin, 'input[type="password"]');

// Bestellnummer kopieren (Finish-Seite)
PluginManager.register('ObCopyText', ObCopyTextPlugin, '[data-ob-copy-text]');

# Only Bubbles Theme (Shopware 6)

Theme-Plugin für den **Only Bubbles** Sales Channel. Läuft über dasselbe
Shopware-6-Backend wie *Fenskes Weine & Feinkost*, nur als eigener
Verkaufskanal mit eigenem Theme.

Der Aufbau ist bewusst identisch zum bestehenden `WeineFeinkostTheme`:
Das Design wird über zwei SCSS-Dateien gesteuert (kein Backend-`config`-Block).

## Struktur

```
composer.json                                     Plugin-Metadaten
src/
├── OnlyBubblesTheme.php                           Plugin-Klasse (implements ThemeInterface)
└── Resources/
    ├── theme.json                                Theme-Konfiguration (Styles/Scripts/Assets)
    └── app/storefront/src/
        ├── scss/
        │   ├── overrides.scss                    Variablen-Overrides (VOR @Storefront)
        │   └── base.scss                         Eigenes Styling (NACH @Storefront)
        ├── main.js                               JS-Entry (aktuell nur Standard)
        └── assets/                               Logo, preview.png, Fonts, Bilder
```

## Lade-Reihenfolge der Styles (aus `theme.json`)

1. `overrides.scss` – überschreibt Bootstrap-/Shopware-SCSS-Variablen
2. `@Storefront` – Shopware Standard-Styles
3. `base.scss` – eigenes Styling obendrauf

## Installation im Shopware-Backend

```bash
# Plugin nach custom/plugins/OnlyBubblesTheme kopieren, dann:
bin/console plugin:refresh
bin/console plugin:install --activate OnlyBubblesTheme
bin/console theme:compile
```

Anschließend im Admin: **Sales Channel → Theme** → "Only Bubbles Theme"
dem Only-Bubbles-Verkaufskanal zuweisen und Theme zuweisen/speichern.

## Design einpflegen (TODO)

Die Dateien `overrides.scss` und `base.scss` enthalten aktuell nur
Platzhalter. Hier kommen die echten Only-Bubbles-Designwerte (Farben,
Fonts) bzw. die angepassten Inhalte aus dem Fenskes-Theme rein.

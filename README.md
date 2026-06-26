# Only Bubbles — Shopware 6 Sales-Channel-Theme

Marken-Theme („Brand-Layer") für den Verkaufskanal **Only Bubbles** (Schaumweine).
Es nutzt **dasselbe Backend** wie dein bestehender Shop und legt nur die Optik
darüber: **Gold-Akzent**, **Cormorant Garamond + Jost**, **Bläschen-Logo**.
Struktur, Layout und Komponenten kommen aus dem Shopware-Storefront bzw. deinem
vorhandenen Theme.

> Wichtig: Die **Produkt-Zuweisung** passiert NICHT im Theme, sondern im Admin —
> du fügst die Schaumwein-Produkte dem Sales Channel „Only Bubbles" hinzu.
> Das Theme bestimmt nur das Aussehen dieses Kanals.

---

## Inhalt

```
OnlyBubblesTheme/
├─ composer.json
└─ src/
   ├─ OnlyBubblesTheme.php                 # Theme-Plugin-Klasse (ThemeInterface)
   └─ Resources/
      ├─ theme.json                        # Theme-Definition + Admin-Konfig (Farben/Schriften)
      ├─ app/storefront/src/
      │  ├─ main.js                        # JS-Einstieg (aktuell leer = erbt Storefront)
      │  ├─ scss/
      │  │  ├─ overrides.scss              # Bootstrap-Variablen VOR @Storefront
      │  │  └─ base.scss                   # Markenschicht NACH @Storefront
      │  └─ assets/logo/only-bubbles-logo.svg
      └─ views/storefront/layout/header/logo.html.twig   # Bläschen-Logo im Header
```

## Voraussetzungen

- Shopware **6.7** (Versionsbereich in `composer.json` ggf. an deine Installation anpassen, z. B. `~6.6.0`).
- Node-Build-Tooling der Storefront (für `theme:compile` / Asset-Build).

## Installation

```bash
# 1) Plugin in den Plugin-Ordner legen
cp -r OnlyBubblesTheme <shopware>/custom/plugins/OnlyBubblesTheme

# 2) Plugin registrieren, installieren & aktivieren
bin/console plugin:refresh
bin/console plugin:install --activate OnlyBubblesTheme

# 3) Theme registrieren
bin/console theme:refresh
```

## Dem Sales Channel zuweisen

1. Admin → **Verkaufskanäle** → „Only Bubbles" anlegen (oder bestehenden wählen).
2. Reiter **Theme** → **OnlyBubblesTheme** auswählen und zuweisen.
3. Farben/Schriften lassen sich direkt in der Theme-Konfiguration feinjustieren
   (Block „Only Bubbles — Farben / Schriften").

```bash
# 4) Storefront-Assets bauen & Theme kompilieren
./bin/build-storefront.sh           # oder: shopware-cli project storefront build
bin/console theme:compile
bin/console cache:clear
```

## Produkte zuordnen

Admin → Produkt → Reiter **Verkaufskanäle** → „Only Bubbles" aktivieren
(bzw. über Kategorien/Dynamic Product Groups dem Kanal zuweisen).

## Anpassen

- **Farben/Schriften:** `theme.json` (Defaults) oder Admin-Theme-Konfiguration.
- **Optik-Feinschliff:** `app/storefront/src/scss/base.scss` (nutzt die Selektoren
  des Storefronts/Eltern-Themes; hier nur Marke überschreiben).
- **Logo:** `assets/logo/only-bubbles-logo.svg` ersetzen, oder den Twig-Override
  `views/storefront/layout/header/logo.html.twig` löschen und das Logo klassisch
  über die Medien-Konfiguration setzen.

## Hinweis

Diese Dateien sind das **Quellcode-Gerüst** des Themes. Das Bauen/Kompilieren
und Aktivieren erfolgt in deiner Shopware-Umgebung (CLI-Befehle oben) — das kann
ich hier nicht für dich ausführen.

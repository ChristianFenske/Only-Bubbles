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
      │  ├─ main.js                        # JS-Einstieg (registriert die ob-*-Plugins)
      │  ├─ plugin/                        # Segmented Pills, Passwort-Auge, Kopieren
      │  ├─ scss/
      │  │  ├─ overrides.scss              # Bootstrap-Variablen VOR @Storefront
      │  │  ├─ base.scss                   # Markenschicht NACH @Storefront
      │  │  └─ ob/                         # Konto & Checkout (Tokens, Komponenten, Seiten)
      │  └─ assets/logo/only-bubbles-logo.svg
      ├─ snippet/                          # Texte DE/EN (Namespace obTheme.*)
      └─ views/storefront/
         ├─ layout/header/logo.html.twig   # Bläschen-Logo im Header
         ├─ component/ob/                  # Schrittanzeige, Trust-Box, Abschnitts-Kopf
         ├─ component/account/             # Login- und Registrierungsformular
         ├─ page/account/                  # Anmelden, Mein Konto (Übersicht, Profil, Adressen, Bestellungen)
         └─ page/checkout/                 # Versandinformationen, Bestellung abschließen, abgeschlossen
```

## Konto & Checkout

Umsetzung des Design-Handoffs „Konto & Checkout“ per Twig-Block-Override + SCSS.
Shopware-Logik (Formular-Validierung, Adress-Modal, Bestellabschluss) bleibt unverändert.

| Seite | Route | Template |
|---|---|---|
| Anmelden / Konto erstellen | `/account/login` | `page/account/register/index.html.twig`, `component/account/login.html.twig`, `component/account/register.html.twig` |
| Versandinformationen | `/checkout/register` | `page/checkout/address/index.html.twig`, `page/checkout/address/register.html.twig` |
| Bestellung abschließen | `/checkout/confirm` | `page/checkout/confirm/index.html.twig`, `confirm-address.html.twig` |
| Bestellung abgeschlossen | `/checkout/finish` | `page/checkout/finish/*.html.twig` |
| Mein Konto | `/account`, `/account/profile`, `/account/address`, `/account/order` | `page/account/*.html.twig` |

**JavaScript** (`app/storefront/src/plugin/`):
- `ObSegmentedSelect` — Kontotyp und Anrede als Segmented Pills. Das Original-`<select>` bleibt
  im Formular (Name, Validierung, Firma-Felder-Toggle) und wird nur visuell versteckt.
- `ObPasswordToggle` — Auge-Button an allen Passwortfeldern.
- `ObCopyText` — Bestellnummer kopieren auf der Abschluss-Seite.

**Theme-Konfiguration** (Admin → Theme → Block „Only Bubbles — Links“):
- *Link „Bestellung widerrufen“* — URL der Widerrufs-Formularseite (CMS-Seite/Formular). Leer = Menüpunkt aus.
- *Link „Als Händler registrieren“* — URL der Händler-Registrierung. Leer = Button aus.

**Hinweise:**
- Vorkasse-Hinweis auf der Abschluss-Seite erscheint nur bei Zahlungsart Vorkasse
  (`technicalName = payment_prepayment` bzw. Handler `PrePayment`).
- Nur Standard-Versand anbieten: im Admin unter Einstellungen → Versand (nicht im Theme).
- Nach Änderungen an JS: `./bin/build-storefront.sh`, danach `bin/console theme:compile`.

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

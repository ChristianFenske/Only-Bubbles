# Only Bubbles Theme — Konto & Checkout

Erweiterung des Only-Bubbles-Themes (Basis: WeineFeinkostTheme) um die Seiten aus dem
Design-Handoff „Konto & Checkout“. Umsetzung per Twig-Block-Override + SCSS; die
Shopware-Logik (Validierung, Adress-Modal, Bestellabschluss) bleibt unverändert.
Getestet mit Shopware 6.7.2 und Block-Namen geprüft gegen 6.7.14.

| Seite | Route | Template |
|---|---|---|
| Anmelden / Konto erstellen | `/account/login` | `page/account/register/index.html.twig`, `component/account/login.html.twig`, `component/account/register.html.twig` |
| Versandinformationen | `/checkout/register` | `page/checkout/address/*.html.twig` |
| Bestellung abschließen | `/checkout/confirm` | `page/checkout/confirm/*.html.twig` |
| Bestellung abgeschlossen | `/checkout/finish` | `page/checkout/finish/*.html.twig` |
| Mein Konto | `/account`, `/account/profile`, `/account/address`, `/account/order` | `page/account/*.html.twig` |

Gemeinsame Bausteine: `views/storefront/component/ob/` (Schrittanzeige, Trust-Box, Abschnitts-Kopf).
Styles: `app/storefront/src/scss/ob/` (am Ende von `base.scss` importiert; Farben aus den
`$color_*`-Variablen in `base.scss`). Texte: `snippet/onlybubbles.de-DE.json` / `.en-GB.json`
(Namespace `obTheme.*`).

## JavaScript

`app/storefront/src/main.js` registriert:
- `ObSegmentedSelect` — Kontotyp und Anrede als Segmented Pills; „Privat“ ist vorausgewählt.
  Das Original-`<select>` bleibt im Formular (Name, Validierung, Firmenfelder-Toggle).
- `ObPasswordToggle` — Auge-Button an Passwortfeldern.
- `ObCopyText` — Bestellnummer kopieren auf der Abschluss-Seite.

Das gebaute JS liegt unter `app/storefront/dist/…/only-bubbles-theme.js` und ist in
`theme.json` unter `script` eingetragen. Nach Änderungen an JS: `./bin/build-storefront.sh`.

## Theme-Konfiguration

Admin → Verkaufskanal → Theme → Block „Only Bubbles — Links“:
- **Link „Bestellung widerrufen“** — URL der Widerrufs-Formularseite. Leer = Menüpunkt aus.
- **Link „Als Händler registrieren“** — URL der Händler-Registrierung. Leer = Button aus.

## Installation / Update

```bash
bin/console plugin:refresh
bin/console plugin:update OnlyBubblesTheme   # bzw. plugin:install --activate
bin/console theme:compile
bin/console cache:clear
```

Hinweise: Vorkasse-Hinweis auf der Abschluss-Seite nur bei Zahlungsart Vorkasse.
Checkout-Seiten zeigen den vollen Shop-Header/-Footer. „Nur Standard-Versand“ wird im
Admin unter Versandarten eingestellt.

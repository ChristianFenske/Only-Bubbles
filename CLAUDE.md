# Projekt: Only Bubbles – Produkt- und Herstellertexte (Master)

Stand: 10.10.2026 · Ausführung: Claude Code + Claude in Chrome · Freigabe: Christian Fenske
Ersetzt die bisherigen Dateien CLAUDE.md (Produkte) und CLAUDE-Hersteller.md (Ablage: `archiv/`).

---

## 0. Die drei Grundregeln (vor jeder Aktion)

1. **weine-feinkost.de bleibt unverändert.** In der Sprache „Deutsch" wird NICHTS gespeichert –
   keine Beschreibung, keine Meta-Daten, keine Zusatzfelder, auch keine Fehlerkorrektur.
   Fehler im weine-feinkost-Text werden nur gemeldet (Abschnitt 12, „Gefundene WF-Fehler").
2. **Alles für Only Bubbles nur in der Sprache „Deutsch – Only Bubbles".**
3. **Nichts einsetzen ohne Freigabe.** Freigabe gilt pro Paket (Abschnitt 3).

Login macht Christian. Claude gibt niemals Zugangsdaten ein. Bei Login-Seite oder
„Session timeout": anhalten und Christian bitten, sich neu anzumelden.
Nie Daten löschen. Keine Einstellungen außerhalb der Produkte und Hersteller ändern.

---

## 1. Ziel und Marke

Only Bubbles (onlybubbles.de) ist eine eigenständige Schaumwein-Marke: modern, frisch, urban,
Lifestyle mit Substanz. Nach außen kein Ableger von Fenske's Weine & Feinkost.

Für jeden Hersteller und jedes Produkt im Verkaufskanal „Only Bubbles": eigener Text,
eigener Meta-Titel, eigene Meta-Beschreibung. Grundlage: Daten und Texte von weine-feinkost.de
(nur lesen) plus eigene Recherche.

Die Texte müssen
- für Leser hilfreich und kauforientiert sein,
- für Google (klassische Suche, AI Overviews, AI Mode) und KI-Suchen zitierfähig sein,
- sich inhaltlich und sprachlich klar von weine-feinkost.de unterscheiden.

**Bereits erledigt (nicht erneut bearbeiten):** Hersteller Franck Bonville,
Produkt Franck Bonville Grand Cru Blanc de Blancs Brut (SW11832). Beide sind Referenz für Ton und Aufbau.

---

## 2. Technische Grundlage

- weine-feinkost.de und Only Bubbles laufen in DERSELBEN Shopware-6.7-Installation.
  Admin: https://onlybubbles.de/admin (identisch mit weine-feinkost.de/admin).
- Sprache „Deutsch – Only Bubbles" erbt von „Deutsch". Leere Felder zeigen den weine-feinkost-Text.
- Only Bubbles ist noch im Wartungsmodus. Christians Chrome sieht die Live-Seiten (IP-Freigabe).

### 2.1 Felder pro Produkt (Kataloge → Produkte)

| Feld | Ort im Admin | Ausgabe |
|---|---|---|
| Beschreibung | Allgemein → Beschreibung | JSON-LD, Beschreibungs-Tab, Snippets in Listings und Cross-Selling |
| Only Bubbles Beschreibung Text (`only_bubbles_description_text`) | Spezifikationen → Zusatzfelder → Reiter „Only Bubbles Beschreibung" | sichtbarer Bereich „Zum Wein" |
| Meta-Titel | SEO → Meta-Tags | `<title>`, OG-/Twitter-Titel |
| Meta-Beschreibung | SEO → Meta-Tags | Meta Description, OG-/Twitter-Beschreibung |

Beschreibung und Zusatzfeld bekommen **exakt dasselbe HTML**.

### 2.2 Felder pro Hersteller (Kataloge → Hersteller)

| Feld | Ort im Admin | Hinweis |
|---|---|---|
| Beschreibung | Herstellerinformationen → Beschreibung | komplettes HTML |
| Meta-Titel | Zusatzfelder → Reiter „Biloba Manufacturer Pro" → Meta-Titel | Vererbung lösen, dann eintragen |
| Meta-Beschreibung | Zusatzfelder → Reiter „Biloba Manufacturer Pro" → Meta-Beschreibung | wie Meta-Titel |

Der Herstellertext erscheint auf onlybubbles.de im Produktbereich „Zum Winzer" (nur der Teil
**vor dem ersten `<hr>`**), im Produkt-Reiter „Hersteller …" (ganz) und auf der Herstellerseite.

### 2.3 Nicht ändern

Produkt: Name, Hersteller, Preis, Eigenschaften, Steckbrief, Kategorien, Sichtbarkeit, Bilder,
Schlüsselwörter, Cross-Selling. Ausnahme Hauptkategorie (Abschnitt 9).
Hersteller: Name, Webseite, Logo, Schlüsselwörter, Robots, Canonical URL (bleibt leer, zeigt nie
auf weine-feinkost.de), Link-Ziel, Erlebniswelt, alle anderen Zusatzfelder.

---

## 3. Arbeitsablauf: Paket = ein Hersteller + seine Produkte

### 3.1 Arbeitsliste

Datei `progress.csv` mit Spalten:
`Typ;Paket;Hersteller;Produktnummer;Name;Status;Datum;Notiz`
- Typ: `Hersteller` oder `Produkt`. Paket: laufende Nummer.
- Status: `offen` · `Entwurf` · `freigegeben` · `eingesetzt` · `geprüft` · `Problem`.
- Fehlt die Datei: im Admin Kataloge → Produkte nach Verkaufskanal „Only Bubbles" filtern,
  alle Produkte (inkl. Varianten-Hauptprodukte, Magnums, Sets) erfassen, nach Hersteller gruppieren.
  Reihenfolge: Hersteller mit den meisten Only-Bubbles-Produkten zuerst.
  Liste Christian einmal zur Kontrolle zeigen (Anzahl Hersteller, Anzahl Produkte).
- Hat ein Hersteller mehr als 6 Produkte: in Pakete à max. 6 Produkte teilen. Der Herstellertext
  kommt ins erste Paket.
- Nach jedem eingesetzten Element die Datei sofort aktualisieren.

### 3.2 Ablauf pro Paket

1. **Quellen lesen (nur lesen, Sprache „Deutsch"):**
   Hersteller: Beschreibung, Biloba-Meta-Daten, Webseite, Abfüller-Angaben.
   Je Produkt: Beschreibung, Eigenschaften, Steckbrief (Rebsorten, Region, Dosage/Restzucker,
   Ausbau, Hefelager, Bio-Kontrollnummer), Meta-Daten, aktuelle Hauptkategorie im Kanal Only Bubbles.
   Vorher prüfen, ob in „Deutsch – Only Bubbles" schon eigene Werte stehen. Wenn ja: melden,
   nicht ungefragt überschreiben.
2. **Recherche:** offizielle Website des Erzeugers, seriöse Fachquellen, Importeur-Datenblätter.
   Nur prüfbare Fakten. Marketingtexte nicht übernehmen, Kritikerzitate nur sinngemäß mit Quelle
   und Jahr. Widersprechen sich Quellen: Steckbrief im Shop gilt, Widerspruch melden.
3. **Schreiben:** Hersteller nach Abschnitt 6, Produkte nach Abschnitt 5. Hersteller zuerst,
   damit Produkt- und Herstellertexte sich ergänzen statt wiederholen.
4. **Abgleich:** jeden Text Satz für Satz gegen den weine-feinkost-Text prüfen (Abschnitt 7).
   Zusätzlich die Texte des Pakets untereinander: keine wiederholten Hooks, Janus-Sätze oder Fragen.
5. **Vorlage im Chat** (ein Paket = eine Nachricht bzw. eine Datei `pakete/paket-XX.md`):
   - Übersichtstabelle: Element · Meta-Titel (Zeichen) · Meta-Beschreibung (Zeichen) · Wörter · Janus ja/nein.
   - Je Element: komplettes HTML, Meta-Titel, Meta-Beschreibung.
   - Faktenliste mit Quelle je Zahl.
   - Abgleich-Ergebnis in einem Satz je Element.
   - Hauptkategorie-Vorschlag, falls nötig (Abschnitt 9).
   - Offene Punkte und gefundene WF-Fehler.
6. **Freigabe:** Christian gibt das Paket frei („Paket X frei") oder nennt Korrekturen.
   Teilfreigaben möglich („frei außer SW12345"). Nur Freigegebenes einsetzen.
7. **Einsetzen** nach Abschnitt 10, Hersteller zuerst, dann die Produkte.
8. **Prüfen** nach Abschnitt 10.4, `progress.csv` aktualisieren, kurze Erfolgsmeldung
   (eine Zeile pro Element). Dann nächstes Paket vorbereiten.

---

## 4. Stil (für alle Texte)

- Du-Ansprache. Modern, frisch, kurz, bildhaft, konkret. Kein Weinlexikon-Ton.
- Lifestyle mit Substanz: Anlass, Moment, Food, Serviertipp – immer passend zum Wein.
  Beispiele: Aperitivo, Dinner mit Freunden, Sonntagsbrunch, Afterwork, Picknick, Austern-Abend.
- Fachbegriffe (Méthode Traditionnelle, Blanc de Blancs, Brut Nature, Hefelager, Dosage,
  Récoltant-Manipulant) kurz und locker einordnen.
- Keine Floskeln: „ein wahrer Genuss", „ein Muss für jeden", „perfekt für jeden Anlass",
  „lassen Sie sich verzaubern", „ein Erlebnis für alle Sinne", „Tradition trifft Moderne",
  „mit viel Liebe zum Detail", „Leidenschaft in jeder Flasche".
- Produkte: 180–260 Wörter (ohne Faktenzeile). Hersteller: 250–400 Wörter (ohne Pflichtangaben).
- Jeder Text muss sich so lesen, als hätte ein Mensch genau diesen Wein probiert. Keine
  austauschbaren Sätze, die auf jeden Champagner passen.

---

## 5. Produkt: HTML-Struktur

```html
<div class="ob-product">
  <p class="ob-hook">[1–2 Sätze Einstieg: Moment/Gefühl, Produktname und Erzeuger enthalten]</p>
  <h3>Im Glas</h3>
  <p>[Farbe, Mousseux, Aromen, Gaumen, Stil – konkret und sinnlich]</p>
  <h3>Mach was draus</h3>
  <p>[Anlass, Food-Pairing, Serviertemperatur, Glas]</p>
  <div class="ob-janus" style="margin:28px 0;padding:20px 22px;background:#f7f2ea;">
    <p class="ob-janus-label" style="margin:0 0 12px;font-size:14px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8a6a3c;"><img class="ob-janus-img" src="https://onlybubbles.de/media/f4/3a/65/1790833159/janus-fenske-rund.png" alt="Janus Fenske" width="56" height="56" style="width:56px;height:56px;margin:0 14px 0 0;vertical-align:middle;">Janus empfiehlt</p>
    <p class="ob-janus-quote" style="margin:0;font-size:19px;font-style:italic;line-height:1.5;">„[Empfehlung, Ich-Form, 2–3 Sätze]“</p>
    <p class="ob-janus-more" style="margin:12px 0 0;font-size:14px;"><a href="/Service/Janus-Fenske/">Mehr über Janus, unseren Sommelier</a></p>
  </div>
  <h3>Kurz gefragt</h3>
  <p><strong>[Frage 1]</strong> [Antwort in 1–2 Sätzen, mit konkretem Wert]</p>
  <p><strong>[Frage 2]</strong> [Antwort in 1–2 Sätzen]</p>
  <p class="ob-facts">[Rebsorten mit Anteil · Herkunft/Lage · Methode · Hefelager · Dosage/Restzucker · Erzeuger]</p>
</div>
```

Regeln:
- **Hook:** Produktname und Erzeuger im ersten Satz, für sich allein verständlich.
- **Janus empfiehlt:** nur aus der weine-feinkost-Produktbeschreibung ableiten (Serviertipp, Glas,
  Pairing, Besonderheit), neu formuliert. Nichts hinzuerfinden. Steht nichts Passendes in der
  Quelle: ganzen `ob-janus`-Block weglassen und melden.
- **Kurz gefragt:** 2 (max. 3) echte Käuferfragen: Süße/Dosage, Lagerfähigkeit, Unterschied zu
  einem verwandten Stil, Anlass, Menge pro Person. Konkrete Werte aus Steckbrief oder Quelle.
  Kein FAQ-Schema. Nicht dieselben Fragen wie im Herstellertext.
- **Faktenzeile:** nur geprüfte Fakten, kein Alkoholgehalt. Bio-Artikel: Öko-Kontrollnummer.
- **Varianten/Magnum/Sets:** eigener Text mit eigenem Blickwinkel (z. B. Magnum: Runde, Fest,
  Reifung im großen Format), kein Kopieren des 0,75-l-Textes.

---

## 6. Hersteller: HTML-Struktur

```html
<div class="ob-producer">
  <p class="ob-producer-hook">[2–3 Sätze: Name des Hauses, Ort/Region, Stil in einem Bild. Teaser auf jeder Produktseite, für sich allein verständlich.]</p>
  <hr>
  <h3>Das Haus</h3>
  <p>[Wer steht dahinter, was macht das Haus besonders – Status, Größe, Generation, Arbeitsweise.]</p>
  <h3>Der Stil im Glas</h3>
  <p>[Typischer Stil und warum – Lagen, Rebsorten, Ausbau, Dosage, locker erklärt.]</p>
  <h3>Unsere Auswahl</h3>
  <p>[Nur Cuvées, die im Kanal Only Bubbles sichtbar sind. Je Cuvée ein Halbsatz zu Stil oder Anlass. Keine Preise.]</p>
  <div class="ob-janus" style="margin:28px 0;padding:20px 22px;background:#f7f2ea;">
    <p class="ob-janus-label" style="margin:0 0 12px;font-size:14px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8a6a3c;"><img class="ob-janus-img" src="https://onlybubbles.de/media/f4/3a/65/1790833159/janus-fenske-rund.png" alt="Janus Fenske" width="56" height="56" style="width:56px;height:56px;margin:0 14px 0 0;vertical-align:middle;">Janus über [Erzeuger]</p>
    <p class="ob-janus-quote" style="margin:0;font-size:19px;font-style:italic;line-height:1.5;">„[2–3 Sätze, Ich-Form]“</p>
    <p class="ob-janus-more" style="margin:12px 0 0;font-size:14px;"><a href="/Service/Janus-Fenske/">Mehr über Janus, unseren Sommelier</a></p>
  </div>
  <h3>Kurz gefragt</h3>
  <p><strong>[Frage 1]</strong> [Antwort in 1–2 Sätzen]</p>
  <p><strong>[Frage 2]</strong> [Antwort in 1–2 Sätzen]</p>
  <p class="ob-facts">[Region · Ort · gegründet · Status · Rebfläche · Zertifizierung]</p>
  <p class="ob-legal" style="margin:24px 0 0;font-size:13px;line-height:1.5;"><strong>Name und Anschrift des Erzeugers/Abfüllers:</strong><br>[aus der Quelle unverändert]</p>
</div>
```

Regeln:
- **Hook:** Name des Hauses im ersten Satz.
- **Janus über …:** nur Aussagen aus der weine-feinkost-Quelle oder aus den Produkttexten dieses
  Erzeugers, neu formuliert. Nichts Passendes: Block weglassen und melden.
- **Kurz gefragt:** andere Fragen als im weine-feinkost-FAQ (dort z. B. „Wo liegt das Weingut?",
  „Seit wann gibt es das Haus?"). Beispiele: „Welche Cuvée passt für den Einstieg?",
  „Wie trocken sind die Weine des Hauses?", „Was unterscheidet einen Winzerchampagner von einem
  großen Haus?", „Arbeitet das Haus biologisch?".
- **Pflichtangaben:** Name und Anschrift aus der Quelle unverändert. Fehlen sie: melden, nicht
  selbst recherchieren und einsetzen.
- **Unsere Auswahl:** nur Cuvées, die im Kanal Only Bubbles sichtbar sind (gegen `progress.csv` prüfen).

### Gemeinsame HTML-Regeln

- Überschriften `h3` (Theme gibt „Detailbeschreibung" als `h2` aus).
- Shopware-HTML-Filter entfernt u. a. `display:flex`, `gap`, `border-radius`, `object-fit`,
  `flex`, `loading`. Erlaubt: `margin`, `padding`, `background`, `color`, `font-size`,
  `font-weight`, `font-style`, `line-height`, `letter-spacing`, `text-transform`, `width`,
  `height`, `vertical-align`.
- Bild-URL, Inline-Styles, Klassennamen und den Link „Mehr über Janus" (Autorenseite, E-E-A-T)
  nie verändern. Das Janus-Bild ist bereits rund zugeschnitten.
- Immer komplettes HTML liefern und setzen, nie Teilstücke.

---

## 7. Unterschied zu weine-feinkost.de (Pflicht)

Ziel ist eigener Mehrwert, nicht Umformulieren. Synonym-Tausch oder Satzumstellung = Spinning = verboten.

- **Anderer Blickwinkel:** weine-feinkost.de = Terroir, Geologie, Handwerk, Hausgeschichte, FAQ.
  Only Bubbles = Moment, Anlass, Geschmack, Food, Servieren, Entscheidungshilfe.
- **Andere Struktur und Reihenfolge** (Abschnitte 5 und 6).
- **Kein identischer Satz, keine gleiche Folge von 5 oder mehr Wörtern.** Ausgenommen: Produkt-,
  Erzeuger-, Orts-, Lagen-, Cuvée- und Rebsortennamen, feste Fachbegriffe, Pflichtangaben.
  Prüfung per Skript (5-Wort-Shingles gegen den WF-Text), Ergebnis in der Vorlage nennen.
- **Eigener Mehrwert pro Text:** mindestens ein fachlich korrektes Element, das auf
  weine-feinkost.de fehlt (Anlass, Pairing, Antwort in „Kurz gefragt").
- **Andere Meta-Daten:** anderer Keyword-Fokus (Anlass/Stil statt Herkunft), kein Satz aus der
  WF-Meta-Beschreibung, kein „kaufen bei Fenske's".
- **Keine Bezüge zu Fenske's:** kein „Unsere Hausmarke", kein Wuppertal, kein Ladengeschäft,
  keine Links zu weine-feinkost.de. Ausnahme: die Janus-Seite (Link fest im Baustein).

---

## 8. Meta-Daten

- **Produkt-Meta-Titel:** 50–60 Zeichen, Pixelanzeige im Admin unter 580 px.
  Muster: `[Erzeuger] [Cuvée] – [Stil/Anlass] | Only Bubbles`.
- **Hersteller-Meta-Titel:** 50–60 Zeichen. Muster: `[Erzeuger]: [Stil/Region kurz] | Only Bubbles`
  (Referenz: „Franck Bonville: Blanc de Blancs aus Avize | Only Bubbles").
- **Meta-Beschreibung (beide):** 140–155 Zeichen. Nutzen + Geschmack + Anlass + Handlungsimpuls.
  Ehrlich, keine Superlative ohne Beleg.
- Zeichen per Skript zählen, nicht schätzen.
- Schlüsselwörter-Felder nicht anfassen.

---

## 9. Hauptkategorie im Kanal Only Bubbles (Teil der Paketfreigabe)

Ist für den Kanal Only Bubbles eine unpassende Hauptkategorie gesetzt (z. B. „Entdecken >
Empfehlungen") oder keine, im Paket die passende vorschlagen (z. B. „Champagne"). Sie bestimmt
Breadcrumb und URL-Kontext.
Ort: Produkt → SEO → Karte „SEO-URLs" → Verkaufskanal „Only Bubbles" wählen → Hauptkategorie.
Auswählbar sind nur Kategorien, denen das Produkt zugeordnet ist. Nie „Entdecken"/„Empfehlungen",
nie eine weine-feinkost-Kategorie. Ändern nur, wenn im freigegebenen Paket enthalten, und nur für
den Kanal Only Bubbles. Passt keine zugeordnete Kategorie: melden, keine Kategorien zuordnen.

---

## 10. Einsetzen und Prüfen im Browser

### 10.1 Sprache setzen (jedes Mal)

1. Produkt bzw. Hersteller öffnen.
2. Sprachauswahl oben rechts auf **„Deutsch – Only Bubbles"**.
3. Prüfen, dass der Hinweis „Darstellung von … in der Sprache ‚Deutsch – Only Bubbles'. Diese erbt
   von …" sichtbar ist. **Fehlt er: abbrechen, nichts speichern.**
4. Erscheint beim Sprachwechsel „Nicht gespeicherte Änderungen": „Änderungen verwerfen" (nur wenn
   bewusst nichts ungespeichert ist).
5. Vor dem Speichern noch einmal die Sprachauswahl per Screenshot kontrollieren.

### 10.2 HTML setzen

- Code-Ansicht öffnen (Symbol `</>` rechts in der Editor-Leiste).
- Prüfen, dass genau ein `.ace_editor` existiert und er zum richtigen Feld gehört.
- `ace.edit(document.querySelector('.ace_editor')).setValue(html, -1)`
- Zurück in die normale Ansicht schalten, erst dann weiter.
- Produkt: zuerst Allgemein → Beschreibung, dann Spezifikationen → Zusatzfelder → Reiter
  „Only Bubbles Beschreibung" (Reiter-Klick per Screenshot kontrollieren, trifft manchmal den falschen).
- Zusatzfelder mit lila Verknüpfungssymbol sind geerbt: ein Klick auf das Symbol löst die Vererbung.

### 10.3 Meta-Daten, Hauptkategorie, Speichern

- Produkt: SEO → Meta-Titel, Meta-Beschreibung; ggf. Hauptkategorie (Abschnitt 9).
- Hersteller: Reiter „Biloba Manufacturer Pro" → Vererbung lösen → Meta-Titel, Meta-Beschreibung.
- Speichern. Erfolgsmeldung abwarten.

### 10.4 Prüfen nach dem Speichern

1. Seite neu laden, Sprache wieder „Deutsch – Only Bubbles".
2. Alle Felder per `getValue()` bzw. Feldinhalt prüfen: `class="ob-product"` bzw. `ob-producer`,
   `ob-janus-img` (falls Janus-Block), `font-size:19px`, Hook-Text, Meta-Titel, Meta-Beschreibung.
3. Sprache „Deutsch" nur ansehen: WF-Beschreibung und WF-Meta-Daten unverändert. **Nichts speichern.**
   Falls dabei „Nicht gespeicherte Änderungen" erscheint: „Änderungen verwerfen".
4. Live-Prüfung auf onlybubbles.de (Produkt- bzw. Herstellerseite): `<title>`, Meta Description,
   Bereich „Zum Wein"/„Zum Winzer", Janus-Block sichtbar, kein „Hausmarke", kein Fenske's-Bezug.
5. Stichprobe auf weine-feinkost.de: dieselbe Seite zeigt weiter den WF-Text.

### 10.5 Browser-Technik

- Elemente über `find` suchen, nicht über feste Koordinaten. Nach jedem Reiterwechsel Screenshot.
- JavaScript-Ausgaben mit URLs oder `? & =` können vom Chrome-Tool blockiert werden: Zeichen vor
  der Ausgabe ersetzen oder nur Prüfwerte (true/false, Längen) ausgeben.
- Lange Ausgaben in Stücken (max. ca. 1.000 Zeichen) lesen.
- Mehrere Schritte per `browser_batch` bündeln.
- Bei Fehlern nach 2–3 Versuchen anhalten und Christian fragen.

---

## 11. Recht, Werbung, Qualität

- Kein Alkoholgehalt (steht im Steckbrief). Keine Preise in Texten.
- Jahrgänge nur bei Jahrgangs-, Lagen- oder Prestige-Cuvées, wenn das Produkt ein Jahrgang ist.
- Keine Gesundheitsaussagen.
- Werberegeln für alkoholhaltige Getränke: keine Ansprache Minderjähriger, kein Bezug zu
  Leistungssteigerung, Enthemmung, sozialem oder sexuellem Erfolg, keine Verharmlosung von Menge,
  kein Trinken mit Autofahren oder Sport. Lifestyle heißt Genuss und Anlass, nicht Rausch.
- Bio/biodynamisch nur mit belegter Zertifizierung (Kontrollstelle oder Siegel).
- Auszeichnungen nur mit Quelle und Jahr, sinngemäß.
- Nichts erfinden. Jede Zahl braucht eine Quelle. Fehlt eine Angabe: weglassen und melden.
- Jeder Text wird vor dem Einsetzen von Christian geprüft (inkl. Meta-Daten und Alt-Texten).

---

## 12. Berichte

- `progress.csv` – Arbeitsstand (Abschnitt 3.1).
- `offene-punkte.md` – Quellenwidersprüche, fehlende Angaben, weggelassene Janus-Blöcke,
  fehlende Abfüller-Angaben, unpassende Hauptkategorien.
- **Gefundene WF-Fehler** (eigener Abschnitt in `offene-punkte.md`): Fehler im weine-feinkost-Text
  mit Produkt/Hersteller, falscher Aussage, korrekter Aussage, Quelle. **Nur melden, nicht korrigieren.**
- Nach jedem Paket: eine kurze Zusammenfassung im Chat (was eingesetzt, was offen).

---

## 13. Leitplanken (Recherchestand Oktober 2026)

- Google bewertet KI-Texte nicht als solche negativ. Massenhaft erzeugte Seiten ohne Mehrwert
  fallen unter die Spam-Richtlinie „Scaled Content Abuse". Deshalb: eigener Mehrwert pro Text,
  menschliche Prüfung, kein Schema-F.
- Seit 1. Oktober 2026 nennt Google die manuelle Faktenprüfung von KI-Inhalten ausdrücklich
  „kritisch", auch für Seitentitel, Meta-Beschreibungen und Bildbeschreibungen.
- AI Overviews und AI Mode nutzen denselben Index wie die klassische Suche. Keine
  Spezial-Optimierung (kein llms.txt, keine Mini-Absätze, kein „KI-Stil"). Entscheidend sind
  originelle, konkrete Inhalte mit Anlässen, Unterschieden und Fakten.
- Produktbeschreibungen mit menschlicher Prüfung und redaktioneller Verantwortung brauchen nach
  Art. 50 AI Act keine KI-Kennzeichnung.

# ObMailchimpSync – Newsletter-Empfänger nach Mailchimp

Überträgt Newsletter-Anmeldungen aus Shopware 6.7 automatisch in eine Mailchimp-Zielgruppe.

## Was passiert
- **Sofort:** Jede Anmeldung, Bestätigung (Double-Opt-in) oder Abmeldung wird direkt an Mailchimp geschickt.
  - bestätigt (`optIn` / `direct`) → wird in Mailchimp als neuer Kontakt „subscribed“ angelegt
  - abgemeldet (`optOut`) → ein vorhandener Mailchimp-Kontakt wird auf „unsubscribed“ gesetzt (abschaltbar)
  - unbestätigt (`notSet`) → wird **nicht** übertragen (Double-Opt-in bleibt in Shopware)
- **Täglich:** Komplett-Abgleich aller Empfänger (geplante Aufgabe `ob_mailchimp_sync.full_sync`).
  Läuft direkt nach der Installation zum ersten Mal und überträgt so auch alle bestehenden Empfänger.
- Fehler bei Mailchimp blockieren nie die Anmeldung im Shop; sie landen im Shopware-Log.

## Sicherheit: nichts wird gelöscht oder überschrieben
- Das Plugin **löscht nichts** – weder in Shopware noch in Mailchimp (kein DELETE-Aufruf).
- In Shopware wird **nur gelesen**, nie geschrieben.
- In Mailchimp werden **nur neue Kontakte angelegt**. Bestehende Kontakte bleiben unverändert
  (Status, Namen, Tags) – wer sich in Mailchimp abgemeldet hat, wird nie wieder angemeldet.
- Einzige Änderung an bestehenden Kontakten: Abmeldung in Shopware → „unsubscribed“ in Mailchimp.
- Deinstallation entfernt nur das Plugin und seine Einstellungen.

## Einrichtung
1. ZIP unter Erweiterungen → Meine Erweiterungen hochladen, installieren, aktivieren.
2. Konfigurieren – **oben den Verkaufskanal „Only Bubbles“ auswählen**:
   - Synchronisation aktiv: an
   - API-Key: Mailchimp → Profil → Extras → API-Keys (Format `xxxxxxxx-us21`)
   - Zielgruppen-ID: Mailchimp → Zielgruppe → Einstellungen → Zielgruppenname und Standardwerte
3. Speichern. Neue Anmeldungen gehen ab sofort durch, bestehende spätestens mit dem nächsten Komplett-Abgleich.

## Testen / Fehlersuche (ohne Server-Zugriff)
In der Plugin-Konfiguration (Verkaufskanal auswählen) gibt es die Karte **„Test & Abgleich“**:
1. „Jetzt prüfen und alle übertragen“ einschalten → **Speichern** → Seite neu laden.
2. Im Feld **„Letztes Ergebnis“** steht dann z. B.:
   „Verbindung OK – Zielgruppe „Only Bubbles“ (42 Kontakte). Shopware: 5 bestätigt, 1 unbestätigt, 0 abgemeldet.
   Übertragen: 4 neu angelegt, 1 schon in Mailchimp (unverändert), 0 abgemeldet.“
   oder eine verständliche Fehlermeldung (falscher API-Key, falsche Zielgruppen-ID …).
Fehler beim Sofort-Abgleich (z. B. Mailchimp lehnt eine Adresse ab) landen ebenfalls dort.

Optional per Konsole: `bin/console ob:mailchimp:sync` (sofortiger Komplett-Abgleich mit Ausgabe).

## Hinweise
- Je Verkaufskanal eigene Zielgruppe möglich (z. B. Weine & Feinkost getrennt).
- Vor-/Nachname gehen in die Standardfelder FNAME / LNAME.
- Geplante Aufgaben brauchen einen laufenden Cronjob bzw. den Admin-Worker.

# ObMailchimpSync – Newsletter-Empfänger nach Mailchimp

Überträgt Newsletter-Anmeldungen aus Shopware 6.7 automatisch in eine Mailchimp-Zielgruppe.

## Was passiert
- **Sofort:** Jede Anmeldung, Bestätigung (Double-Opt-in) oder Abmeldung wird direkt an Mailchimp geschickt.
  - bestätigt (`optIn` / `direct`) → Mailchimp „subscribed“
  - abgemeldet (`optOut`) → Mailchimp „unsubscribed“ (abschaltbar)
  - unbestätigt (`notSet`) → wird **nicht** übertragen (Double-Opt-in bleibt in Shopware)
- **Täglich:** Komplett-Abgleich aller Empfänger (geplante Aufgabe `ob_mailchimp_sync.full_sync`).
  Läuft direkt nach der Installation zum ersten Mal und überträgt so auch alle bestehenden Empfänger.
- Fehler bei Mailchimp blockieren nie die Anmeldung im Shop; sie landen im Shopware-Log.

## Einrichtung
1. ZIP unter Erweiterungen → Meine Erweiterungen hochladen, installieren, aktivieren.
2. Konfigurieren – **oben den Verkaufskanal „Only Bubbles“ auswählen**:
   - Synchronisation aktiv: an
   - API-Key: Mailchimp → Profil → Extras → API-Keys (Format `xxxxxxxx-us21`)
   - Zielgruppen-ID: Mailchimp → Zielgruppe → Einstellungen → Zielgruppenname und Standardwerte
3. Speichern. Neue Anmeldungen gehen ab sofort durch, bestehende spätestens mit dem nächsten Komplett-Abgleich.

Optional per Konsole: `bin/console ob:mailchimp:sync` (sofortiger Komplett-Abgleich mit Ausgabe).

## Hinweise
- Je Verkaufskanal eigene Zielgruppe möglich (z. B. Weine & Feinkost getrennt).
- Vor-/Nachname gehen in die Standardfelder FNAME / LNAME.
- Geplante Aufgaben brauchen einen laufenden Cronjob bzw. den Admin-Worker.

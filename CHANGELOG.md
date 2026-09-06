# Changelog: translator-Addon (LMOnext)

Übersetzungs-Editor: DE/EN/beliebige Sprachdateien side-by-side bearbeiten,
fehlende Keys ergänzen, optional automatisch via DeepL übersetzen.

Ursprünglich von Torsten Hofmann bereitgestellt. Vor der Übernahme
sicherheitsgeprüft - zwei kritische Path-Traversal/Local-File-Inclusion-
Schwachstellen gefunden und behoben (siehe unten).

## Version 1.0.6 (KRITISCHER Bugfix - Config geht bei jedem Update verloren)

- KRITISCHER Bugfix (gemeldet: nach dem Anlegen einer neuen Sprache und einem zwischenzeitlichen Addon-Update fehlte plötzlich der Auto-Übersetzen-Button, obwohl das Browser-Eingabefeld weiterhin einen gespeicherten API-Key anzeigte): die Konfigurationsdatei (.config.php mit dem DeepL API-Key) lag bisher INNERHALB des Addon-Ordners selbst. Ein Addon-Update tauscht das komplette Addon-Verzeichnis atomar aus (siehe AddonManager::atomicReplaceAddonDir() im Core) - jede zur Laufzeit erzeugte Datei, die nicht Teil des neuen ZIP-Inhalts ist, ging dadurch bei JEDEM Update verloren. Der Browser zeigte durch Passwort-Feld-Autofill weiterhin Punkte im Eingabefeld an, obwohl der Server den Key längst nicht mehr kannte - dadurch war das Problem zusätzlich schwer bemerkbar (der Nutzer dachte, der Key sei noch gespeichert).
- Fix (handler_translator.php 1.1.5): die Config liegt jetzt in einem eigenen, UPDATE-SICHEREN Verzeichnis außerhalb von addon/ (_addon_data/translator/config.php, analog zum bereits bestehenden _addon_backups-Muster im Core), das von einem Addon-Update nicht berührt wird. Automatische, einmalige Migration einer eventuell noch vorhandenen alten Config-Datei an den neuen Ort eingebaut. Neues Verzeichnis wird mit einer .htaccess (Deny from all) gegen direkten Web-Zugriff abgesichert.
- WICHTIG: dieser Fix verhindert das Problem nur für ZUKÜNFTIGE Updates - ein bereits verlorener API-Key (wie im gemeldeten Fall) kann dadurch nicht automatisch wiederhergestellt werden und muss einmalig erneut eingegeben werden.

## Version 1.0.5 (KRITISCHER Bugfix - DeepL-Authentifizierung)

- KRITISCHER Bugfix (gemeldet, dank der verbesserten Fehlermeldung aus Version 1.0.4 sofort erkennbar: "HTTP 403: Missing Authorization header, expected 'Authorization: DeepL-Auth-Key <API key>'"): DeepL akzeptiert den API-Key nicht mehr (nur) als auth_key-Feld im POST-Body - stattdessen wird jetzt ein eigener "Authorization: DeepL-Auth-Key <key>"-HTTP-Header verlangt (siehe https://developers.deepl.com/docs/getting-started/auth). translatorDeepLTranslate() (handler_translator.php 1.1.4) setzt diesen Header jetzt bei BEIDEN Übertragungswegen (curl UND file_get_contents-Fallback), auth_key wurde aus dem POST-Body entfernt. Der hochgeladene Screenshot bestätigte einen gültigen, aktiven, unrestricted Free-Tier-Key (endet korrekt auf ":fx") - das Problem lag ausschließlich am veralteten Authentifizierungsverfahren im Addon-Code, nicht am Key selbst oder am Nutzer-Setup.

## Version 1.0.4 (Bugfix + Diagnose)

- Bugfix (gemeldet: Auto-Übersetzen-Button zeigt "Fehlende EN übersetzen" auch wenn eine andere Zielsprache wie NL ausgewählt ist): der Button-Text war fest auf "EN" einprogrammiert (lang/de.php, lang/en.php: tr_btn_auto_translate), statt die tatsächlich ausgewählte Zielsprache anzuzeigen. Zeigt jetzt korrekt die gewählte Sprache (z.B. "Fehlende NL übersetzen").
- Bugfix (Nebenfund): der Bestätigungsdialog vor dem Auto-Übersetzen nutzte fälschlich denselben Text wie die Erfolgsmeldung NACH einer Übersetzung (tr_auto_translated, z.B. "5 Keys automatisch übersetzt.?" als Frage - falscher Sinn). Neuer, passender Schlüssel tr_auto_translate_confirm ergänzt.
- KRITISCHER Diagnose-Fix (gemeldet: Klick auf Auto-Übersetzen bewirkt nichts sichtbares, die Zieldatei bleibt leer, keine erkennbare Fehlermeldung): translatorDeepLTranslate() (handler_translator.php 1.1.3) gab bei JEDEM Fehler (falscher/ungültiger API-Key, Netzwerkproblem, falsche Domain, ...) nur ein leeres Array zurück - der Nutzer sah zwar eine Fehlermeldung, aber ohne jeden Hinweis auf die eigentliche Ursache. Liefert jetzt eine strukturierte Antwort mit konkretem Fehlergrund und HTTP-Statuscode. curl wird jetzt VOR file_get_contents versucht (liefert zuverlässig den Statuscode auch bei Fehlern, file_get_contents ohne besondere Konfiguration meist nur "false" ohne Detail). Neue, aussagekräftige Fehlermeldung (tr_error_translate_failed_detail) zeigt jetzt z.B. "HTTP 403: Authorization failed. Please supply a valid auth_key parameter." statt einer generischen Meldung - das macht z.B. einen ungültigen oder bei der falschen Stelle bezogenen API-Key sofort erkennbar.
- Verifiziert: die vom Nutzer hochgeladene, leer angelegte nl.php bestätigt, dass der vorherige kritische Fix (Version 1.0.3) das ursprüngliche Datenverlust-Problem korrekt behoben hat - nl.php wurde korrekt als eigene, separate Datei angelegt, nicht mehr in de.php geschrieben. Das verbleibende Problem (Auto-Übersetzung selbst liefert keine Werte) ist mit diesem Fix nun diagnostizierbar.

## Version 1.0.3 (KRITISCHER Datenverlust-Bugfix)

- KRITISCHER Bugfix (gemeldet: neue Sprache wurde nicht befüllt, stattdessen wurde die komplette Quellsprache mit Leerwerten überschrieben): das `<select id="tr-tgt-lang">`-Dropdown enthielt beim Anlegen einer neuen Sprache KEINE `<option>` für diese neue (noch nicht auf der Festplatte existierende) Sprache - es wird nur aus bereits vorhandenen Sprachdateien aufgebaut. Ein `<select>` ohne passende Option fällt im Browser automatisch auf die erste verfügbare Option zurück (typischerweise die Quellsprache selbst). Der JavaScript-Submit-Handler las diesen (falschen) Dropdown-Wert ungeprüft aus und sendete dadurch src_lang UND tgt_lang identisch - der Server schrieb daraufhin zweimal hintereinander in DIESELBE Datei (einmal mit den echten Quellwerten, direkt danach nochmal mit den leeren "Ziel"-Werten, die die zuvor geschriebenen Werte überschrieben).
- Fix 1 (view_translator.php 1.1.3): bei einer neu anzulegenden Sprache wird jetzt zusätzlich eine eigene, korrekt vorausgewählte `<option>` im Ziel-Dropdown gerendert.
- Fix 2 (handler_translator.php 1.1.2, Verteidigung in der Tiefe): neue serverseitige Sperre in 'save' UND 'auto_translate' - Quell- und Zielsprache dürfen nie identisch sein, sonst wird der Speichervorgang mit einer Fehlermeldung abgebrochen, unabhängig von der genauen Ursache.
- Fix 3 (handler_translator.php 1.1.2, generelle Datenverlust-Bremse): translatorSaveLangFile() vergleicht jetzt vor jedem Schreiben die Anzahl befüllter Werte mit der bereits vorhandenen Datei - fällt sie auf unter 20% des vorherigen Stands (bei mindestens 10 vorher befüllten Werten), wird der Schreibvorgang verweigert und nichts geändert. Schützt auch vor noch unbekannten, zukünftigen Bugs mit ähnlicher Wirkung.
- Neue Sprachschlüssel: tr_error_same_language, tr_error_data_loss_prevented.

## Version 1.0.2 (Bugfix)

- Bugfix (gemeldet: Klick auf "OK" beim Anlegen einer neuen Sprache tut nichts, nur die Eingabe von "Enter" im Feld funktionierte): der "OK"-Button (view_translator.php 1.1.2) rief fälschlich trOnLangChange() auf - diese Funktion prüft aber nur den DROPDOWN-Wert ("__new__"), nicht den tatsächlich eingegebenen Text im Eingabefeld. Der Button hat dadurch nie wirklich navigiert, sondern nur das bereits sichtbare Eingabefeld erneut "aktiviert" (fokussiert), ohne sichtbare Wirkung. Neue, gemeinsame Funktion trSubmitNewLang() ergänzt, die sowohl vom OK-Button als auch vom Enter-Tastendruck genutzt wird (vorher zwei unabhängige Code-Pfade, jetzt ein einziger) - liest den tatsächlichen Eingabefeld-Wert aus, validiert ihn client-seitig (2-3 Kleinbuchstaben, deckungsgleich mit der server-seitigen Prüfung aus Version 1.0.1) und navigiert bei gültiger Eingabe zur passenden URL. Bei ungültiger Eingabe wird das Feld jetzt fokussiert und der Inhalt markiert, statt stillschweigend nichts zu tun.

## Version 1.0.1 (Sicherheitsfix)

- KRITISCHER Bugfix (handler_translator.php 1.1.1, view_translator.php 1.1.1): translatorValidLangCode() (Muster ^[a-z]{2,3}$) wurde bisher nur beim Anlegen einer NEUEN Sprache aufgerufen, nicht beim Speichern/Anzeigen einer bereits bestehenden - src_lang/tgt_lang kamen in diesem Fall ungeprüft direkt aus $_POST bzw. $_GET in den Dateipfad. Zwei Angriffswege dadurch möglich:
  1. Path Traversal beim Schreiben (handler_translator.php, case 'save' und 'auto_translate'): ein manipulierter Sprachcode wie "../../../../config" hätte theoretisch beliebige .php-Dateien irgendwo im beschreibbaren Dateisystem erzeugen/überschreiben können.
  2. Local File Inclusion über GET-Parameter (view_translator.php, ?src=/?tgt=) - ohne CSRF-Schutz (GET-Requests) und schwerwiegender als reines Lesen, da translatorLoadLangFile() die Datei per require lädt, nicht nur einliest.
- Fix: translatorLangFilePath() validiert jetzt IMMER, an einer zentralen Stelle statt bei jedem Aufrufer einzeln, sodass die Prüfung strukturell nicht mehr vergessen werden kann. Zusätzlich realpath()-Gegenprobe (Verteidigung in der Tiefe): der aufgelöste Pfad muss exakt innerhalb des erwarteten Sprachverzeichnisses liegen. Rückgabetyp auf `?string` geändert, alle vier Aufrufstellen entsprechend angepasst (Handler: Fehlermeldung + redirect; View: stiller Fallback auf die sicheren Standardwerte de/en, passend zum rein lesenden Charakter dieser Datei).
- Verifiziert: alle vier Aufrufstellen geprüft, Validierungs-Regex gegen eine Reihe realistischer Angriffs-Payloads getestet (Path Traversal, Null-Byte, Sonderzeichen), gegen den Addon-Manager-Sicherheitsscanner simuliert getestet (keine Treffer).
- Keine funktionale Änderung für den normalen Gebrauch (gültige 2-3-stellige Sprachcodes wie "de", "en", "fr" funktionieren unverändert).

## Version 1.0.0

- Ursprüngliche Version von Torsten Hofmann.

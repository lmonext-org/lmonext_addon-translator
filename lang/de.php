<?php
/**
 * Project: LMOnext
 * Filename: addon/translator/lang/de.php
 * Fileversion: 1.0.0
 *
 * PHP version 8.2
 *
 * @copyright 2026 Torsten Hofmann
 * @license   GPL-3.0-only
 */

return [
    'nav_translator'           => 'Übersetzungen',
    'tr_title'                 => 'Übersetzungs-Editor',
    'tr_select_file'           => 'Sprachdatei wählen',
    'tr_core_group'            => 'Core',
    'tr_addon_group'           => 'Addons',
    'tr_de_column'             => 'Deutsch (DE)',
    'tr_en_column'             => 'Englisch (EN)',
    'tr_key_column'            => 'Schlüssel',
    'tr_status'                => 'Status',
    'tr_status_ok'             => 'OK',
    'tr_status_missing_en'     => 'EN fehlt',
    'tr_status_missing_de'     => 'DE fehlt',
    'tr_status_empty'          => 'Leer',
    'tr_btn_save'              => 'Speichern',
    'tr_btn_auto_translate'    => 'Fehlende {lang} übersetzen',
    'tr_btn_save_config'       => 'API-Key speichern',
    'tr_deepl_key'             => 'DeepL API-Key',
    'tr_deepl_hint'            => 'Kostenloser Key unter deepl.com — 500.000 Zeichen/Monat inklusive. Key endet auf :fx für Free-Tier.',
    'tr_saved_success'         => 'Sprachdateien gespeichert.',
    'tr_error_invalid_file'    => 'Ungültige Datei-Auswahl.',
    'tr_error_no_api_key'      => 'Kein DeepL API-Key konfiguriert. Bitte in den Einstellungen hinterlegen.',
    'tr_error_translate_failed'=> 'Auto-Übersetzung fehlgeschlagen. API-Key oder Netzwerk prüfen.',
    'tr_error_translate_failed_detail'=> 'Auto-Übersetzung fehlgeschlagen: {error}',
    'tr_nothing_to_translate'  => 'Nichts zu übersetzen — alle EN-Keys sind bereits vorhanden.',
    'tr_auto_translated'       => '{n} Keys automatisch übersetzt.',
    'tr_auto_translate_confirm' => 'Wirklich {n} fehlende Übersetzungen für {lang} automatisch via DeepL übersetzen?',
    'tr_config_saved'          => 'Konfiguration gespeichert.',
    'tr_en_will_be_created'    => 'EN-Datei wird beim Speichern neu erstellt.',
    'tr_filter'                => 'Filter…',
    'tr_show_missing_only'     => 'Nur fehlende anzeigen',
    'tr_keys_total'            => 'Keys gesamt',
    'tr_keys_missing'          => 'davon fehlend',
    'tr_no_file_selected'      => 'Bitte eine Sprachdatei aus der Liste oben wählen.',
    'tr_stats_label'           => 'Statistik',
    'tr_new_language'           => 'Neue Sprache',
    'tr_lang_will_be_created'   => 'Sprachdatei wird beim Speichern neu angelegt',
    'tr_status_missing'         => 'Ziel fehlt',
    'tr_status_missing_src'     => 'Quelle fehlt',
    'tr_error_invalid_lang_code'=> 'Ungültiger Sprach-Code. Nur 2-3 Buchstaben (a-z).',
    'tr_error_same_language'    => 'Quell- und Zielsprache dürfen nicht identisch sein.',
    'tr_error_data_loss_prevented' => 'Speichern abgebrochen: die neuen Werte enthalten deutlich weniger Übersetzungen als bisher vorhanden - das sieht nach einem Fehler aus. Nichts wurde geändert. Bitte die Seite neu laden und erneut versuchen.',
];

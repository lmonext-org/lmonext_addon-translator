<?php
/**
 * Project: LMOnext
 * Filename: addon/translator/lang/en.php
 * Fileversion: 1.0.0
 *
 * PHP version 8.2
 *
 * @copyright 2026 Torsten Hofmann
 * @license   GPL-3.0-only
 */

return [
    'nav_translator'           => 'Translations',
    'tr_title'                 => 'Translation Editor',
    'tr_select_file'           => 'Select language file',
    'tr_core_group'            => 'Core',
    'tr_addon_group'           => 'Addons',
    'tr_de_column'             => 'German (DE)',
    'tr_en_column'             => 'English (EN)',
    'tr_key_column'            => 'Key',
    'tr_status'                => 'Status',
    'tr_status_ok'             => 'OK',
    'tr_status_missing_en'     => 'EN missing',
    'tr_status_missing_de'     => 'DE missing',
    'tr_status_empty'          => 'Empty',
    'tr_btn_save'              => 'Save',
    'tr_btn_auto_translate'    => 'Auto-translate missing {lang}',
    'tr_btn_save_config'       => 'Save API key',
    'tr_deepl_key'             => 'DeepL API key',
    'tr_deepl_hint'            => 'Free key at deepl.com — 500,000 characters/month included. Key ending in :fx is free tier.',
    'tr_saved_success'         => 'Language files saved.',
    'tr_error_invalid_file'    => 'Invalid file selection.',
    'tr_error_no_api_key'      => 'No DeepL API key configured. Please set one in settings below.',
    'tr_error_translate_failed'=> 'Auto-translation failed. Check API key or network.',
    'tr_error_translate_failed_detail'=> 'Auto-translation failed: {error}',
    'tr_nothing_to_translate'  => 'Nothing to translate — all EN keys already present.',
    'tr_auto_translated'       => '{n} keys auto-translated.',
    'tr_auto_translate_confirm' => 'Really auto-translate {n} missing entries for {lang} via DeepL?',
    'tr_config_saved'          => 'Configuration saved.',
    'tr_en_will_be_created'    => 'EN file will be created on save.',
    'tr_filter'                => 'Filter…',
    'tr_show_missing_only'     => 'Show missing only',
    'tr_keys_total'            => 'Total keys',
    'tr_keys_missing'          => 'missing',
    'tr_no_file_selected'      => 'Please select a language file from the list above.',
    'tr_stats_label'           => 'Statistics',
    'tr_new_language'           => 'New language',
    'tr_lang_will_be_created'   => 'Language file will be created on save',
    'tr_status_missing'         => 'Target missing',
    'tr_status_missing_src'     => 'Source missing',
    'tr_error_invalid_lang_code'=> 'Invalid language code. Only 2-3 letters (a-z).',
    'tr_error_same_language'    => 'Source and target language must not be identical.',
    'tr_error_data_loss_prevented' => 'Save aborted: the new values contain significantly fewer translations than previously present - this looks like an error. Nothing was changed. Please reload the page and try again.',
];

<?php
/**
 * Project: LMOnext
 * Filename: addon/translator/view_translator.php
 * Fileversion: 1.1.4
 *
 * PHP version 8.2
 *
 * Split-View Editor für beliebige Sprachpaare (DE/EN/FR/ES/...).
 * Unterstütigt das Anlegen neuer Sprachdateien direkt aus dem Editor.
 *
 * @author    Torsten Hofmann <entwickler@bastel-code.de>
 * @copyright 2026 Torsten Hofmann
 * @license   GPL-3.0-only
 */

// Datei-Liste scannen
$trGroups = translatorScanLangFiles();

// Gewählte Datei-Gruppe aus GET
$trSelectedKey = $_GET['file'] ?? '';
if ($trSelectedKey === '' && !empty($trGroups)) {
    $trSelectedKey = array_key_first($trGroups);
}
$trSelected = $trGroups[$trSelectedKey] ?? null;

// Sprachpaar aus GET (Default: de → en)
// SICHERHEIT: ungültige/verdächtige Werte (z.B. Path-Traversal-Versuche wie
// "../../../etc/passwd") fallen auf die sicheren Standardwerte zurück,
// statt einen Fehler zu zeigen oder (schlimmer) unvalidiert an
// translatorLangFilePath() durchgereicht zu werden - siehe dortiger
// Docblock für den vollständigen Hintergrund. Besonders wichtig hier, da
// dies ein GET-Parameter ist (kein CSRF-Schutz möglich) und
// translatorLoadLangFile() die Datei per require lädt, nicht nur liest.
$trSrcLang = $_GET['src'] ?? 'de';
$trTgtLang = $_GET['tgt'] ?? 'en';
if (!translatorValidLangCode($trSrcLang)) {
    $trSrcLang = 'de';
}
if (!translatorValidLangCode($trTgtLang)) {
    $trTgtLang = 'en';
}

// Daten laden
$trSrcData = [];
$trTgtData = [];
$trTgtWillCreate = false;
if ($trSelected !== null) {
    $srcPath = translatorLangFilePath($trSelected['dir'], $trSrcLang);
    $tgtPath = translatorLangFilePath($trSelected['dir'], $trTgtLang);
    // Zusätzliche Absicherung (Verteidigung in der Tiefe): sollte
    // translatorLangFilePath() trotz der Vorprüfung oben null liefern
    // (z.B. weil $trSelected['dir'] selbst nicht existiert), wird die
    // Auswahl schlicht als "keine Datei ausgewählt" behandelt statt einen
    // Fehler zu werfen.
    if ($srcPath === null || $tgtPath === null) {
        $trSelected = null;
    } else {
        $trSrcData = translatorLoadLangFile($srcPath);
        $trTgtData = translatorLoadLangFile($tgtPath);
        $trTgtWillCreate = !is_file($tgtPath);
    }
}

// Alle Keys vereinigen (Source-Keys zuerst, dann zusätzliche Target-Keys)
$trAllKeys = array_unique(array_merge(array_keys($trSrcData), array_keys($trTgtData)));

// Statistik
$trTotal   = count($trAllKeys);
$trMissing = 0;
foreach ($trAllKeys as $k) {
    $srcEmpty = !isset($trSrcData[$k]) || $trSrcData[$k] === '';
    $tgtEmpty = !isset($trTgtData[$k]) || $trTgtData[$k] === '';
    if ($srcEmpty || $tgtEmpty) {
        $trMissing++;
    }
}

// DeepL Config
$trConfig   = translatorGetConfig();
$trDeeplKey = $trConfig['deepl_key'] ?? '';

// Sprach-Namen für Anzeige
$trLangNames = [
    'de' => 'Deutsch',  'en' => 'English', 'fr' => 'Français',
    'es' => 'Español',  'it' => 'Italiano', 'nl' => 'Nederlands',
    'pl' => 'Polski',   'pt' => 'Português', 'ru' => 'Русский',
    'tr' => 'Türkçe',   'ja' => '日本語',    'zh' => '中文',
    'cs' => 'Čeština',  'da' => 'Dansk',    'sv' => 'Svenska',
];

function trLangLabel(string $code): string
{
    global $trLangNames;
    return $trLangNames[$code] ?? strtoupper($code);
}
?>
<div class="translator-addon">
    <style>
        .tr-toolbar {
            display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .tr-toolbar select,
        .tr-toolbar input[type="text"] {
            padding: 6px 10px; border: 1px solid var(--border);
            border-radius: 4px; font-size: .85rem;
            background: var(--surface); color: var(--text);
        }
        .tr-lang-pair {
            display: flex; gap: 6px; align-items: center;
            padding: 6px 10px; border: 1px solid var(--border);
            border-radius: 6px; background: var(--surface);
        }
        .tr-lang-pair select {
            padding: 4px 8px; border: 1px solid var(--border);
            border-radius: 4px; font-size: .82rem;
            background: var(--bg); color: var(--text);
        }
        .tr-lang-pair .arrow { font-size: 1rem; color: var(--muted); }
        .tr-toolbar .tr-stats {
            margin-left: auto; font-size: .78rem; color: var(--muted);
        }
        .tr-toolbar .tr-stats .badge {
            display: inline-block; padding: 2px 8px; border-radius: 10px;
            background: var(--surface2, #232736); margin-left: 6px;
        }
        .tr-toolbar .tr-stats .badge-warn {
            background: rgba(245,158,11,.2); color: #f59e0b;
        }
        .tr-filter-bar {
            display: flex; gap: 10px; align-items: center; margin-bottom: 10px;
        }
        .tr-filter-bar input {
            flex: 1; max-width: 300px;
            padding: 5px 10px; border: 1px solid var(--border);
            border-radius: 4px; font-size: .82rem;
            background: var(--surface); color: var(--text);
        }
        .tr-table {
            width: 100%; border-collapse: collapse; font-size: .83rem;
        }
        .tr-table th {
            text-align: left; padding: 8px 10px; border-bottom: 2px solid var(--border);
            font-size: .78rem; color: var(--muted); text-transform: uppercase;
            letter-spacing: .03em; position: sticky; top: 0; background: var(--surface);
            z-index: 1;
        }
        .tr-table td {
            padding: 6px 10px; border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        .tr-table tr.missing-tgt { background: rgba(245,158,11,.08); }
        .tr-table tr.missing-src { background: rgba(239,68,68,.08); }
        .tr-table tr.hidden { display: none; }
        .tr-table .key-cell {
            font-family: monospace; font-size: .76rem; color: var(--muted);
            white-space: nowrap; max-width: 200px; overflow: hidden;
            text-overflow: ellipsis;
        }
        .tr-table textarea {
            width: 100%; min-height: 34px; max-height: 120px;
            padding: 5px 8px; border: 1px solid var(--border);
            border-radius: 4px; font-size: .82rem; resize: vertical;
            box-sizing: border-box; background: var(--bg); color: var(--text);
        }
        .tr-table textarea:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(59,130,246,.2);
        }
        .tr-status {
            font-size: .68rem; padding: 2px 6px; border-radius: 8px;
            white-space: nowrap;
        }
        .tr-status-ok      { background: rgba(34,197,94,.15); color: #22c55e; }
        .tr-status-missing { background: rgba(245,158,11,.15); color: #f59e0b; }
        .tr-status-empty   { background: rgba(239,68,68,.15); color: #ef4444; }
        .tr-config-panel {
            margin: 16px 0; padding: 14px 16px; border: 1px solid var(--border);
            border-radius: 8px; background: var(--surface);
        }
        .tr-config-panel h3 { margin: 0 0 10px 0; font-size: .88rem; font-weight: 600; }
        .tr-config-panel .hint {
            font-size: .76rem; color: var(--muted); margin-bottom: 8px;
        }
        .tr-config-panel .row {
            display: flex; gap: 8px; align-items: center;
        }
        .tr-config-panel input[type="password"] {
            flex: 1; max-width: 400px; padding: 6px 10px;
            border: 1px solid var(--border); border-radius: 4px;
            font-size: .82rem; background: var(--bg); color: var(--text);
        }
        .tr-info-banner {
            padding: 8px 12px; border-radius: 6px; margin-bottom: 12px;
            font-size: .8rem;
        }
        .tr-info-create {
            background: rgba(59,130,246,.1); color: #60a5fa; border: 1px solid rgba(59,130,246,.3);
        }
        .tr-new-lang-row {
            display: none; gap: 6px; align-items: center;
        }
        .tr-new-lang-row input {
            width: 60px; padding: 4px 8px; border: 1px solid var(--border);
            border-radius: 4px; font-size: .82rem;
            background: var(--bg); color: var(--text);
            text-transform: lowercase;
        }
        .tr-new-lang-row.active { display: flex; }
        @media (max-width: 700px) {
            .tr-table .key-cell { max-width: 120px; }
            .tr-toolbar { flex-direction: column; align-items: stretch; }
            .tr-toolbar .tr-stats { margin-left: 0; }
        }
    </style>

    <h1 style="margin: 8px 0 16px">🌐 <?= h(t('tr_title')) ?></h1>

    <!-- ── Toolbar: Datei-Auswahl + Sprachpaar + Statistik ────────────── -->
    <div class="tr-toolbar">
        <label for="tr-file-select" style="font-size:.83rem;font-weight:600">
            <?= h(t('tr_select_file')) ?>:
        </label>
        <select id="tr-file-select" onchange="trOnFileChange(this.value)">
            <?php foreach ($trGroups as $gKey => $gInfo): ?>
                <option value="<?= h($gKey) ?>" <?= $gKey === $trSelectedKey ? 'selected' : '' ?>>
                    <?= h($gInfo['label']) ?>
                    (<?= implode(', ', array_map('strtoupper', $gInfo['languages'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($trSelected !== null): ?>
        <div class="tr-lang-pair">
            <select id="tr-src-lang" onchange="trOnLangChange()">
                <?php foreach ($trSelected['languages'] as $lang): ?>
                    <option value="<?= h($lang) ?>" <?= $lang === $trSrcLang ? 'selected' : '' ?>>
                        <?= h(trLangLabel($lang)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="arrow">→</span>
            <select id="tr-tgt-lang" onchange="trOnLangChange()">
                <?php foreach ($trSelected['languages'] as $lang): ?>
                    <option value="<?= h($lang) ?>" <?= $lang === $trTgtLang ? 'selected' : '' ?>>
                        <?= h(trLangLabel($lang)) ?>
                    </option>
                <?php endforeach; ?>
                <?php if ($trTgtWillCreate): ?>
                    <!-- BUGFIX: die gerade erst angelegte, noch nicht auf der
                         Festplatte existierende Zielsprache braucht HIER eine
                         eigene <option>, sonst hat das <select> KEINE Option
                         mit diesem Wert - der Browser würde dann beim Rendern
                         automatisch auf die ERSTE verfügbare Option
                         zurückfallen (typischerweise die Quellsprache selbst!).
                         Der JS-Submit-Handler liest diesen Dropdown-Wert beim
                         Speichern ungeprüft aus - ohne diese Option würden
                         src_lang UND tgt_lang identisch gesendet, wodurch der
                         Server zweimal hintereinander in DIESELBE Datei
                         schreibt und der zweite (leere) Schreibvorgang die
                         Werte des ersten überschreibt. Das war die exakte
                         Ursache eines gemeldeten Datenverlusts (die deutsche
                         Sprachdatei wurde beim Anlegen einer neuen Sprache
                         mit Leerwerten überschrieben). -->
                    <option value="<?= h($trTgtLang) ?>" selected>
                        <?= h(trLangLabel($trTgtLang)) ?> (<?= h(t('tr_new_language')) ?>)
                    </option>
                <?php endif; ?>
                <option value="__new__">➕ <?= h(t('tr_new_language')) ?></option>
            </select>
            <div class="tr-new-lang-row" id="tr-new-lang-row">
                <input type="text" id="tr-new-lang-code" maxlength="3"
                       placeholder="fr" oninput="this.value=this.value.toLowerCase()">
                <button type="button" class="btn btn-sm btn-primary" onclick="trSubmitNewLang()">
                    OK
                </button>
            </div>
        </div>

        <div class="tr-stats">
            <?= h(t('tr_keys_total')) ?>: <span class="badge"><?= $trTotal ?></span>
            <?php if ($trMissing > 0): ?>
            <?= h(t('tr_keys_missing')) ?>: <span class="badge badge-warn"><?= $trMissing ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($trSelected === null): ?>
        <p style="color:var(--muted);padding:20px 0"><?= h(t('tr_no_file_selected')) ?></p>
    <?php else: ?>

    <?php if ($trTgtWillCreate): ?>
    <div class="tr-info-banner tr-info-create">
        ➕ <?= h(t('tr_lang_will_be_created')) ?>: <code><?= h($trTgtLang) ?>.php</code>
    </div>
    <?php endif; ?>

    <!-- ── Filter-Bar ──────────────────────────────────────────────────── -->
    <div class="tr-filter-bar">
        <input type="text" id="tr-filter" placeholder="<?= h(t('tr_filter')) ?>"
               oninput="trApplyFilter()">
        <label style="font-size:.78rem;display:flex;align-items:center;gap:4px">
            <input type="checkbox" id="tr-missing-only" onchange="trApplyFilter()">
            <?= h(t('tr_show_missing_only')) ?>
        </label>
    </div>

    <!-- ── Editor-Tabelle ──────────────────────────────────────────────── -->
    <form method="post" action="?action=translator" id="tr-form">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="translator_action" value="save">
        <input type="hidden" name="file_key" value="<?= h($trSelectedKey) ?>">
        <input type="hidden" name="src_lang" value="<?= h($trSrcLang) ?>" id="tr-form-src-lang">
        <input type="hidden" name="tgt_lang" value="<?= h($trTgtLang) ?>" id="tr-form-tgt-lang">
        <input type="hidden" name="new_lang_code" value="" id="tr-form-new-lang">

        <table class="tr-table" id="tr-table">
            <thead>
                <tr>
                    <th style="width:180px"><?= h(t('tr_key_column')) ?></th>
                    <th><?= h(trLangLabel($trSrcLang)) ?></th>
                    <th><?= h(trLangLabel($trTgtLang)) ?><?= $trTgtWillCreate ? ' <span style="color:var(--accent)">(neu)</span>' : '' ?></th>
                    <th style="width:80px"><?= h(t('tr_status')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($trAllKeys as $idx => $key):
                $srcVal  = $trSrcData[$key] ?? '';
                $tgtVal  = $trTgtData[$key] ?? '';
                $srcEmpty = $srcVal === '';
                $tgtEmpty = $tgtVal === '';
                $rowClass = '';
                if ($tgtEmpty && !$srcEmpty) { $rowClass = 'missing-tgt'; }
                elseif ($srcEmpty && !$tgtEmpty) { $rowClass = 'missing-src'; }

                $statusBadge = '<span class="tr-status tr-status-ok">' . h(t('tr_status_ok')) . '</span>';
                if ($srcEmpty && $tgtEmpty) { $statusBadge = '<span class="tr-status tr-status-empty">' . h(t('tr_status_empty')) . '</span>'; }
                elseif ($tgtEmpty) { $statusBadge = '<span class="tr-status tr-status-missing">' . h(t('tr_status_missing')) . '</span>'; }
                elseif ($srcEmpty) { $statusBadge = '<span class="tr-status tr-status-missing">' . h(t('tr_status_missing_src')) . '</span>'; }
            ?>
                <tr class="<?= $rowClass ?>" data-key="<?= h(strtolower($key)) ?>"
                    data-missing="<?= ($srcEmpty || $tgtEmpty) ? '1' : '0' ?>">
                    <td class="key-cell" title="<?= h($key) ?>"><?= h($key) ?></td>
                    <td>
                        <textarea name="src_values[]" data-idx="<?= $idx ?>"><?= h($srcVal) ?></textarea>
                        <input type="hidden" name="keys[]" value="<?= h($key) ?>">
                    </td>
                    <td>
                        <textarea name="tgt_values[]" data-idx="<?= $idx ?>"><?= h($tgtVal) ?></textarea>
                    </td>
                    <td><?= $statusBadge ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">
                💾 <?= h(t('tr_btn_save')) ?>
            </button>

            <?php if ($trMissing > 0 && $trDeeplKey !== ''): ?>
            <button type="submit" name="translator_action" value="auto_translate" class="btn btn-success"
                    formaction="?action=translator" onclick="return confirm('<?= h(t('tr_auto_translate_confirm', ['n' => $trMissing, 'lang' => trLangLabel($trTgtLang)])) ?>')">
                🤖 <?= h(t('tr_btn_auto_translate', ['lang' => strtoupper($trTgtLang)])) ?> (<?= $trMissing ?>)
            </button>
            <?php endif; ?>
        </div>
    </form>

    <!-- ── DeepL Konfiguration ─────────────────────────────────────────── -->
    <div class="tr-config-panel">
        <h3>🤖 DeepL Auto-Translate</h3>
        <p class="hint"><?= h(t('tr_deepl_hint')) ?></p>
        <form method="post" action="?action=translator">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="translator_action" value="save_config">
            <input type="hidden" name="file_key" value="<?= h($trSelectedKey) ?>">
            <input type="hidden" name="src_lang" value="<?= h($trSrcLang) ?>">
            <input type="hidden" name="tgt_lang" value="<?= h($trTgtLang) ?>">
            <div class="row">
                <input type="password" name="deepl_key" value="<?= h($trDeeplKey) ?>"
                       placeholder="<?= h(t('tr_deepl_key')) ?>" autocomplete="off">
                <button type="submit" class="btn btn-muted btn-sm">
                    <?= h(t('tr_btn_save_config')) ?>
                </button>
            </div>
        </form>
    </div>

    <?php endif; ?>
</div>

<script>
function trOnFileChange(fileKey) {
    window.location.href = '?action=translator&file=' + encodeURIComponent(fileKey);
}

function trOnLangChange() {
    var fileKey = document.getElementById('tr-file-select').value;
    var src = document.getElementById('tr-src-lang').value;
    var tgtSel = document.getElementById('tr-tgt-lang');
    var tgt = tgtSel.value;
    var newLangRow = document.getElementById('tr-new-lang-row');
    var newCodeInput = document.getElementById('tr-new-lang-code');

    if (tgt === '__new__') {
        newLangRow.classList.add('active');
        newCodeInput.focus();
        return;
    } else {
        newLangRow.classList.remove('active');
    }

    window.location.href = '?action=translator&file=' + encodeURIComponent(fileKey)
        + '&src=' + encodeURIComponent(src) + '&tgt=' + encodeURIComponent(tgt);
}

// Gemeinsame Funktion für "Neue Sprache anlegen" - wird sowohl vom OK-Button
// als auch vom Enter-Tastendruck im Eingabefeld genutzt (Bugfix: der
// OK-Button rief vorher fälschlich trOnLangChange() auf, das nur den
// Dropdown-Wert "__new__" prüft, nicht den tatsächlich eingegebenen Text im
// Eingabefeld - der Button hat dadurch nie wirklich navigiert, nur das
// bereits sichtbare Eingabefeld erneut "aktiviert").
function trSubmitNewLang() {
    var input = document.getElementById('tr-new-lang-code');
    var code = input.value.trim().toLowerCase();
    if (code.length < 2 || code.length > 3 || !/^[a-z]+$/.test(code)) {
        input.focus();
        input.select();
        return;
    }
    var fileKey = document.getElementById('tr-file-select').value;
    var src = document.getElementById('tr-src-lang').value;
    window.location.href = '?action=translator&file=' + encodeURIComponent(fileKey)
        + '&src=' + encodeURIComponent(src) + '&tgt=' + encodeURIComponent(code);
}

// Auto-submit wenn neue Sprache eingegeben wurde
document.getElementById('tr-new-lang-code')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        trSubmitNewLang();
    }
});

function trApplyFilter() {
    var query = document.getElementById('tr-filter').value.toLowerCase();
    var missingOnly = document.getElementById('tr-missing-only').checked;
    var rows = document.querySelectorAll('#tr-table tbody tr');

    rows.forEach(function(row) {
        var key      = row.getAttribute('data-key') || '';
        var isMissing = row.getAttribute('data-missing') === '1';
        var matchKey  = key.includes(query);
        var matchMiss = !missingOnly || isMissing;
        row.classList.toggle('hidden', !(matchKey && matchMiss));
    });
}

// Form hidden fields beim Submit aktualisieren
document.getElementById('tr-form')?.addEventListener('submit', function() {
    var tgtSel = document.getElementById('tr-tgt-lang');
    var tgt = tgtSel.value;
    if (tgt === '__new__') {
        var newCode = document.getElementById('tr-new-lang-code').value.trim().toLowerCase();
        document.getElementById('tr-form-tgt-lang').value = '__new__';
        document.getElementById('tr-form-new-lang').value = newCode;
    } else {
        document.getElementById('tr-form-tgt-lang').value = tgt;
    }
    document.getElementById('tr-form-src-lang').value = document.getElementById('tr-src-lang').value;
});
</script>

<?php
/**
 * lookup_helper.php
 * Metodi universali per nazionalità e lingue.
 * Tutte le funzioni usano la cache in $_GLOBALS per evitare query ripetute.
 */

declare(strict_types=1);

// ─── Nazionalità ────────────────────────────────────────────────────────────

/**
 * Restituisce tutte le nazionalità come array [id => nome].
 */
function getNazionalita(): array {
    global $_naz_cache;
    if (!isset($_naz_cache)) {
        $rows = getPDO()->query("SELECT id, nome FROM nazionalita ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $_naz_cache = array_column($rows, 'nome', 'id');
    }
    return $_naz_cache;
}

/**
 * Restituisce il nome della nazionalità dato il suo ID.
 * Restituisce '' se non trovata.
 */
function getNazionalitaNome(int $id): string {
    return getNazionalita()[$id] ?? '';
}

/**
 * Restituisce l'ID della nazionalità dato il nome (case-insensitive).
 * Restituisce null se non trovata.
 */
function getNazionalitaId(string $nome): ?int {
    $nome = mb_strtolower(trim($nome));
    foreach (getNazionalita() as $id => $n) {
        if (mb_strtolower($n) === $nome) return (int)$id;
    }
    return null;
}

// ─── Lingue ─────────────────────────────────────────────────────────────────

/**
 * Restituisce tutte le lingue come array [id => nome].
 */
function getLingue(): array {
    global $_ling_cache;
    if (!isset($_ling_cache)) {
        $rows = getPDO()->query("SELECT id, nome FROM lingue ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $_ling_cache = array_column($rows, 'nome', 'id');
    }
    return $_ling_cache;
}

/**
 * Restituisce il nome della lingua dato il suo ID.
 * Restituisce '' se non trovata.
 */
function getLinguaNome(int $id): string {
    return getLingue()[$id] ?? '';
}

/**
 * Restituisce l'ID della lingua dato il nome (case-insensitive).
 * Restituisce null se non trovata.
 */
function getLinguaId(string $nome): ?int {
    $nome = mb_strtolower(trim($nome));
    foreach (getLingue() as $id => $n) {
        if (mb_strtolower($n) === $nome) return (int)$id;
    }
    return null;
}

// ─── HTML helper: <select> ───────────────────────────────────────────────────

/**
 * Genera le <option> per un <select> di nazionalità.
 * $selected = ID correntemente selezionato (intero).
 */
function nazionalitaOptions(int $selected = 0): string {
    $html = '<option value="">— Seleziona nazionalità —</option>';
    foreach (getNazionalita() as $id => $nome) {
        $sel   = ($id === $selected) ? ' selected' : '';
        $label = ucfirst($nome);
        $html .= "<option value=\"$id\"$sel>$label</option>";
    }
    return $html;
}

/**
 * Genera le <option> per un <select> di lingue.
 * $selected = ID correntemente selezionato (intero).
 */
function lingueOptions(int $selected = 0): string {
    $html = '<option value="">— Seleziona lingua —</option>';
    foreach (getLingue() as $id => $nome) {
        $sel   = ($id === $selected) ? ' selected' : '';
        $label = ucfirst($nome);
        $html .= "<option value=\"$id\"$sel>$label</option>";
    }
    return $html;
}

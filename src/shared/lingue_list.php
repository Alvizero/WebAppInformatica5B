<?php
/**
 * Restituisce tutte le lingue disponibili dal database.
 * Richiede che $pdo (PDO) sia già disponibile nel contesto chiamante.
 *
 * @param PDO $pdo
 * @return array  [['id'=>1,'nome'=>'Italiano'], ...]
 */
function getAllLingue(PDO $pdo): array {
    static $cache = null;
    if ($cache === null) {
        $cache = $pdo->query('SELECT id, nome FROM lingue ORDER BY nome ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
    return $cache;
}

/**
 * Genera un elemento <select> HTML per la selezione della lingua.
 *
 * @param PDO    $pdo
 * @param string $name        Attributo name del select
 * @param int    $selected    ID lingua selezionata (0 = nessuna)
 * @param bool   $required
 * @param string $placeholder Testo dell'opzione vuota
 */
function lingueSelectHtml(PDO $pdo, string $name = 'lingua_id', int $selected = 0, bool $required = true, string $placeholder = 'Seleziona lingua…'): string {
    $lingue = getAllLingue($pdo);
    $req    = $required ? ' required' : '';
    $html   = "<select name=\"{$name}\" id=\"{$name}\"{$req} class=\"form-select\">\n";
    $html  .= "  <option value=\"\">{$placeholder}</option>\n";
    foreach ($lingue as $l) {
        $sel   = ((int)$l['id'] === $selected) ? ' selected' : '';
        $html .= "  <option value=\"{$l['id']}\"{$sel}>" . htmlspecialchars($l['nome']) . "</option>\n";
    }
    $html .= "</select>";
    return $html;
}

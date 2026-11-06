<?php
// ============================================================
// Funções utilitárias compartilhadas
// ============================================================

/** Escapa para HTML. Decodifica antes para não duplicar dados antigos já escapados. */
function e($s): string {
    $s = html_entity_decode((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Limpa texto vindo de formulário: sem tags, sem caracteres de controle, tamanho máximo. */
function clean_text($s, int $max = 255, bool $multiline = false): string {
    $s = is_string($s) ? $s : '';
    $s = strip_tags($s);
    $s = $multiline
        ? preg_replace('/[^\P{C}\n\t]/u', '', str_replace("\r\n", "\n", $s))
        : preg_replace('/\p{C}/u', '', $s);
    $s = trim((string)$s);
    return mb_substr($s, 0, $max);
}

function clean_phone($s): string {
    $s = clean_text($s, 30);
    return preg_match('/^[\d\s()+\-.]{8,30}$/', $s) ? $s : '';
}

function clean_email($s): string {
    $s = clean_text($s, 160);
    return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : '';
}

/** Valida data Y-m-d entre hoje e +1 ano. */
function clean_future_date($s): string {
    $s = is_string($s) ? trim($s) : '';
    $d = DateTime::createFromFormat('!Y-m-d', $s);
    if (!$d || $d->format('Y-m-d') !== $s) return '';
    $today = new DateTime('today');
    if ($d < $today || $d > (clone $today)->modify('+1 year')) return '';
    return $s;
}

/** Aceita somente valores de uma lista (para <select>). */
function pick($value, array $allowed, string $default = ''): string {
    $value = is_string($value) ? trim($value) : '';
    return in_array($value, $allowed, true) ? $value : $default;
}

function old(string $key): string {
    return e($_POST[$key] ?? '');
}

function selected(string $key, string $value): string {
    return (($_POST[$key] ?? '') === $value) ? 'selected' : '';
}

// ── CSRF ────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}
function csrf_valid(string $token): bool {
    return !empty($_SESSION['_csrf']) && $token !== '' && hash_equals($_SESSION['_csrf'], $token);
}

// ── Anti-spam ───────────────────────────────────────────────
function client_ip(): string {
    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff !== '') {
        $parts = array_map('trim', explode(',', $xff));
        $ip = end($parts);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Limita tentativas por IP (arquivo em /tmp). Retorna true se ainda pode.
 * $hit=false apenas consulta sem contar.
 */
function rate_limit(string $bucket, int $max, int $windowSec, bool $hit = true): bool {
    $dir = sys_get_temp_dir() . '/sitepet_rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . sha1($bucket . '|' . client_ip()) . '.json';
    $now  = time();

    $fp = @fopen($file, 'c+');
    if (!$fp) return true;                       // falha aberta: não derruba o site
    flock($fp, LOCK_EX);
    $hits = json_decode((string)stream_get_contents($fp), true) ?: [];
    $hits = array_values(array_filter($hits, fn($t) => $t > $now - $windowSec));
    $allowed = count($hits) < $max;
    if ($hit && $allowed) $hits[] = $now;
    ftruncate($fp, 0); rewind($fp);
    fwrite($fp, json_encode($hits));
    flock($fp, LOCK_UN); fclose($fp);
    return $allowed;
}

function rate_limit_reset(string $bucket): void {
    @unlink(sys_get_temp_dir() . '/sitepet_rl/' . sha1($bucket . '|' . client_ip()) . '.json');
}

/** Campo "isca" invisível: robôs preenchem, humanos não. */
function honeypot_field(): string {
    return '<div class="hp-field" aria-hidden="true"><label>Não preencha<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
}
function honeypot_triggered(): bool {
    return !empty($_POST['website']);
}

// ── Arquivos JSON em data/ ──────────────────────────────────
function data_path(string $name): string {
    return __DIR__ . '/../data/' . basename($name) . '.json';
}
function load_json(string $name, array $default = []): array {
    $f = data_path($name);
    if (!is_file($f)) return $default;
    $d = json_decode((string)@file_get_contents($f), true);
    return is_array($d) ? $d : $default;
}
function save_json(string $name, array $data): bool {
    return @file_put_contents(
        data_path($name),
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

function horarios(): array {
    return load_json('horarios', ['consultas' => [], 'tosagem' => [], 'urgencia' => []]);
}

/** Retorna seções de preço na ordem, cada uma com 'itens'. */
function precos_secoes(): array {
    $p = load_json('precos');
    $secs = $p['_sections'] ?? [
        ['tipo'=>'consultas','titulo'=>'Consultas & Vacinas',   'icon'=>'stethoscope','color'=>'#2e9e6b'],
        ['tipo'=>'tosagem',  'titulo'=>'Banho & Tosa',          'icon'=>'scissors',   'color'=>'#8e44ad'],
        ['tipo'=>'exames',   'titulo'=>'Exames & Procedimentos','icon'=>'flask',      'color'=>'#2980b9'],
    ];
    foreach ($secs as &$s) $s['itens'] = $p[$s['tipo']] ?? [];
    return $secs;
}

function brl($v): string {
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function whats_link(string $text = ''): string {
    return 'https://wa.me/' . CLINIC_WHATS . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function safe_url(string $url, string $fallback = '#'): string {
    return preg_match('#^https://#i', $url) ? $url : $fallback;
}

const DIAS_PT = ['Mon'=>'Seg','Tue'=>'Ter','Wed'=>'Qua','Thu'=>'Qui','Fri'=>'Sex','Sat'=>'Sáb','Sun'=>'Dom'];

/** Resumo "Seg – Sex / Sábado / Domingo" de um tipo de horário. */
function horario_resumo(string $tipo = 'consultas'): array {
    $rows = horarios()[$tipo] ?? [];
    $fmt  = function (array $r): string {
        if (($r['status'] ?? '') === 'fechado' || empty($r['abertura'])) return 'Fechado';
        return $r['abertura'] . ' – ' . $r['fechamento'];
    };
    $util = array_slice($rows, 0, 5);
    $out  = [];
    if ($util) {
        $abre  = min(array_map(fn($r) => $r['abertura'] ?: '99:99', $util));
        $fecha = max(array_map(fn($r) => $r['fechamento'] ?: '00:00', $util));
        $out['Seg – Sex'] = $abre === '99:99' ? 'Fechado' : "$abre – $fecha";
    }
    if (isset($rows[5])) $out['Sábado']  = $fmt($rows[5]);
    if (isset($rows[6])) $out['Domingo'] = $fmt($rows[6]);
    return $out;
}

/** Status atual da clínica a partir de horarios.json (para o selo "Aberto agora"). */
function clinica_aberta_agora(): bool {
    $map  = [1=>'Segunda-feira',2=>'Terça-feira',3=>'Quarta-feira',4=>'Quinta-feira',5=>'Sexta-feira',6=>'Sábado',7=>'Domingo'];
    $dia  = $map[(int)date('N')];
    $hora = date('H:i');
    foreach (horarios()['consultas'] ?? [] as $r) {
        if (($r['dia'] ?? '') !== $dia || ($r['status'] ?? '') === 'fechado') continue;
        return $hora >= ($r['abertura'] ?? '99') && $hora < ($r['fechamento'] ?? '00');
    }
    return false;
}

/** Visual (ícone/cores) de um produto por palavra-chave ou categoria. */
function product_visual(string $name, string $cat): array {
    $n = mb_strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name);
    $s = fn($icon, $a, $b, $bg) => ['icon'=>$icon, 'grad'=>"135deg,$a,$b", 'accent'=>$a, 'bg'=>$bg];
    $byWord = [
        'racao'   => $s('fa-bag-shopping',      '#f39c12','#e67e22','#fef5e7'),
        'petisco' => $s('fa-bone',              '#a0522d','#c0392b','#fdf0e8'),
        'escova'  => $s('fa-soap',              '#8e44ad','#6c3483','#f5eef8'),
        'shampoo' => $s('fa-pump-soap',         '#2980b9','#1a5276','#eaf4fb'),
        'vermif'  => $s('fa-pills',             '#27ae60','#1e8449','#e9f7ef'),
        'bola'    => $s('fa-baseball',          '#e74c3c','#c0392b','#fdedec'),
        'caminha' => $s('fa-bed',               '#16a085','#0e6655','#e8f8f5'),
        'colonia' => $s('fa-spray-can-sparkles','#9b59b6','#7d3c98','#f5eef8'),
        'pulga'   => $s('fa-shield-dog',        '#2ecc71','#27ae60','#e9f7ef'),
        'pipeta'  => $s('fa-shield-dog',        '#2ecc71','#27ae60','#e9f7ef'),
        'lacinho' => $s('fa-ribbon',            '#e91e8c','#c2185b','#fce4ec'),
        'gravata' => $s('fa-ribbon',            '#e91e8c','#c2185b','#fce4ec'),
    ];
    foreach ($byWord as $kw => $v) if (str_contains($n, $kw)) return $v;
    $byCat = [
        'racao'     => $byWord['racao'],
        'petisco'   => $byWord['petisco'],
        'higiene'   => $byWord['shampoo'],
        'saude'     => $byWord['vermif'],
        'acessorio' => $s('fa-paw',  '#8e44ad','#6c3483','#f5eef8'),
        'brinquedo' => $s('fa-star', '#f1c40f','#d4ac0d','#fefde7'),
    ];
    return $byCat[$cat] ?? $s('fa-paw', '#2e9e6b', '#1e7a50', '#e8f8f0');
}

const CATEGORIAS = [
    'racao'=>'Ração','petisco'=>'Petiscos','higiene'=>'Higiene',
    'saude'=>'Saúde','acessorio'=>'Acessórios','brinquedo'=>'Brinquedos',
];

/** Produtos ativos do Nhost com fallback estático. */
function produtos_ativos(?int $limit = null): array {
    $fields = '{ id nome descricao preco categoria emoji estoque }';
    $q = $limit
        ? gql('query($l:Int!){ produtos(where:{ativo:{_eq:true}}, order_by:{id:asc}, limit:$l) ' . $fields . ' }', ['l' => $limit])
        : gql('query { produtos(where:{ativo:{_eq:true}}, order_by:{categoria:asc,nome:asc}) ' . $fields . ' }');
    if ($q['ok'] && !empty($q['data']['produtos'])) return $q['data']['produtos'];

    $fallback = [
        ['emoji'=>'🥩','nome'=>'Ração Premium Cão Adulto 15kg','descricao'=>'Alta digestibilidade, fórmula completa e balanceada.','preco'=>189.90,'categoria'=>'racao'],
        ['emoji'=>'🐟','nome'=>'Ração Premium Gato 3kg','descricao'=>'Rico em ômega-3, pelo brilhante e digestão saudável.','preco'=>79.90,'categoria'=>'racao'],
        ['emoji'=>'🦴','nome'=>'Petisco Natural Cão 200g','descricao'=>'Snack natural sem aditivos, excelente para treinamento.','preco'=>24.90,'categoria'=>'petisco'],
        ['emoji'=>'🐠','nome'=>'Petisco Gato Atum 60g','descricao'=>'Lanche delicioso e nutritivo para felinos exigentes.','preco'=>12.90,'categoria'=>'petisco'],
        ['emoji'=>'🪮','nome'=>'Escova de Remoção de Pelos','descricao'=>'Reduz a queda de pelos em até 90%. Ergonômica.','preco'=>54.90,'categoria'=>'acessorio'],
        ['emoji'=>'🎾','nome'=>'Bola Interativa com Apito','descricao'=>'Brinquedo resistente para cães de todos os portes.','preco'=>34.90,'categoria'=>'brinquedo'],
        ['emoji'=>'🛁','nome'=>'Shampoo Neutro Pet 500ml','descricao'=>'Fórmula suave para banhos frequentes sem ressecar.','preco'=>29.90,'categoria'=>'higiene'],
        ['emoji'=>'💊','nome'=>'Vermífugo Comprimido (4cp)','descricao'=>'Eficaz contra lombrigas, ancilóstomos e outros.','preco'=>38.90,'categoria'=>'saude'],
        ['emoji'=>'🏠','nome'=>'Caminha Pet Tamanho M','descricao'=>'Confortável e lavável, perfeita para cães e gatos.','preco'=>119.90,'categoria'=>'acessorio'],
        ['emoji'=>'🧴','nome'=>'Colônia Pet Floral 100ml','descricao'=>'Fragrância suave que dura até 48h.','preco'=>22.90,'categoria'=>'higiene'],
        ['emoji'=>'🎀','nome'=>'Kit Lacinhos e Gravatas','descricao'=>'Acessórios fashion para deixar seu pet ainda mais fofo.','preco'=>19.90,'categoria'=>'acessorio'],
    ];
    return $limit ? array_slice($fallback, 0, $limit) : $fallback;
}

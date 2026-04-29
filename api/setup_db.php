<?php
// Cria todas as tabelas no Nhost/Hasura e as rastreia automaticamente.
// Pode ser rodado via CLI (php api/setup_db.php) ou via web (GET /api/setup_db.php).
require_once __DIR__ . '/../includes/config.php';

$isCli = php_sapi_name() === 'cli';
$log   = [];

function log_step(string $msg, bool $ok = true): void {
    global $log, $isCli;
    $log[] = ['ok' => $ok, 'msg' => $msg];
    if ($isCli) {
        echo ($ok ? '[OK]  ' : '[ERR] ') . $msg . PHP_EOL;
    }
}

function hasura_sql(string $sql): array {
    $url = 'https://' . NHOST_SUBDOMAIN . '.hasura.' . NHOST_REGION . '.nhost.run/v2/query';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-hasura-admin-secret: ' . NHOST_ADMIN_SECRET,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'type' => 'run_sql',
            'args' => ['sql' => $sql, 'cascade' => false, 'read_only' => false],
        ]),
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($resp, true) ?? [];
    return ['ok' => $code === 200 && empty($data['error']), 'code' => $code, 'data' => $data];
}

function hasura_track(string $table): array {
    $url = 'https://' . NHOST_SUBDOMAIN . '.hasura.' . NHOST_REGION . '.nhost.run/v1/metadata';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-hasura-admin-secret: ' . NHOST_ADMIN_SECRET,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'type' => 'pg_track_table',
            'args' => ['source' => 'default', 'schema' => 'public', 'name' => $table],
        ]),
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($resp, true) ?? [];
    // 200 = sucesso | 400 com "already-tracked" = tabela já rastreada (ok)
    $alreadyTracked = isset($data['error']) && (
        str_contains($data['error'], 'already') ||
        str_contains(json_encode($data), 'already-tracked')
    );
    return ['ok' => $code === 200 || $alreadyTracked, 'code' => $code, 'data' => $data];
}

// ── 1. Criar tabelas ──────────────────────────────────────────────────────────
$sql = "
CREATE TABLE IF NOT EXISTS public.agendamentos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    telefone    TEXT        NOT NULL,
    email       TEXT,
    pet_nome    TEXT        NOT NULL,
    pet_especie TEXT,
    pet_raca    TEXT,
    servico     TEXT,
    data        DATE        NOT NULL,
    horario     TEXT,
    obs         TEXT,
    status      TEXT        NOT NULL DEFAULT 'pendente',
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.tosagem_agendamentos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    telefone    TEXT        NOT NULL,
    pet_nome    TEXT        NOT NULL,
    pet_raca    TEXT,
    pet_porte   TEXT,
    servico     TEXT,
    data        DATE        NOT NULL,
    horario     TEXT,
    status      TEXT        NOT NULL DEFAULT 'pendente',
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.contatos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT        NOT NULL,
    email       TEXT        NOT NULL,
    assunto     TEXT,
    mensagem    TEXT        NOT NULL,
    lido        BOOLEAN     NOT NULL DEFAULT FALSE,
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.produtos (
    id          BIGSERIAL PRIMARY KEY,
    nome        TEXT           NOT NULL,
    descricao   TEXT,
    preco       NUMERIC(10,2)  NOT NULL,
    categoria   TEXT,
    emoji       TEXT,
    estoque     INT            NOT NULL DEFAULT 0,
    ativo       BOOLEAN        NOT NULL DEFAULT TRUE,
    criado_em   TIMESTAMPTZ    NOT NULL DEFAULT NOW()
);
";

$r = hasura_sql($sql);
log_step('Criar tabelas', $r['ok']);
if (!$r['ok']) {
    log_step('Detalhe: ' . json_encode($r['data']), false);
}

// ── 2. Rastrear tabelas no Hasura ─────────────────────────────────────────────
foreach (['agendamentos', 'tosagem_agendamentos', 'contatos', 'produtos'] as $t) {
    $r = hasura_track($t);
    log_step("Rastrear tabela: $t", $r['ok']);
}

// ── 3. Inserir produtos iniciais (ignora duplicatas) ─────────────────────────
$seedSql = "
INSERT INTO public.produtos (nome, descricao, preco, categoria, emoji, estoque) VALUES
('Racao Premium Cao Adulto 15kg', 'Alta digestibilidade, formula completa e balanceada.',    189.90, 'racao',    '🥩', 50),
('Racao Premium Gato 3kg',        'Rico em omega-3, pelo brilhante e digestao saudavel.',    79.90,  'racao',    '🐟', 30),
('Petisco Natural Cao 200g',      'Snack natural sem aditivos, ideal para treinos.',          24.90,  'petisco',  '🦴', 100),
('Petisco Gato Atum 60g',         'Lanche nutritivo para felinos exigentes.',                12.90,  'petisco',  '🐠', 80),
('Escova de Remocao de Pelos',    'Reduz a queda de pelos em ate 90%. Ergonomica.',          54.90,  'acessorio','🪮', 25),
('Bola Interativa com Apito',     'Brinquedo resistente para caes de todos os portes.',      34.90,  'brinquedo','🎾', 40),
('Shampoo Neutro Pet 500ml',      'Formula suave para banhos frequentes.',                   29.90,  'higiene',  '🛁', 60),
('Vermifugo Comprimido (4cp)',    'Eficaz contra lombrigas e outros parasitas internos.',    38.90,  'saude',    '💊', 45),
('Caminha Pet Tamanho M',         'Confortavel e lavavel, perfeita para caes e gatos.',     119.90, 'acessorio','🏠', 20),
('Colonia Pet Floral 100ml',      'Fragancia suave que dura ate 48h.',                       22.90,  'higiene',  '🧴', 35),
('Anti-pulgas Pipeta Mensal',     'Protecao de 30 dias contra pulgas e carrapatos.',         45.90,  'saude',    '🐾', 55),
('Kit Lacinhos e Gravatas',       'Acessorios fashion para deixar o pet ainda mais fofo.',  19.90,  'acessorio','🎀', 70)
ON CONFLICT DO NOTHING;
";

$r = hasura_sql($seedSql);
log_step('Inserir produtos iniciais', $r['ok']);

// ── Saída ────────────────────────────────────────────────────────────────────
if (!$isCli) {
    header('Content-Type: application/json');
    echo json_encode(['done' => true, 'log' => $log], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

<?php
// Registra visitas únicas por sessão em data/visitas.json.
// Uma mesma pessoa navegando entre páginas conta como 1 visita no dia.
function track_visit(string $page): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $isBot = preg_match('/bot|crawl|spider|slurp|curl|python|java|wget/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($isBot) return;

    $today     = date('Y-m-d');
    $month     = date('Y-m');
    $sessKey   = 'visited_' . $today;                    // chave muda a cada dia
    $visited   = $_SESSION[$sessKey] ?? [];              // páginas já vistas hoje nesta sessão

    if (in_array($page, $visited)) return;               // esta página já foi contada na sessão

    $_SESSION[$sessKey] = array_merge($visited, [$page]);

    $file = __DIR__ . '/../data/visitas.json';
    $data = file_exists($file) ? (json_decode(@file_get_contents($file), true) ?? []) : [];

    // Contadores globais (total, dia, mês) só na PRIMEIRA página da sessão hoje
    if (empty($visited)) {
        $data['total']          = ($data['total'] ?? 0) + 1;
        $data['dias'][$today]   = ($data['dias'][$today] ?? 0) + 1;
        $data['meses'][$month]  = ($data['meses'][$month] ?? 0) + 1;
    }

    // Contador por página (uma vez por sessão por página)
    $data['paginas'][$page] = ($data['paginas'][$page] ?? 0) + 1;

    // Manter só os últimos 60 dias
    if (!empty($data['dias']) && count($data['dias']) > 60) {
        ksort($data['dias']);
        $data['dias'] = array_slice($data['dias'], -60, 60, true);
    }

    @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

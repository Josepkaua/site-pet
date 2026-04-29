<?php
// Registra visitas por página em data/visitas.json
function track_visit(string $page): void {
    $file = __DIR__ . '/../data/visitas.json';
    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?? []) : [];

    $today  = date('Y-m-d');
    $month  = date('Y-m');
    $isBot  = preg_match('/bot|crawl|spider|slurp|curl/i', $_SERVER['HTTP_USER_AGENT'] ?? '');

    if ($isBot) return;

    // Total geral
    $data['total'] = ($data['total'] ?? 0) + 1;

    // Por dia
    $data['dias'][$today] = ($data['dias'][$today] ?? 0) + 1;

    // Por mês
    $data['meses'][$month] = ($data['meses'][$month] ?? 0) + 1;

    // Por página
    $data['paginas'][$page] = ($data['paginas'][$page] ?? 0) + 1;

    // Manter só últimos 60 dias
    if (isset($data['dias']) && count($data['dias']) > 60) {
        ksort($data['dias']);
        $data['dias'] = array_slice($data['dias'], -60, 60, true);
    }

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth.php';

// Registra visitas únicas por sessão em data/visitas.json.
// Uma mesma pessoa navegando entre páginas conta como 1 visita no dia.
function track_visit(string $page): void {
    if (PHP_SAPI === 'cli') return;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|curl|python|java|wget|headless|preview|monitor/i', $ua)) return;
    if (is_admin()) return;                                // não conta a própria clínica

    $today   = date('Y-m-d');
    $month   = date('Y-m');
    $sessKey = 'visited_' . $today;
    $visited = $_SESSION[$sessKey] ?? [];

    if (in_array($page, $visited, true)) return;
    $_SESSION[$sessKey] = array_merge($visited, [$page]);

    $file = data_path('visitas');
    $fp   = @fopen($file, 'c+');
    if (!$fp) return;
    flock($fp, LOCK_EX);                                   // evita perder contagens simultâneas
    $data = json_decode((string)stream_get_contents($fp), true) ?: [];

    if (empty($visited)) {
        $data['total']         = ($data['total'] ?? 0) + 1;
        $data['dias'][$today]  = ($data['dias'][$today] ?? 0) + 1;
        $data['meses'][$month] = ($data['meses'][$month] ?? 0) + 1;
    }
    $data['paginas'][$page] = ($data['paginas'][$page] ?? 0) + 1;

    if (!empty($data['dias']) && count($data['dias']) > 60) {
        ksort($data['dias']);
        $data['dias'] = array_slice($data['dias'], -60, 60, true);
    }

    ftruncate($fp, 0); rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN); fclose($fp);
}

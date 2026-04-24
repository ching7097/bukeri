<?php
declare(strict_types=1);

/**
 * Board list alias: rendering and AdSense live in index.php → pages/home.php.
 * Use list.php?b=slug (&q=) for stable URLs; always forwards to index.php.
 */
$b = isset($_GET['b']) ? (string) $_GET['b'] : 'house';
$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$params = ['b' => $b !== '' ? $b : 'house'];
if ($q !== '') {
    $params['q'] = $q;
}
header('Location: index.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986), true, 302);
exit;

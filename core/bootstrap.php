<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Database: only core/db.php — single PDO via db(). No other DB bootstrap.
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/nickname.php';
require_once __DIR__ . '/google_oauth.php';
require_once __DIR__ . '/auth_google.php';
require_once __DIR__ . '/reports.php';
require_once __DIR__ . '/post_images.php';
require_once __DIR__ . '/posts.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/../logic/risk.php';
require_once __DIR__ . '/../logic/recommend.php';

init_db();

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../components/adsense.php';

function voter_key(): string
{
    if (empty($_SESSION['vk'])) {
        $_SESSION['vk'] = bin2hex(random_bytes(16));
    }
    return (string) $_SESSION['vk'];
}

function remember_viewed_post(int $postId): void
{
    $ids = $_SESSION['viewed_post_ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [];
    }
    array_unshift($ids, $postId);
    $out = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        if ($id > 0 && !in_array($id, $out, true)) {
            $out[] = $id;
        }
        if (count($out) >= 40) {
            break;
        }
    }
    $_SESSION['viewed_post_ids'] = $out;
}

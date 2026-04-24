<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

/** @var array<string, string> $boards */
$boards = require __DIR__ . '/config/boards.php';

$bRaw = (string) ($_GET['b'] ?? '');
if ($bRaw === '' || $bRaw === 'home') {
    $pageTitle = '부커리 | 커뮤니티 홈';
    $hideShellHeader = true;
    $boardSlug = 'home';
    ob_start();
    require __DIR__ . '/pages/site_home.php';
    $content = ob_get_clean();
    require __DIR__ . '/templates/shell.php';
    exit;
}

$boardSlug = board_normalize($bRaw, $boards);
$boardTitle = $boards[$boardSlug];

$qSearch = trim((string) ($_GET['q'] ?? ''));

$pinned = require __DIR__ . '/config/pinned.php';
$trending = posts_trending_in_board($boardSlug, 10);
$popularByViews = posts_hot_by_views_in_board($boardSlug, 10);
$mostCommented = posts_most_commented_in_board($boardSlug, 10);
$posts = $qSearch !== ''
    ? posts_search_in_board($boardSlug, $qSearch, 50)
    : posts_latest_in_board($boardSlug, 50);

$pageTitle = '부커리 | ' . $boardTitle;
$hideShellHeader = true;

ob_start();
require __DIR__ . '/pages/home.php';
$content = ob_get_clean();

require __DIR__ . '/templates/shell.php';

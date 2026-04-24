<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $rid = (int) ($_POST['post_id'] ?? 0);
    $sortRaw = (string) ($_POST['sort'] ?? 'latest');
    $sortQ = $sortRaw === 'liked' ? 'liked' : 'latest';
    $loc = 'view.php?id=' . $rid . '&sort=' . urlencode($sortQ);

    if ($action === 'post_agree' && $rid > 0) {
        post_try_agree($rid, voter_key());
        header('Location: ' . $loc);
        exit;
    }
    if ($action === 'comment_agree' && $rid > 0) {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) {
            comment_try_agree($cid, voter_key());
        }
        header('Location: ' . $loc);
        exit;
    }
    if ($action === 'report') {
        $rid = (int) ($_POST['post_id'] ?? $rid);
        $sortRaw = (string) ($_POST['sort'] ?? 'latest');
        $sortQ = $sortRaw === 'liked' ? 'liked' : 'latest';
        $loc = 'view.php?id=' . $rid . '&sort=' . urlencode($sortQ);
        $tt = (string) ($_POST['target_type'] ?? '');
        $tid = (int) ($_POST['target_id'] ?? 0);
        if ($rid > 0 && $tid > 0 && ($tt === 'post' || $tt === 'comment')) {
            $reason = trim((string) ($_POST['reason'] ?? ''));
            report_add($tt, $tid, voter_key(), $reason);
            $_SESSION['flash_ok'] = '신고가 접수되었습니다.';
        }
        header('Location: ' . $loc);
        exit;
    }
    if ($action === 'comment' && $rid > 0) {
        $res = auth_or_nickname_for_write(trim((string) ($_POST['author'] ?? '')));
        if (!$res['ok']) {
            $_SESSION['flash_err'] = $res['error'];
            header('Location: ' . $loc);
            exit;
        }
        $text = trim((string) ($_POST['content'] ?? $_POST['body'] ?? ''));
        if ($text !== '') {
            comment_add($rid, null, $res['nickname'], $text);
        }
        header('Location: ' . $loc);
        exit;
    }
    if ($action === 'reply' && $rid > 0) {
        $res = auth_or_nickname_for_write(trim((string) ($_POST['author'] ?? '')));
        if (!$res['ok']) {
            $_SESSION['flash_err'] = $res['error'];
            header('Location: ' . $loc);
            exit;
        }
        $parent = (int) ($_POST['parent_id'] ?? 0);
        $text = trim((string) ($_POST['content'] ?? $_POST['body'] ?? ''));
        if ($text !== '' && $parent > 0) {
            comment_add($rid, $parent, $res['nickname'], $text);
        }
        header('Location: ' . $loc);
        exit;
    }
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo '잘못된 요청';
    exit;
}

$post = post_find($id);
if (!$post) {
    http_response_code(404);
    echo '없는 글';
    exit;
}

post_increment_views($id);
remember_viewed_post($id);
$post = post_find($id);
if (!$post) {
    http_response_code(404);
    echo '없는 글';
    exit;
}

$sort = ($_GET['sort'] ?? 'latest') === 'liked' ? 'liked' : 'latest';
$risk = risk_evaluate_post($post);
$comments = comments_for_post($id, $sort, voter_key());
$related = recommend_related($post, 5);
$also = recommend_also_viewed($post, $_SESSION['viewed_post_ids'] ?? [], 5);
$agreed = post_user_has_agreed($id, voter_key());
$boards = require __DIR__ . '/config/boards.php';
$boardSlug = board_normalize((string) ($post['gallery'] ?? 'house'), $boards);
$pageTitle = (string) $post['title'] . ' | 부커리';
$postImages = post_images_for_post($id);
$flashErr = (string) ($_SESSION['flash_err'] ?? '');
$flashOk = (string) ($_SESSION['flash_ok'] ?? '');
unset($_SESSION['flash_err'], $_SESSION['flash_ok']);
$sessNickDisplay = nickname_session_display();
$googleLogged = auth_google_logged_in();
$brandLogoUrl = 'assets/images/bookeri_logo_final.png';

ob_start();
require __DIR__ . '/pages/view.php';
$content = ob_get_clean();

require __DIR__ . '/templates/shell.php';

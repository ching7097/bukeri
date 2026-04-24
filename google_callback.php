<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

if (isset($_GET['error'])) {
    $_SESSION['toast'] = 'Google 로그인이 완료되지 않았습니다.';
    header('Location: index.php');
    exit;
}

if (!google_oauth_is_configured()) {
    http_response_code(503);
    echo 'Google OAuth 미설정';
    exit;
}

$state = (string) ($_GET['state'] ?? '');
$sessState = (string) ($_SESSION['oauth_state'] ?? '');
if ($state === '' || $sessState === '' || !hash_equals($sessState, $state)) {
    http_response_code(400);
    echo '잘못된 요청(state).';
    exit;
}
unset($_SESSION['oauth_state']);

$code = (string) ($_GET['code'] ?? '');
if ($code === '') {
    header('Location: index.php');
    exit;
}

$tok = google_oauth_exchange_code($code);
if ($tok === null) {
    $_SESSION['toast'] = 'Google 토큰 교환에 실패했습니다. 다시 시도해 주세요.';
    header('Location: index.php');
    exit;
}

$access = (string) ($tok['access_token'] ?? '');
if ($access === '') {
    $_SESSION['toast'] = 'Google 로그인 응답이 올바르지 않습니다.';
    header('Location: index.php');
    exit;
}

$info = google_oauth_userinfo($access);
if ($info === null || empty($info['sub'])) {
    $_SESSION['toast'] = 'Google 사용자 정보를 가져오지 못했습니다.';
    header('Location: index.php');
    exit;
}

$sub = (string) $info['sub'];
$existing = user_find_by_google_id($sub);
if ($existing !== null) {
    $_SESSION['user_id'] = (int) $existing['id'];
    unset($_SESSION['google_oauth_pending_sub']);
    $_SESSION['toast'] = '로그인되었습니다.';
    header('Location: index.php');
    exit;
}

$_SESSION['google_oauth_pending_sub'] = $sub;
header('Location: google_nickname.php');
exit;

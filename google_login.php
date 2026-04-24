<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$oauthErrors = google_oauth_config_validation_errors();
if ($oauthErrors !== []) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>설정 필요</title></head><body>';
    echo '<p>Google OAuth 설정 오류:</p>';
    foreach ($oauthErrors as $msg) {
        echo '<p>' . h($msg) . '</p>';
    }
    $effectiveRedirect = trim(google_oauth_redirect_uri());
    if ($effectiveRedirect !== '') {
        echo '<p>리디렉션 URI (Google Cloud Console에 등록): <strong>' . h($effectiveRedirect) . '</strong></p>';
    }
    echo '<p><a href="index.php">홈</a></p></body></html>';
    exit;
}

$c = google_oauth_config();
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));
$params = http_build_query([
    'client_id' => $c['client_id'],
    'redirect_uri' => google_oauth_redirect_uri(),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account',
], '', '&', PHP_QUERY_RFC3986);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;

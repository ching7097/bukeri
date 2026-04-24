<?php
declare(strict_types=1);

function google_oauth_config_path(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google_oauth.php';
}

/**
 * @return array{client_id:string, client_secret:string, redirect_uri:string}
 */
function google_oauth_config(): array
{
    $path = google_oauth_config_path();
    if (!is_file($path)) {
        return ['client_id' => '', 'client_secret' => '', 'redirect_uri' => ''];
    }
    $c = require $path;
    if (!is_array($c)) {
        return ['client_id' => '', 'client_secret' => '', 'redirect_uri' => ''];
    }
    return [
        'client_id' => (string) ($c['client_id'] ?? ''),
        'client_secret' => (string) ($c['client_secret'] ?? ''),
        'redirect_uri' => (string) ($c['redirect_uri'] ?? ''),
    ];
}

/**
 * @return list<string>
 */
function google_oauth_config_validation_errors(): array
{
    $path = google_oauth_config_path();
    $errors = [];
    if (!is_file($path)) {
        $errors[] = 'config/google_oauth.php 파일이 없습니다. 프로젝트 루트의 config 폴더를 확인하세요.';

        return $errors;
    }
    $c = google_oauth_config();
    if (trim($c['client_id']) === '') {
        $errors[] = 'client_id가 비어 있습니다. config/google_oauth.php 의 client_id 를 채우세요.';
    }
    if (trim($c['client_secret']) === '') {
        $errors[] = 'client_secret이 비어 있습니다. config/google_oauth.php 에 Google Cloud Console의 Client Secret을 입력하세요.';
    }
    $redirect = trim(google_oauth_redirect_uri());
    if ($redirect === '') {
        $errors[] = 'redirect_uri를 사용할 수 없습니다. config/google_oauth.php 의 redirect_uri 를 설정하세요.';
    }

    return $errors;
}

function google_oauth_is_configured(): bool
{
    return google_oauth_config_validation_errors() === [];
}

function google_oauth_redirect_uri(): string
{
    $c = google_oauth_config();
    $fromConfig = trim((string) ($c['redirect_uri'] ?? ''));
    if ($fromConfig !== '') {
        return $fromConfig;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $dir = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');

    return $scheme . '://' . $host . $dir . '/google_callback.php';
}

/**
 * @param array<string, string> $fields
 * @return array<string, mixed>|null
 */
function google_oauth_post_form(string $url, array $fields): ?array
{
    $body = http_build_query($fields);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($body) . "\r\n",
            'content' => $body,
            'timeout' => 25,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);

    return is_array($j) ? $j : null;
}

/**
 * @return array<string, mixed>|null
 */
function google_oauth_exchange_code(string $code): ?array
{
    $c = google_oauth_config();
    $res = google_oauth_post_form('https://oauth2.googleapis.com/token', [
        'code' => $code,
        'client_id' => $c['client_id'],
        'client_secret' => $c['client_secret'],
        'redirect_uri' => google_oauth_redirect_uri(),
        'grant_type' => 'authorization_code',
    ]);
    if ($res === null || empty($res['access_token'])) {
        return null;
    }

    return $res;
}

/**
 * @return array<string, mixed>|null
 */
function google_oauth_userinfo(string $accessToken): ?array
{
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $accessToken . "\r\n",
            'timeout' => 25,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents('https://openidconnect.googleapis.com/v1/userinfo', false, $ctx);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);

    return is_array($j) ? $j : null;
}

<?php
declare(strict_types=1);

function auth_google_logged_in(): bool
{
    $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($uid <= 0) {
        return false;
    }
    $st = db()->prepare('SELECT google_id FROM users WHERE id = ? LIMIT 1');
    $st->execute([$uid]);
    $g = $st->fetchColumn();
    if ($g === false || $g === null) {
        return false;
    }
    $g = (string) $g;

    return $g !== '';
}

/**
 * @return array<string, mixed>|null
 */
function auth_google_user_row(): ?array
{
    $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($uid <= 0 || !auth_google_logged_in()) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([$uid]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * @return array<string, mixed>|null
 */
function user_find_by_google_id(string $googleSub): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE google_id = ? LIMIT 1');
    $st->execute([$googleSub]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * 글·댓글 작성 시: Google 로그인이면 DB 닉네임, 아니면 기존 닉네임 해석.
 *
 * @return array{ok:true, user_id:int, nickname:string}|array{ok:false, error:string}
 */
function auth_or_nickname_for_write(string $rawNickFromForm): array
{
    if (auth_google_logged_in()) {
        $row = auth_google_user_row();
        if (!$row) {
            return ['ok' => false, 'error' => '로그인 정보를 확인할 수 없습니다. 다시 로그인해 주세요.'];
        }

        return ['ok' => true, 'user_id' => (int) $row['id'], 'nickname' => (string) $row['nickname']];
    }

    return nickname_resolve_for_action($rawNickFromForm);
}

<?php
declare(strict_types=1);

/**
 * Session-bound unique nicknames: first claim wins globally; browser session locks to one user row.
 *
 * @return array{ok:true, user_id:int, nickname:string}|array{ok:false, error:string}
 */
function nickname_resolve_for_action(string $rawNick): array
{
    $norm = nickname_normalize($rawNick);
    if ($norm === '') {
        return ['ok' => false, 'error' => '닉네임을 입력해 주세요.'];
    }
    $bad = nickname_profanity_reason($norm);
    if ($bad !== null) {
        return ['ok' => false, 'error' => $bad];
    }

    $db = db();
    $sessUid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    if ($sessUid > 0) {
        $st = $db->prepare('SELECT id, nickname FROM users WHERE id = ? LIMIT 1');
        $st->execute([$sessUid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            unset($_SESSION['user_id']);
            $sessUid = 0;
        } else {
            $mine = (string) $row['nickname'];
            if (nickname_normalize($mine) !== $norm) {
                return ['ok' => false, 'error' => '이 브라우저에 등록된 닉네임은 「' . $mine . '」입니다. 해당 닉네임으로만 글·댓글을 쓸 수 있습니다.'];
            }
            return ['ok' => true, 'user_id' => $sessUid, 'nickname' => $mine];
        }
    }

    $st = $db->prepare('SELECT id, nickname FROM users WHERE nickname = ? LIMIT 1');
    $st->execute([$norm]);
    $ex = $st->fetch(PDO::FETCH_ASSOC);
    if ($ex) {
        return ['ok' => false, 'error' => '이미 다른 분이 사용 중인 닉네임입니다. 다른 닉네임을 정해 주세요.'];
    }

    $st = $db->prepare('INSERT INTO users (nickname, created_at) VALUES (?, ?)');
    $st->execute([$norm, time()]);
    $newId = (int) $db->lastInsertId();
    $_SESSION['user_id'] = $newId;

    return ['ok' => true, 'user_id' => $newId, 'nickname' => $norm];
}

function nickname_normalize(string $raw): string
{
    $s = trim(preg_replace('/\s+/u', ' ', $raw) ?? '');
    if (function_exists('mb_substr')) {
        $s = mb_substr($s, 0, 64, 'UTF-8');
    } elseif (strlen($s) > 192) {
        $s = substr($s, 0, 64);
    }
    return $s;
}

function nickname_session_display(): ?string
{
    $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($uid <= 0) {
        return null;
    }
    $st = db()->prepare('SELECT nickname FROM users WHERE id = ? LIMIT 1');
    $st->execute([$uid]);
    $n = $st->fetchColumn();
    return $n !== false ? (string) $n : null;
}

/** @return string|null Error message if blocked */
function nickname_profanity_reason(string $nick): ?string
{
    $lower = $nick;
    if (function_exists('mb_strtolower')) {
        $lower = mb_strtolower($nick, 'UTF-8');
    } else {
        $lower = strtolower($nick);
    }
    foreach (nickname_banned_substrings() as $bad) {
        $b = $bad;
        if (function_exists('mb_strtolower')) {
            $b = mb_strtolower($bad, 'UTF-8');
        } else {
            $b = strtolower($bad);
        }
        if ($b === '') {
            continue;
        }
        if (function_exists('mb_strpos')) {
            if (mb_strpos($lower, $b, 0, 'UTF-8') !== false) {
                return '닉네임에 사용할 수 없는 표현이 포함되어 있습니다.';
            }
        } elseif (strpos($lower, $b) !== false) {
            return '닉네임에 사용할 수 없는 표현이 포함되어 있습니다.';
        }
    }
    return null;
}

/**
 * @return list<string>
 */
function nickname_banned_substrings(): array
{
    return [
        '시발', '씨발', 'ㅅㅂ', '병신', '좆', '씹', '개새', '지랄', 'fuck', 'shit',
    ];
}

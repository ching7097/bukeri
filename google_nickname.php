<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$pending = (string) ($_SESSION['google_oauth_pending_sub'] ?? '');
if ($pending === '') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $norm = nickname_normalize(trim((string) ($_POST['nickname'] ?? '')));
    if ($norm === '') {
        $_SESSION['join_err'] = '닉네임을 입력해 주세요.';
        header('Location: google_nickname.php');
        exit;
    }
    $bad = nickname_profanity_reason($norm);
    if ($bad !== null) {
        $_SESSION['join_err'] = $bad;
        header('Location: google_nickname.php');
        exit;
    }
    $chk = db()->prepare('SELECT id FROM users WHERE nickname = ? LIMIT 1');
    $chk->execute([$norm]);
    if ($chk->fetchColumn() !== false) {
        $_SESSION['join_err'] = '이미 사용 중인 닉네임입니다.';
        header('Location: google_nickname.php');
        exit;
    }

    $sub = (string) ($_SESSION['google_oauth_pending_sub'] ?? '');
    if ($sub === '') {
        header('Location: index.php');
        exit;
    }

    $st = db()->prepare('INSERT INTO users (google_id, nickname, created_at) VALUES (?,?,?)');
    $st->execute([$sub, $norm, time()]);
    $_SESSION['user_id'] = (int) db()->lastInsertId();
    unset($_SESSION['google_oauth_pending_sub']);
    unset($_SESSION['join_err']);
    $_SESSION['toast'] = 'Google 계정으로 가입·로그인되었습니다.';
    header('Location: index.php');
    exit;
}

$pageTitle = '닉네임 설정 (Google) | 부커리';
$boardSlug = 'house';
$err = (string) ($_SESSION['join_err'] ?? '');
unset($_SESSION['join_err']);

ob_start();
?>
<div class="dc-write-wrap">
    <section class="dc-write-frame" aria-label="닉네임 설정">
        <div class="dc-write-frame-top">
            <span><a href="index.php">← 홈</a></span>
            <span class="dc-write-board"><strong>Google 첫 로그인</strong> · 닉네임만 정하면 끝</span>
        </div>
        <div class="dc-join-body" style="padding:12px 14px 16px">
            <?php if ($err !== ''): ?>
                <p class="write-flash-err" role="alert"><?= h($err) ?></p>
            <?php endif; ?>
            <p class="dc-join-hint" style="margin:0 0 12px;font-size:12px;color:#444">이 닉네임은 이 사이트에서 글·댓글에 표시됩니다. 한 번 정하면 Google 계정과 묶입니다.</p>
            <form class="dc-join-form" method="post">
                <label class="dc-write-row-lbl" for="g-nick">닉네임</label>
                <input id="g-nick" type="text" name="nickname" class="dc-inp dc-inp-title" required maxlength="64" autocomplete="username" placeholder="닉네임">
                <div class="dc-write-actions">
                    <button type="submit" class="dc-btn-submit">저장하고 시작</button>
                </div>
            </form>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/templates/shell.php';

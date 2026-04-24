<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (nickname_session_display() !== null) {
        header('Location: join.php');
        exit;
    }
    $raw = trim((string) ($_POST['nickname'] ?? ''));
    $res = nickname_resolve_for_action($raw);
    if (!$res['ok']) {
        $_SESSION['join_err'] = $res['error'];
        header('Location: join.php');
        exit;
    }
    $_SESSION['toast'] = '가입 완료. 앞으로 이 브라우저에서는 「' . $res['nickname'] . '」닉네임으로 글·댓글을 씁니다.';
    header('Location: index.php');
    exit;
}

$pageTitle = '가입 · 닉네임 등록 | 부커리';
$boardSlug = 'house';
$registeredNick = nickname_session_display();
$joinErr = (string) ($_SESSION['join_err'] ?? '');
unset($_SESSION['join_err']);

ob_start();
?>
<div class="dc-write-wrap">
    <section class="dc-write-frame" aria-label="가입">
        <div class="dc-write-frame-top">
            <span><a href="index.php">← 홈</a></span>
            <span class="dc-write-board"><strong>부커리 가입</strong> · 닉네임만 정하면 됩니다</span>
        </div>
        <div class="dc-join-body">
            <?php if ($joinErr !== ''): ?>
                <p class="write-flash-err" role="alert"><?= h($joinErr) ?></p>
            <?php endif; ?>

            <?php if ($registeredNick !== null): ?>
                <p class="dc-join-done">이미 이 브라우저에서 가입되어 있습니다.</p>
                <p class="dc-join-nick-show">닉네임: <strong><?= h($registeredNick) ?></strong></p>
                <p class="dc-join-hint">비밀번호 없이 세션으로만 구분합니다. 다른 기기·브라우저에서는 다시 가입(닉 중복 시 다른 닉)이 필요합니다.</p>
                <p><a href="index.php">홈으로</a> · <a href="write.php">글쓰기</a></p>
            <?php else: ?>
                <p class="dc-join-hint">한 닉네임은 한 사람만 쓸 수 있습니다. 욕설·불건전 닉은 제한됩니다.</p>
                <form class="dc-join-form" method="post" id="dc-join-form">
                    <label class="dc-write-row-lbl" for="join-nick">닉네임</label>
                    <input id="join-nick" type="text" name="nickname" class="dc-inp dc-inp-title" required maxlength="64" autocomplete="username" placeholder="사용할 닉네임">
                    <div class="dc-write-actions">
                        <button type="submit" class="dc-btn-submit">가입 완료</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>
<style>
.dc-join-body { padding: 12px 14px 16px; }
.dc-join-hint { margin: 0 0 12px; font-size: 12px; color: #444; line-height: 1.5; }
.dc-join-done { margin: 0 0 6px; font-weight: 900; font-size: 14px; }
.dc-join-nick-show { margin: 0 0 12px; font-size: 15px; }
.dc-join-form { display: flex; flex-direction: column; gap: 0; }
</style>
<?php
$content = ob_get_clean();
require __DIR__ . '/templates/shell.php';

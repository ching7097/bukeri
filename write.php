<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$boards = require __DIR__ . '/config/boards.php';
$defaultBoard = board_normalize((string) ($_GET['b'] ?? 'house'), $boards);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gal = board_normalize((string) ($_POST['gallery'] ?? $defaultBoard), $boards);
    $category = $boards[$gal] ?? '일반';
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['content'] ?? ''));
    $nickRaw = trim((string) ($_POST['author'] ?? ''));

    $res = auth_or_nickname_for_write($nickRaw);
    if (!$res['ok']) {
        $_SESSION['flash_err'] = $res['error'];
        header('Location: write.php?b=' . urlencode($gal));
        exit;
    }

    if ($title !== '' && $body !== '') {
        $newId = post_create($res['user_id'], $category, $title, $body, $res['nickname'], $gal);
        post_images_save_from_form($newId, $_FILES['images'] ?? null);
        header('Location: view.php?id=' . $newId);
        exit;
    }
    $_SESSION['flash_err'] = '제목과 내용을 입력해 주세요.';
    header('Location: write.php?b=' . urlencode($gal));
    exit;
}

$pageTitle = '글쓰기 | 부커리';
$boardSlug = $defaultBoard;
$flashErr = (string) ($_SESSION['flash_err'] ?? '');
unset($_SESSION['flash_err']);
$sessNick = nickname_session_display();
$boardLabel = $boards[$defaultBoard] ?? '';
$googleLogged = auth_google_logged_in();

ob_start();
?>
<div class="dc-write-wrap">
    <?php if ($flashErr !== ''): ?>
        <p class="write-flash-err" role="alert"><?= h($flashErr) ?></p>
    <?php endif; ?>
    <section class="dc-write-frame" aria-label="글쓰기">
        <div class="dc-write-frame-top">
            <span><a href="index.php?b=<?= h($defaultBoard) ?>">← <?= h($boardLabel) ?> 목록</a></span>
            <span class="dc-write-board">등록 판: <strong><?= h($boardLabel) ?></strong></span>
        </div>
        <div class="dc-write-board-pick" aria-label="판 선택">
            <?php foreach ($boards as $slug => $label): ?>
                <a href="write.php?b=<?= h($slug) ?>" class="<?= $slug === $defaultBoard ? 'is-here' : '' ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </div>
        <?php if ($googleLogged && $sessNick !== null && $sessNick !== ''): ?>
            <p class="dc-write-nick-ok">Google 로그인 · 닉네임: <strong><?= h((string) $sessNick) ?></strong> (아래 닉 입력란 없음)</p>
        <?php elseif ($sessNick === null || $sessNick === ''): ?>
            <p class="dc-write-need-join">닉네임은 <a href="join.php">가입</a> 또는 <a href="google_login.php">Google 로그인</a> 때 정합니다. (가입 없이 쓰려면 아래 닉 칸을 비우면 임시 익명)</p>
        <?php else: ?>
            <p class="dc-write-nick-ok">가입 닉네임: <strong><?= h((string) $sessNick) ?></strong></p>
        <?php endif; ?>
        <form id="dc-write-form" class="dc-write-form" method="post" enctype="multipart/form-data" data-google-logged="<?= $googleLogged ? '1' : '0' ?>">
            <input type="hidden" name="gallery" value="<?= h($defaultBoard) ?>">
            <input type="hidden" name="author" id="dc-write-author" value="<?= h((string) ($sessNick ?? '')) ?>">
            <label class="dc-write-row-lbl" for="dc-inp-title">제목</label>
            <input id="dc-inp-title" type="text" name="title" class="dc-inp dc-inp-title" required maxlength="200" placeholder="제목을 입력하세요" autocomplete="off">
            <label class="dc-write-row-lbl" for="dc-inp-body">내용</label>
            <textarea id="dc-inp-body" name="content" class="dc-inp dc-inp-body" rows="16" required placeholder="내용을 입력하세요"></textarea>
            <label class="dc-write-row-lbl" for="dc-inp-file">이미지 첨부 (선택)</label>
            <div class="dc-write-file-row">
                <input id="dc-inp-file" name="images[]" type="file" class="dc-file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
            </div>
            <div class="dc-write-actions">
                <?php if (!$googleLogged): ?>
                    <input type="text" id="dc-write-nick-opt" class="dc-inp dc-inp-nick" maxlength="64" placeholder="닉네임 · 비우면 익명" autocomplete="off" value="<?= h((string) ($sessNick ?? '')) ?>">
                <?php endif; ?>
                <button type="submit" class="dc-btn-submit">등록</button>
            </div>
        </form>
    </section>
</div>
<script>
(function () {
    var form = document.getElementById('dc-write-form');
    if (!form) return;
    var hid = document.getElementById('dc-write-author');
    var opt = document.getElementById('dc-write-nick-opt');
    form.addEventListener('submit', function () {
        if (form.getAttribute('data-google-logged') === '1') {
            return;
        }
        var v = (opt && opt.value) ? opt.value.trim() : '';
        if (v === '') {
            try {
                v = sessionStorage.getItem('bukkeri_nick') || '';
                if (!v) {
                    v = '익명' + String(Math.floor(10000 + Math.random() * 90000));
                    sessionStorage.setItem('bukkeri_nick', v);
                }
            } catch (e) {
                v = '익명' + String(Math.floor(10000 + Math.random() * 90000));
            }
        } else {
            try { sessionStorage.setItem('bukkeri_nick', v); } catch (e) {}
        }
        if (hid) hid.value = v;
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/templates/shell.php';

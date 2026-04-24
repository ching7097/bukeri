<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle ?? '부커리 | 전세 · 월세 · 계약 고민 커뮤니티') ?></title>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="icon" href="assets/images/bookeri_logo_final.png" type="image/png">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8573716814546415" crossorigin="anonymous"></script>
</head>
<body>
<?php if (empty($hideShellHeader)): ?>
<?php
$navBoards = is_file(__DIR__ . '/../config/boards.php') ? require __DIR__ . '/../config/boards.php' : [];
?>
<header class="site-hd">
    <a href="index.php" class="brand" aria-label="부커리 홈" title="부커리">
        <img class="site-hd-brand-img" src="assets/images/bookeri_logo_final.png" alt="부커리" width="150" height="40" decoding="async">
    </a>
    <nav class="nav site-hd-nav-main" aria-label="커뮤니티 메뉴">
        <a href="index.php">홈</a>
        <?php foreach ($navBoards as $slug => $label): ?>
            <a href="index.php?b=<?= h($slug) ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
        <a href="checklist.php">체크리스트</a>
        <a href="mustknow.php">필수정보</a>
    </nav>
    <div class="site-hd-user" aria-label="계정">
        <?php if (nickname_session_display() !== null): ?>
            <span class="site-hd-nick" title="계정"><?= h((string) nickname_session_display()) ?></span>
            <span class="site-hd-user-sep">|</span>
            <a href="logout.php" class="site-hd-account-link site-hd-logout">로그아웃</a>
            <span class="site-hd-user-sep">|</span>
        <?php else: ?>
            <a href="google_login.php" class="site-hd-account-link">로그인</a>
            <span class="site-hd-user-sep">|</span>
        <?php endif; ?>
        <a href="write.php?b=<?= h($boardSlug ?? 'house') ?>" class="site-hd-write-btn">글쓰기</a>
    </div>
</header>
<?php endif; ?>
<main class="site-main">
    <?php if (!empty($_SESSION['toast'])): ?>
        <p class="site-toast" role="status"><?= h((string) $_SESSION['toast']) ?></p>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
    <?= $content ?>
</main>
<?php
if (function_exists('nickname_session_display')) {
    $sn = nickname_session_display();
    if ($sn !== null && $sn !== '') {
        echo '<script>try{sessionStorage.setItem("bukkeri_nick",' . json_encode($sn, JSON_UNESCAPED_UNICODE) . ');}catch(e){}</script>';
    }
}
?>
</body>
</html>

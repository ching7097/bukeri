<?php
declare(strict_types=1);

/** @var array<string, string> $boards */

$pillars = require __DIR__ . '/../config/pillars.php';
$gallerySlugs = $pillars['gallery'];

$realtimePosts = posts_latest(12);
$hotPosts = posts_trending(12);
$galleryPosts = posts_latest_in_galleries($gallerySlugs, 12);
$galleryIds = array_map(static fn (array $r): int => (int) $r['id'], $galleryPosts);
$galleryThumbs = posts_first_image_paths($galleryIds);
$latestPosts = posts_latest(15);
$hotIds = array_map(static fn (array $r): int => (int) $r['id'], $hotPosts);
$hotThumbs = $hotIds !== [] ? posts_first_image_paths($hotIds) : [];

$hubGoogleNick = nickname_session_display();

/** 대표 로고 · 썸네일 없을 때 기본 이미지 (프로젝트 assets 경로) */
$hubBrandLogo = 'assets/images/bookeri_logo_final.png';

$hubCommentPool = [];
foreach ([$hotPosts, $realtimePosts, $latestPosts] as $_hubList) {
    foreach ($_hubList as $_row) {
        $iid = (int) $_row['id'];
        $ccc = (int) ($_row['comment_count'] ?? 0);
        if (!isset($hubCommentPool[$iid]) || $ccc > (int) ($hubCommentPool[$iid]['comment_count'] ?? 0)) {
            $hubCommentPool[$iid] = $_row;
        }
    }
}
$mostCommented = array_values($hubCommentPool);
usort(
    $mostCommented,
    static fn (array $a, array $b): int => (int) ($b['comment_count'] ?? 0) <=> (int) ($a['comment_count'] ?? 0)
);
$mostCommented = array_slice($mostCommented, 0, 10);
?>
<style>
.hub-dc { font-family: "Malgun Gothic", "Apple SD Gothic Neo", "Noto Sans KR", system-ui, sans-serif; font-size: 13px; color: #111; background: linear-gradient(180deg, #eef0f6 0%, #f5f5f7 48%, #ebecef 100%); margin: 0; padding: 0 0 24px; }
.hub-dc a { color: #1a3a9e; text-decoration: none; }
.hub-dc a:hover { text-decoration: none; }
/* 디씨형 1단: 흰 배경 · 대표 로고 (남색 줄과 분리) */
.hub-topstripe {
    background: #fff;
    border-bottom: 1px solid #c5cad6;
    box-shadow: 0 1px 0 rgba(30, 40, 90, 0.04);
}
.hub-topstripe-inner {
    max-width: var(--site-content-max, 1100px);
    margin: 0 auto;
    width: 100%;
    padding: 18px 16px 14px;
    box-sizing: border-box;
}
.hub-brand-hero {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    line-height: 0;
    text-decoration: none !important;
    color: inherit;
}
.hub-brand-hero:hover { opacity: 0.92; }
/* 디씨 메인 상단 로고에 가깝게: 포털형 크게 */
.hub-brand-hero__img {
    height: 84px;
    width: auto;
    max-width: min(520px, 100%);
    object-fit: contain;
    object-position: left center;
    display: block;
}
.hub-brand-hero__tag {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #3d5a96;
    letter-spacing: -0.02em;
    line-height: 1.35;
}
.hub-bar {
    background: #29367c;
    color: #fff;
    padding: 0;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.35;
}
.hub-bar-inner {
    max-width: var(--site-content-max, 1100px);
    margin: 0 auto;
    width: 100%;
    padding: 6px 14px;
    box-sizing: border-box;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px 16px;
}
.hub-bar-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 10px;
    flex: 1;
    min-width: 0;
}
.hub-bar-nav > a {
    color: #fff;
    text-decoration: none;
    padding: 2px 0;
    border-radius: 2px;
}
.hub-bar-nav > a:hover { text-decoration: underline; }
.hub-thumb--brand {
    object-fit: contain !important;
    padding: 10px 12px;
    background: #0a0a0c;
    box-sizing: border-box;
}
.hub-bar .sep { opacity: 0.45; user-select: none; font-weight: 400; margin: 0 2px; }
.hub-bar-user {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px 18px;
    flex-shrink: 0;
    margin-left: auto;
}
.hub-user-link {
    color: rgba(255, 255, 255, 0.88) !important;
    text-decoration: none !important;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: -0.02em;
    padding: 2px 0;
    border: 0;
    background: none;
    cursor: pointer;
    font-family: inherit;
}
.hub-user-link:hover { color: #fff !important; text-decoration: underline !important; }
.hub-user-nick {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.95);
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.hub-user-sep {
    color: rgba(255, 255, 255, 0.4);
    font-weight: 400;
    font-size: 11px;
    user-select: none;
    margin: 0 -6px;
}
.hub-bar-write {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 11px;
    min-height: 0;
    line-height: 1.35;
    background: #fff;
    color: #1e2859 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    letter-spacing: -0.02em;
    text-decoration: none !important;
    border-radius: 2px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
    white-space: nowrap;
}
.hub-bar-write:hover {
    background: #f0f3ff;
    color: #121a40 !important;
    text-decoration: none !important;
}
.hub-wrap {
    width: 100%;
    max-width: var(--site-content-max, 1100px);
    margin: 0 auto;
    padding: 10px 14px 0;
    box-sizing: border-box;
}
.hub-layout {
    display: flex;
    align-items: stretch;
    width: 100%;
    background: #fff;
    border: 1px solid #c5cad6;
    border-radius: 2px;
    box-sizing: border-box;
    box-shadow: 0 2px 12px rgba(30, 40, 90, 0.06);
}
.hub-main {
    flex: 7 1 0;
    min-width: 0;
    border-right: 1px solid #ddd;
    background: #fff;
}
.hub-side {
    flex: 3 1 0;
    min-width: 240px;
    max-width: 380px;
    background: #f4f5f9;
    box-sizing: border-box;
}
.hub-side-blk {
    border-bottom: 1px solid #ddd;
    padding: 14px 14px 16px;
}
.hub-side-blk:last-child { border-bottom: 0; }
.hub-side-hd {
    margin: 0 0 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ccc;
    font-size: 12px;
    font-weight: 800;
    color: #1e2859;
    letter-spacing: -0.02em;
}
.hub-side-muted { margin: 0 0 10px; font-size: 12px; color: #555; line-height: 1.45; }
.hub-side-user { margin: 0 0 10px; font-size: 12px; color: #333; line-height: 1.5; }
.hub-side-user strong { font-size: 13px; color: #111; }
.hub-side-actions { display: flex; flex-wrap: wrap; gap: 8px 10px; align-items: center; margin-top: 8px; }
.hub-side-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    background: #29367c;
    color: #fff !important;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    border-radius: 3px;
    border: 1px solid #1e2859;
}
.hub-side-btn:hover { background: #1e2859; color: #fff !important; }
.hub-side-btn--ghost {
    background: #fff;
    color: #29367c !important;
    border-color: #29367c;
}
.hub-side-btn--ghost:hover { background: #eef1fb; }
.hub-side-list { list-style: none; margin: 0; padding: 0; font-size: 12px; }
.hub-side-list li {
    padding: 7px 0;
    border-bottom: 1px solid #e8e8e8;
    display: flex;
    gap: 8px;
    align-items: baseline;
    line-height: 1.35;
}
.hub-side-list li:last-child { border-bottom: 0; }
.hub-side-list a {
    flex: 1;
    min-width: 0;
    color: #1a3a9e;
    font-weight: 800;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    letter-spacing: -0.02em;
}
.hub-side-list a:hover { color: #c45c48; text-decoration: underline; }
.hub-side-meta { font-size: 11px; color: #777; white-space: nowrap; flex-shrink: 0; }
.hub-side-list--cc .hub-side-cc {
    flex-shrink: 0;
    min-width: 22px;
    text-align: center;
    font-size: 11px;
    font-weight: 900;
    color: #fff;
    background: #c45c48;
    padding: 2px 6px;
    border-radius: 4px;
    font-variant-numeric: tabular-nums;
}
.hub-side-engage-hint {
    margin: 0 0 10px;
    font-size: 11px;
    color: #5a6270;
    line-height: 1.45;
    padding: 8px 10px;
    background: rgba(41, 54, 124, 0.06);
    border-radius: 4px;
    border-left: 3px solid #29367c;
}
.hub-rank-list { list-style: none; margin: 0; padding: 0; font-size: 12px; counter-reset: rank; }
.hub-rank-list li {
    counter-increment: rank;
    padding: 6px 0 6px 28px;
    border-bottom: 1px solid #e8e8e8;
    position: relative;
    line-height: 1.35;
}
.hub-rank-list li:last-child { border-bottom: 0; }
.hub-rank-list li::before {
    content: counter(rank);
    position: absolute;
    left: 0;
    top: 6px;
    width: 20px;
    text-align: center;
    font-weight: 800;
    font-size: 11px;
    color: #c45c48;
    font-variant-numeric: tabular-nums;
}
.hub-rank-list a { color: #1a3a9e; font-weight: 800; text-decoration: none; letter-spacing: -0.02em; }
.hub-rank-list a:hover { color: #c45c48; text-decoration: underline; }
.hub-rank-stat { display: block; font-size: 11px; color: #888; margin-top: 2px; }
.hub-hot-strip { margin: 0; border-bottom: 1px solid #e8eaef; }
.hub-hot-strip-hd {
    margin: 0;
    padding: 10px 14px;
    background: linear-gradient(90deg, #f8f9fc 0%, #fff 100%);
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    font-weight: 900;
    color: #1e2859;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.hub-sec-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 4px 8px;
    border-radius: 4px;
    color: #fff;
    background: linear-gradient(135deg, #e85d4a, #b83828);
    box-shadow: 0 1px 4px rgba(200, 60, 40, 0.35);
}
.hub-sec-label--live {
    background: linear-gradient(135deg, #2d6a4f, #1b4332);
    box-shadow: 0 1px 4px rgba(30, 90, 60, 0.3);
}
.hub-sec-label--new {
    background: linear-gradient(135deg, #4361ee, #2940b0);
    box-shadow: 0 1px 4px rgba(50, 70, 200, 0.25);
}
.hub-hot-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px 16px;
    padding: 14px 14px 18px;
}
.hub-hot-card {
    display: flex;
    flex-direction: column;
    border: 1px solid #d8dce8;
    background: #fff;
    color: inherit;
    text-decoration: none;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
}
.hub-hot-card:hover {
    border-color: #29367c;
    box-shadow: 0 8px 24px rgba(41, 54, 124, 0.18);
    transform: translateY(-3px);
}
.hub-hot-card__media {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #1a2240;
}
.hub-hot-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.35s ease;
}
.hub-hot-card:hover .hub-hot-card__media img:not(.hub-thumb--brand) { transform: scale(1.04); }
.hub-card-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 2;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.08em;
    padding: 4px 7px;
    border-radius: 4px;
    color: #fff;
    background: linear-gradient(135deg, #e85d4a, #c03d2e);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
.hub-hot-card__body { padding: 10px 12px 12px; flex: 1; display: flex; align-items: flex-start; }
.hub-hot-card__title {
    font-size: 13px;
    font-weight: 800;
    line-height: 1.4;
    word-break: break-word;
    color: #141824;
    letter-spacing: -0.02em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.15s ease;
}
.hub-hot-card:hover .hub-hot-card__title { color: #c45c48; }
.hub-blk { background: #fff; border-bottom: 1px solid #e8eaef; margin: 0; }
.hub-blk:last-child { border-bottom: 0; }
.hub-blk-hd {
    margin: 0;
    padding: 8px 14px;
    border-bottom: 1px solid #d5dae6;
    background: linear-gradient(180deg, #f4f5f9 0%, #fafbfc 100%);
    font-size: 13px;
    font-weight: 900;
    color: #1e2859;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.hub-ul { list-style: none; margin: 0; padding: 0; }
.hub-ul li {
    border-bottom: 1px solid #eef0f4;
    padding: 9px 14px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    transition: background 0.12s ease;
}
.hub-ul li:hover { background: #fafbff; }
.hub-ul li:last-child { border-bottom: 0; }
.hub-pill-new {
    flex-shrink: 0;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.04em;
    color: #fff;
    background: #2d6a4f;
    padding: 3px 7px;
    border-radius: 3px;
}
.hub-ul .t {
    flex: 1;
    min-width: 120px;
    font-weight: 800;
    word-break: break-word;
    color: #141824;
    letter-spacing: -0.02em;
    transition: color 0.15s ease;
}
.hub-ul .t:hover { color: #c45c48; text-decoration: none; }
.hub-ul .m { font-size: 12px; color: #5a6270; white-space: nowrap; font-variant-numeric: tabular-nums; }
.hub-gal { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; padding: 14px; }
.hub-gal a {
    display: flex;
    flex-direction: column;
    border: 1px solid #d8dce8;
    color: inherit;
    text-decoration: none;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
}
.hub-gal a:hover {
    border-color: #29367c;
    box-shadow: 0 8px 22px rgba(41, 54, 124, 0.15);
    transform: translateY(-2px);
}
.hub-gal__media {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: #1a2240;
}
.hub-gal__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.35s ease; }
.hub-gal a:hover .hub-gal__media img:not(.hub-thumb--brand) { transform: scale(1.05); }
.hub-gal .cap {
    padding: 10px 11px 11px;
    font-size: 12px;
    line-height: 1.35;
    display: flex;
    justify-content: space-between;
    gap: 8px;
    align-items: flex-start;
}
.hub-gal .gal-t {
    flex: 1;
    min-width: 0;
    word-break: break-word;
    font-weight: 800;
    color: #141824;
    letter-spacing: -0.02em;
    transition: color 0.15s ease;
}
.hub-gal a:hover .gal-t { color: #c45c48; }
.hub-gal .cc { color: #c45c48; font-weight: 900; flex-shrink: 0; font-variant-numeric: tabular-nums; }
.hub-gal-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 2;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.06em;
    padding: 3px 7px;
    border-radius: 4px;
    color: #fff;
    background: rgba(30, 40, 90, 0.88);
}
.hub-main .hub-links { padding: 10px 14px; font-size: 12px; border-bottom: 1px solid #e0e4ed; background: #f8f9fc; }
.hub-main .hub-links a { font-weight: 700; }
.hub-main .hub-links a:hover { color: #c45c48; }
@media (max-width: 900px) {
    .hub-layout { flex-direction: column; }
    .hub-main { border-right: 0; border-bottom: 1px solid #ddd; }
    .hub-side { max-width: none; width: 100%; flex: none; }
    .hub-hot-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
}
@media (max-width: 720px) {
    .hub-gal { grid-template-columns: repeat(2, 1fr); }
    .hub-bar-user { margin-left: 0; width: 100%; justify-content: flex-end; }
    .hub-topstripe-inner { padding: 14px 12px 12px; }
    .hub-brand-hero__img { height: 58px; max-width: min(340px, 100%); }
    .hub-brand-hero__tag { font-size: 12px; }
}
</style>

<div class="hub-dc">
    <div class="hub-topstripe" aria-label="사이트 상단">
        <div class="hub-topstripe-inner">
            <a href="index.php" class="hub-brand-hero" title="부커리 홈">
                <img class="hub-brand-hero__img" src="<?= h($hubBrandLogo) ?>" alt="부커리" width="520" height="84" decoding="async">
                <span class="hub-brand-hero__tag">집·계약 고민 · 맛집 · 갤러리 커뮤니티</span>
            </a>
        </div>
    </div>
    <header class="hub-bar">
        <div class="hub-bar-inner">
            <div class="hub-bar-nav" aria-label="커뮤니티 메뉴">
                <a href="index.php">홈</a>
                <span class="sep">|</span>
                <?php foreach ($boards as $slug => $label): ?>
                    <a href="index.php?b=<?= h($slug) ?>"><?= h($label) ?></a>
                <?php endforeach; ?>
                <span class="sep">|</span>
                <a href="checklist.php">체크리스트</a>
                <span class="sep">|</span>
                <a href="mustknow.php">필수정보</a>
            </div>
            <div class="hub-bar-user" aria-label="계정">
                <?php if ($hubGoogleNick !== null): ?>
                    <span class="hub-user-nick" title="계정"><?= h((string) $hubGoogleNick) ?></span>
                    <span class="hub-user-sep">|</span>
                    <a href="logout.php" class="hub-user-link">로그아웃</a>
                    <span class="hub-user-sep">|</span>
                <?php else: ?>
                    <a href="google_login.php" class="hub-user-link">로그인</a>
                    <span class="hub-user-sep">|</span>
                <?php endif; ?>
                <a class="hub-bar-write" href="write.php">글쓰기</a>
            </div>
        </div>
    </header>

    <div class="hub-wrap">
        <div class="hub-layout">
            <div class="hub-main">
                <div class="hub-links">
                    <a href="checklist.php">계약 체크리스트</a> · <a href="mustknow.php">필수 확인</a>
                </div>

                <section class="hub-hot-strip">
                    <h2 class="hub-hot-strip-hd">
                        <span class="hub-sec-label">HOT</span>
                        <span>🔥 인기 글</span>
                    </h2>
                    <div class="hub-hot-grid">
                        <?php foreach (array_slice($hotPosts, 0, 8) as $r): ?>
                            <?php
                            $hid = (int) $r['id'];
                            $him = $hotThumbs[$hid] ?? null;
                            $hotUseBrand = ($him === null || $him === '');
                            $hotImgSrc = $hotUseBrand ? $hubBrandLogo : (string) $him;
                            ?>
                            <a class="hub-hot-card" href="view.php?id=<?= $hid ?>">
                                <div class="hub-hot-card__media">
                                    <span class="hub-card-badge" aria-hidden="true">HOT</span>
                                    <img class="<?= $hotUseBrand ? 'hub-thumb--brand' : '' ?>" src="<?= h($hotImgSrc) ?>" alt="" loading="lazy" width="320" height="240">
                                </div>
                                <div class="hub-hot-card__body">
                                    <span class="hub-hot-card__title"><?= h((string) $r['title']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($hotPosts === []): ?>
                            <p style="grid-column:1/-1;margin:0;padding:8px 0;font-size:12px;color:#666">인기 글이 없습니다.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="hub-blk">
                    <h2 class="hub-blk-hd">
                        <span class="hub-sec-label hub-sec-label--live">LIVE</span>
                        <span>🔥 실시간 글</span>
                    </h2>
                    <ul class="hub-ul">
                        <?php foreach ($realtimePosts as $r): ?>
                            <?php
                            $hubRowNew = isset($r['created_at']) && (time() - (int) $r['created_at'] < 172800);
                            ?>
                            <li>
                                <?php if ($hubRowNew): ?>
                                    <span class="hub-pill-new">NEW</span>
                                <?php endif; ?>
                                <a class="t" href="view.php?id=<?= (int) $r['id'] ?>"><?= h((string) $r['title']) ?></a>
                                <span class="m"><?= (int) $r['comment_count'] ?> / <?= (int) $r['views'] ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($realtimePosts === []): ?>
                            <li><span class="m">없음</span></li>
                        <?php endif; ?>
                    </ul>
                </section>

                <section class="hub-blk">
                    <h2 class="hub-blk-hd">
                        <span class="hub-sec-label hub-sec-label--new">IMG</span>
                        <span>갤러리</span>
                    </h2>
                    <div class="hub-gal">
                        <?php foreach ($galleryPosts as $r): ?>
                            <?php
                            $pid = (int) $r['id'];
                            $thumb = $galleryThumbs[$pid] ?? null;
                            $galUseBrand = ($thumb === null || $thumb === '');
                            $galImgSrc = $galUseBrand ? $hubBrandLogo : (string) $thumb;
                            ?>
                            <a href="view.php?id=<?= $pid ?>">
                                <div class="hub-gal__media">
                                    <span class="hub-gal-badge" aria-hidden="true">IMG</span>
                                    <img class="<?= $galUseBrand ? 'hub-thumb--brand' : '' ?>" src="<?= h($galImgSrc) ?>" alt="" loading="lazy" width="320" height="320">
                                </div>
                                <div class="cap">
                                    <span class="gal-t"><?= h((string) $r['title']) ?></span>
                                    <span class="cc"><?= (int) $r['comment_count'] ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($galleryPosts === []): ?>
                        <p style="margin:0;padding:8px 10px;font-size:12px;color:#666">갤러리 글 없음</p>
                    <?php endif; ?>
                </section>

                <section class="hub-blk">
                    <h2 class="hub-blk-hd">
                        <span class="hub-sec-label hub-sec-label--new">NEW</span>
                        <span>최신 글</span>
                    </h2>
                    <ul class="hub-ul">
                        <?php foreach ($latestPosts as $r): ?>
                            <?php
                            $hubLatestNew = isset($r['created_at']) && (time() - (int) $r['created_at'] < 172800);
                            ?>
                            <li>
                                <?php if ($hubLatestNew): ?>
                                    <span class="hub-pill-new">NEW</span>
                                <?php endif; ?>
                                <a class="t" href="view.php?id=<?= (int) $r['id'] ?>"><?= h((string) $r['title']) ?></a>
                                <span class="m"><?= h($boards[(string) $r['gallery']] ?? '') ?> · <?= h(date('m-d H:i', (int) $r['created_at'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </div>

            <aside class="hub-side" aria-label="사이드바">
                <div class="hub-side-blk">
                    <p class="hub-side-hd">로그인</p>
                    <?php if ($hubGoogleNick !== null): ?>
                        <p class="hub-side-user">로그인됨<br><strong title="계정"><?= h((string) $hubGoogleNick) ?></strong></p>
                        <div class="hub-side-actions">
                            <a class="hub-side-btn hub-side-btn--ghost" href="logout.php">로그아웃</a>
                            <a class="hub-side-btn" href="write.php">글쓰기</a>
                        </div>
                    <?php else: ?>
                        <p class="hub-side-muted">로그인 후 닉네임이 유지되고 글·댓글 작성이 수월해집니다.</p>
                        <div class="hub-side-actions">
                            <a class="hub-side-btn" href="google_login.php">로그인</a>
                            <a class="hub-side-btn hub-side-btn--ghost" href="join.php">회원가입</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="hub-side-blk">
                    <p class="hub-side-hd">💬 댓글 많은 글</p>
                    <p class="hub-side-engage-hint">지금 댓글이 많이 달린 글이에요.</p>
                    <ul class="hub-side-list hub-side-list--cc">
                        <?php foreach ($mostCommented as $r): ?>
                            <li>
                                <a href="view.php?id=<?= (int) $r['id'] ?>"><?= h((string) $r['title']) ?></a>
                                <span class="hub-side-cc" title="댓글 수"><?= (int) $r['comment_count'] ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($mostCommented === []): ?>
                            <li><span class="hub-side-meta">없음</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="hub-side-blk">
                    <p class="hub-side-hd">🔥 인기 글</p>
                    <ul class="hub-side-list">
                        <?php foreach (array_slice($hotPosts, 0, 10) as $r): ?>
                            <li>
                                <a href="view.php?id=<?= (int) $r['id'] ?>"><?= h((string) $r['title']) ?></a>
                                <span class="hub-side-meta"><?= (int) $r['comment_count'] ?> / <?= (int) $r['views'] ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($hotPosts === []): ?>
                            <li><span class="hub-side-meta">없음</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="hub-side-blk">
                    <p class="hub-side-hd">랭킹 TOP</p>
                    <ul class="hub-rank-list">
                        <?php foreach (array_slice($hotPosts, 0, 10) as $r): ?>
                            <li>
                                <a href="view.php?id=<?= (int) $r['id'] ?>"><?= h((string) $r['title']) ?></a>
                                <span class="hub-rank-stat">댓글 <?= (int) $r['comment_count'] ?> · 조회 <?= (int) $r['views'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($hotPosts === []): ?>
                        <p class="hub-side-muted" style="margin:0">데이터 없음</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>

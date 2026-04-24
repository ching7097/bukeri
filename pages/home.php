<?php
declare(strict_types=1);

/** @var array<string, string> $boards */
/** @var string $boardSlug */
/** @var string $boardTitle */
/** @var array $pinned */
/** @var array<int, array<string, mixed>> $trending */
/** @var array<int, array<string, mixed>> $popularByViews */
/** @var array<int, array<string, mixed>> $mostCommented */
/** @var array<int, array<string, mixed>> $posts */

$qVal = (string) ($_GET['q'] ?? '');

$pillars = require __DIR__ . '/../config/pillars.php';
$isGalleryBoard = in_array($boardSlug, $pillars['gallery'], true);
$postIdsForThumb = array_map(static fn (array $r): int => (int) $r['id'], $posts);
$thumbByPost = [];
if ($isGalleryBoard && $postIdsForThumb !== []) {
    $thumbByPost = posts_first_image_paths($postIdsForThumb);
}

$navGoogleNick = nickname_session_display();

$brandLogoUrl = 'assets/images/bookeri_logo_final.png';

$trendingIdsForThumb = array_map(static fn (array $r): int => (int) $r['id'], $trending);
$trendingThumbs = $trendingIdsForThumb !== [] ? posts_first_image_paths($trendingIdsForThumb) : [];

$postIdsForListThumb = array_map(static fn (array $r): int => (int) $r['id'], $posts);
$listThumbs = $postIdsForListThumb !== [] ? posts_first_image_paths($postIdsForListThumb) : [];

$latestStripPosts = array_slice($posts, 0, 8);
$latestStripIds = array_map(static fn (array $r): int => (int) $r['id'], $latestStripPosts);
$latestStripThumbs = $latestStripIds !== [] ? posts_first_image_paths($latestStripIds) : [];
?>
<style>
.site-main:has(.gall-page) {
    max-width: none;
    width: 100%;
    margin: 0;
    padding: 0 12px 28px;
    box-sizing: border-box;
}
.gall-page {
    max-width: var(--site-content-max, 1100px);
    margin: 0 auto;
    width: 100%;
    --gp-navy: #1e2d5a;
    --gp-navy-mid: #2a3f7a;
    --gp-navy-soft: #e8ebf4;
    --gp-line: #e6e8ee;
    --gp-line-faint: #f0f1f5;
    --gp-text: #1b1f2a;
    --gp-text-2: #5a6270;
    --gp-text-3: #8b919c;
    --gp-surface: #ffffff;
    --gp-surface-sub: #f7f8fb;
    --gp-accent: #c45c48;
    --gp-link: #2a4a9a;
    padding: 0;
    font-family: "Malgun Gothic", "Apple SD Gothic Neo", "Noto Sans KR", system-ui, sans-serif;
    font-size: 14px;
    line-height: 1.45;
    color: var(--gp-text);
    background: var(--gp-surface-sub);
    -webkit-font-smoothing: antialiased;
}

/* —— 상단: 로고 | 검색 | 네비 —— */
.dc-topbar {
    display: flex;
    align-items: stretch;
    flex-wrap: wrap;
    gap: 0;
    width: 100%;
    background: var(--gp-surface);
    border: 1px solid var(--gp-line);
    border-radius: 2px 2px 0 0;
    box-shadow: 0 1px 0 rgba(30, 45, 90, 0.04);
}
.dc-topbar__brand { order: 1; }
.dc-search-cell { order: 2; }
.dc-topbar__brand {
    flex: 0 0 auto;
    min-width: 260px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--gp-line-faint);
    border-right: 1px solid var(--gp-line-faint);
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 2px;
}
.dc-logo { margin: 0; }
.dc-logo-mark {
    display: inline-block;
    line-height: 0;
    text-decoration: none;
}
.dc-logo__img {
    height: 72px;
    width: auto;
    max-width: min(380px, 42vw);
    object-fit: contain;
    object-position: left center;
    display: block;
}
.dc-logo-mark:hover .dc-logo__img { opacity: 0.88; }
.dc-nav-logo {
    display: inline-flex;
    align-items: center;
    line-height: 0;
    padding: 2px 4px;
    border-radius: 4px;
}
.dc-nav-logo:hover { background: rgba(255, 255, 255, 0.12); }
.dc-nav-logo__img {
    height: 34px;
    width: auto;
    max-width: 120px;
    object-fit: contain;
    object-position: left center;
    display: block;
}
.dc-thumb-brand {
    object-fit: contain !important;
    padding: 8px 10px;
    background: #0a0a0c;
    box-sizing: border-box;
}
.dc-logo-tagline {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--gp-text-2);
    letter-spacing: -0.02em;
    line-height: 1.35;
}
.dc-search-cell {
    flex: 1 1 280px;
    min-width: 0;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--gp-line-faint);
    background: var(--gp-surface-sub);
}
.dc-search-form {
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: stretch;
    gap: 0;
    width: 100%;
    max-width: 440px;
}
.dc-search-form input[type="hidden"] { display: none; }
.dc-search-wide {
    flex: 1;
    min-width: 0;
    padding: 10px 14px;
    border: 1px solid var(--gp-line);
    border-right: 0;
    border-radius: 2px 0 0 2px;
    font-size: 13px;
    font-family: inherit;
    color: var(--gp-text);
    background: var(--gp-surface);
    box-sizing: border-box;
}
.dc-search-wide::placeholder { color: var(--gp-text-3); }
.dc-search-wide:focus {
    outline: none;
    border-color: var(--gp-navy-mid);
    box-shadow: 0 0 0 1px var(--gp-navy-mid);
    position: relative;
    z-index: 1;
}
.dc-search-form button {
    flex-shrink: 0;
    padding: 10px 20px;
    border: 1px solid var(--gp-navy);
    border-radius: 0 2px 2px 0;
    background: var(--gp-navy);
    color: #f8f9fc;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    white-space: nowrap;
}
.dc-search-form button:hover { background: var(--gp-navy-mid); border-color: var(--gp-navy-mid); }
.dc-nav-cell {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 4px 0;
    padding: 12px 16px;
    background: var(--gp-navy);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    white-space: nowrap;
}
.dc-nav-cell a {
    color: rgba(255, 255, 255, 0.92);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 2px;
}
.dc-nav-cell a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.dc-nav-cell .dc-nav-sep {
    color: rgba(255, 255, 255, 0.35);
    margin: 0 2px;
    font-weight: 400;
    font-size: 12px;
    user-select: none;
}
.dc-nav-cell--global {
    flex: 1 1 100%;
    width: 100%;
    order: 3;
    justify-content: space-between;
    gap: 10px 18px;
    padding: 8px 14px;
}
.dc-nav-menus {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 8px;
    flex: 1;
    min-width: 0;
    justify-content: flex-start;
}
.dc-nav-user {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px 18px;
    flex-shrink: 0;
    margin-left: auto;
}
.dc-nav-user-nick {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.95);
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dc-nav-user-sep {
    color: rgba(255, 255, 255, 0.4);
    font-weight: 400;
    font-size: 11px;
    user-select: none;
    margin: 0 -6px;
}
.dc-account-link {
    color: rgba(255, 255, 255, 0.88) !important;
    text-decoration: none !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 2px 0 !important;
}
.dc-account-link:hover {
    color: #fff !important;
    text-decoration: underline !important;
    background: transparent !important;
}
.dc-nav-write-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 11px;
    min-height: 0;
    line-height: 1.35;
    background: #fff;
    color: var(--gp-navy) !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    border-radius: 2px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.06);
    white-space: nowrap;
}
.dc-nav-write-btn:hover {
    background: #f0f3ff;
    color: #121a40 !important;
    text-decoration: none !important;
}
@media (max-width: 720px) {
    .dc-nav-user { margin-left: 0; width: 100%; justify-content: flex-end; }
}

/* —— 본문 2단 —— */
.dc-body {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--gp-line);
    border-top: 0;
    background: var(--gp-surface);
    border-radius: 0 0 2px 2px;
    overflow-x: clip;
    overflow-y: visible;
}
.dc-main {
    flex: 1 1 0;
    min-width: 0;
    border-right: 1px solid var(--gp-line-faint);
}
.dc-side {
    flex: 0 0 300px;
    width: 300px;
    max-width: 300px;
    background: var(--gp-surface-sub);
    align-self: flex-start;
}
@media (min-width: 901px) {
    .dc-side {
        position: sticky;
        top: 10px;
        max-height: calc(100vh - 20px);
        overflow-y: auto;
    }
}

.dc-hot-strip {
    border-bottom: 1px solid var(--gp-line-faint);
    background: var(--gp-surface);
}
.dc-hot-strip-hd {
    margin: 0;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 800;
    color: var(--gp-navy);
    background: #f0f2f8;
    border-bottom: 1px solid var(--gp-line-faint);
    letter-spacing: -0.02em;
}
.dc-hot-strip-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 12px 14px 14px;
}
.dc-hot-card {
    display: block;
    border: 1px solid var(--gp-line);
    background: var(--gp-surface);
    color: inherit;
    text-decoration: none;
    border-radius: 2px;
    overflow: hidden;
}
.dc-hot-card:hover { background: #fafbff; border-color: var(--gp-navy-mid); }
.dc-hot-card-im {
    aspect-ratio: 4/3;
    background: #eaeaea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: var(--gp-text-3);
}
.dc-hot-card-im img { width: 100%; height: 100%; object-fit: cover; display: block; }
.dc-hot-card-t {
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.35;
    word-break: break-word;
    color: var(--gp-text);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    letter-spacing: -0.02em;
}
.dc-latest-strip {
    border-bottom: 1px solid var(--gp-line-faint);
    background: var(--gp-surface);
}
.dc-latest-strip-hd {
    margin: 0;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 800;
    color: var(--gp-navy-mid);
    background: #f7f8fc;
    border-bottom: 1px solid var(--gp-line-faint);
    letter-spacing: -0.02em;
}
.dc-ad-leader {
    padding: 12px 16px;
    text-align: center;
    background: #eef0f6;
    border: 1px solid var(--gp-line);
    border-radius: 0;
    border-top: 0;
}
.dc-ad-after-hot {
    padding: 12px 14px;
    text-align: center;
    background: var(--gp-surface-sub);
    border-bottom: 1px solid var(--gp-line-faint);
}
.dc-tbl-ad-cell {
    background: #f8f9fc;
    padding: 12px 10px !important;
    vertical-align: middle !important;
}
.dc-tbl-ad-cell .adsense-wrap { margin: 0 auto; min-height: 0; }
.dc-gal-ad-break {
    grid-column: 1 / -1;
    padding: 12px 0;
    text-align: center;
    background: var(--gp-surface-sub);
    border-top: 1px solid var(--gp-line-faint);
    border-bottom: 1px solid var(--gp-line-faint);
}
.dc-side-ad-slot {
    padding: 12px 12px;
    text-align: center;
    background: #e8ebf4;
}
.dc-side-ad-slot .adsense-wrap { margin: 0 auto; min-height: 0; }
.dc-side .adsense {
    position: sticky;
    top: 20px;
}

/* —— 게시판 헤더 + 미리보기 —— */
.dc-board-head {
    border-bottom: 1px solid var(--gp-line-faint);
    padding: 22px 22px 20px;
    background: var(--gp-surface);
}
.dc-board-title {
    margin: 0 0 10px;
    font-size: 26px;
    font-weight: 800;
    color: var(--gp-navy);
    letter-spacing: -0.045em;
    line-height: 1.25;
}
.dc-tag-hint {
    margin: 0 0 14px;
    font-size: 12px;
    color: var(--gp-text-2);
    line-height: 1.5;
}
.dc-tag-hint code {
    font-family: inherit;
    font-weight: 600;
    font-size: 11px;
    color: #8b3a3a;
    background: #faf4f3;
    padding: 2px 6px;
    border: 1px solid #ead5d2;
    border-radius: 2px;
}
.dc-board-switch {
    margin: 0 0 18px;
    font-size: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 4px;
    align-items: center;
}
.dc-board-switch a {
    color: var(--gp-link);
    padding: 4px 10px;
    text-decoration: none;
    font-weight: 600;
    border-radius: 2px;
}
.dc-board-switch a:hover { background: var(--gp-navy-soft); text-decoration: none; }
.dc-board-switch .here {
    font-weight: 700;
    color: var(--gp-accent);
    background: #fdf5f3;
    border: 1px solid #edd5cf;
}
.dc-danger-top5 {
    margin: 0 0 16px;
    padding: 14px 16px;
    background: #fbf9f7;
    border: 1px solid var(--gp-line);
    border-left: 3px solid #b07a5a;
    border-radius: 2px;
}
.dc-danger-hd {
    margin: 0 0 10px;
    font-size: 13px;
    font-weight: 700;
    color: #6b4a38;
    letter-spacing: -0.02em;
}
.dc-danger-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.dc-danger-list li {
    padding: 8px 0;
    border-bottom: 1px solid var(--gp-line-faint);
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    align-items: baseline;
    line-height: 1.35;
}
.dc-danger-list li:last-child { border-bottom: 0; padding-bottom: 0; }
.dc-danger-list li:first-child { padding-top: 0; }
.dc-danger-list a {
    flex: 1;
    min-width: 140px;
    color: var(--gp-link);
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dc-danger-list a:hover { text-decoration: underline; color: var(--gp-navy-mid); }
.dc-d-stat { font-size: 12px; color: var(--gp-text-2); font-weight: 500; white-space: nowrap; }
.dc-d-cc {
    font-size: 12px;
    font-weight: 700;
    color: var(--gp-accent);
    white-space: nowrap;
}

.dc-rank-pair {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 4px 0 0;
}
.dc-rank-block {
    flex: 1;
    min-width: 200px;
    padding: 12px 14px;
    background: var(--gp-surface-sub);
    border: 1px solid var(--gp-line);
    border-radius: 2px;
}
.dc-rank-hd {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--gp-navy);
    border-bottom: 1px solid var(--gp-line);
    padding-bottom: 8px;
    letter-spacing: -0.02em;
}
.dc-rank-list {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 12px;
}
.dc-rank-list li {
    padding: 6px 0;
    border-bottom: 1px solid var(--gp-line-faint);
    display: flex;
    gap: 8px;
    align-items: baseline;
    line-height: 1.3;
}
.dc-rank-list li:last-child { border-bottom: 0; padding-bottom: 0; }
.dc-rank-list li:first-child { padding-top: 2px; }
.dc-rank-list a {
    flex: 1;
    min-width: 0;
    color: var(--gp-link);
    font-weight: 600;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dc-rank-list a:hover { text-decoration: underline; }
.dc-rank-num { font-weight: 700; color: var(--gp-navy-mid); font-variant-numeric: tabular-nums; white-space: nowrap; font-size: 12px; }
.dc-rank-num--cc { color: var(--gp-accent); }

.adsense-wrap {
    margin: 0 0 12px;
    text-align: center;
    min-height: 0;
}
.gall-ad-row .adsense-wrap {
    margin: 0;
    min-height: 0;
    padding: 4px 0;
}
.gall-list-wrap .gall-ad-row .adsbygoogle {
    min-height: 50px !important;
}

.dc-board-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    background: var(--gp-surface);
    border-bottom: 1px solid var(--gp-line-faint);
}
.dc-board-write {
    display: inline-block;
    padding: 9px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    background: var(--gp-navy);
    border: 1px solid var(--gp-navy);
    border-radius: 2px;
    text-decoration: none;
    white-space: nowrap;
    font-family: inherit;
}
.dc-board-write:hover {
    background: var(--gp-navy-mid);
    border-color: var(--gp-navy-mid);
    color: #fff;
}

.dc-encourage {
    margin: 0;
    padding: 14px 18px;
    background: #f4f7f5;
    border-bottom: 1px solid var(--gp-line-faint);
    font-size: 13px;
    line-height: 1.55;
    color: #2d4a3e;
}
.dc-encourage strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #1e3a30;
}

.ctr-tag {
    display: inline;
    font-size: 11px;
    font-weight: 700;
    color: #9a4a42;
    margin-right: 6px;
    vertical-align: baseline;
    letter-spacing: -0.02em;
}

/* —— 탭 —— */
.dc-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0;
    margin: 0;
    padding: 0 12px;
    background: var(--gp-surface-sub);
    border-bottom: 1px solid var(--gp-line);
}
.dc-tab {
    margin: 0;
    margin-right: -1px;
    padding: 11px 18px 10px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid transparent;
    border-bottom: 0;
    background: transparent;
    color: var(--gp-text-2);
    cursor: pointer;
    border-radius: 2px 2px 0 0;
    position: relative;
}
.dc-tab:hover { color: var(--gp-navy); background: rgba(255, 255, 255, 0.7); }
.dc-tab.is-on {
    background: var(--gp-surface);
    color: var(--gp-navy);
    border-color: var(--gp-line);
    border-bottom-color: var(--gp-surface);
    z-index: 1;
    margin-bottom: -1px;
    padding-bottom: 11px;
}

/* —— 글 목록 테이블 —— */
.gall-list-wrap {
    background: var(--gp-surface);
    border-top: 1px solid var(--gp-line);
}
.gall-list-panel {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.gall-tbl {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
    margin: 0;
}
.gall-tbl thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    box-shadow: 0 1px 0 var(--gp-line);
}
.gall-tbl th,
.gall-tbl td {
    border: 0;
    border-bottom: 1px solid var(--gp-line-faint);
    padding: 12px 10px;
    vertical-align: middle;
    font-size: 13px;
    line-height: 1.4;
}
.gall-tbl th {
    background: #eef0f6;
    text-align: left;
    font-weight: 800;
    font-size: 11px;
    color: var(--gp-navy);
    text-transform: none;
    letter-spacing: -0.03em;
    border-bottom: 1px solid var(--gp-line);
    padding: 11px 10px;
    white-space: nowrap;
}
.gall-tbl th.c-view,
.gall-tbl th.c-cc,
.gall-tbl th.c-rec {
    text-align: right;
}
.gall-tr { cursor: pointer; background: var(--gp-surface); }
.gall-tr:not(.gall-tr-notice):not(.gall-ad-row):hover { background: #f3f6fb; }
.gall-tr:not(.gall-tr-notice):not(.gall-ad-row):active { background: #e9eef8; }
.gall-ad-row { cursor: default; background: #f2f3f7; }
.gall-ad-row:hover { background: #f2f3f7; }
.gall-ad-row td {
    padding: 6px 12px !important;
    border-bottom: 1px solid var(--gp-line) !important;
    vertical-align: middle !important;
}
.gall-tr-notice {
    background: #fcfaf5;
    cursor: default;
}
.gall-tr-notice:hover { background: #f8f4eb; }
.gall-tr-notice td { border-bottom-color: var(--gp-line-faint); }
.gall-tr-notice .c-subj {
    font-weight: 600;
    font-size: 13px;
    color: var(--gp-text-2);
    padding-left: 4px;
}
.c-ico {
    width: 28px;
    text-align: center;
    padding: 12px 6px !important;
    vertical-align: middle;
}
.c-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    vertical-align: middle;
    box-shadow: none;
}
.c-dot--0 { background: #c45c48; }
.c-dot--1 { background: #c17a2d; }
.c-dot--2 { background: #3d7a56; }
.c-dot--n { background: var(--gp-text-3); width: 6px; height: 6px; }
.c-no {
    width: 52px;
    text-align: right;
    font-variant-numeric: tabular-nums;
    color: var(--gp-text-3);
    font-size: 12px;
    font-weight: 600;
    padding-right: 12px !important;
}
.c-subj {
    word-break: break-word;
    font-weight: 600;
    font-size: 14px;
    color: var(--gp-text);
    line-height: 1.45;
    padding-left: 6px !important;
    padding-right: 14px !important;
}
.c-author {
    width: 88px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--gp-text-2);
    font-size: 12px;
    font-weight: 500;
    padding-left: 8px !important;
}
.c-date {
    width: 86px;
    white-space: nowrap;
    color: var(--gp-text-3);
    font-variant-numeric: tabular-nums;
    font-size: 12px;
    padding-left: 8px !important;
}
.c-view {
    width: 52px;
    text-align: right;
    font-variant-numeric: tabular-nums;
    color: var(--gp-text-3);
    font-size: 12px;
    padding-left: 10px !important;
    padding-right: 6px !important;
}
.c-cc {
    width: 44px;
    text-align: right;
    font-weight: 700;
    font-size: 12px;
    color: var(--gp-navy-mid);
    font-variant-numeric: tabular-nums;
    padding-left: 8px !important;
    padding-right: 8px !important;
}
.c-rec {
    width: 44px;
    text-align: right;
    font-weight: 600;
    font-size: 12px;
    color: var(--gp-text-2);
    font-variant-numeric: tabular-nums;
    padding-left: 8px !important;
    padding-right: 12px !important;
}

.gall-list-wrap.filter-concept tbody tr { display: none; }
.gall-list-wrap.filter-concept tbody tr[data-pinned="1"],
.gall-list-wrap.filter-concept tbody tr[data-concept="1"] { display: table-row; }
.gall-list-wrap.filter-notice tbody tr:not(.gall-tr-notice) { display: none; }
.gall-list-wrap.filter-notice .gall-tbl thead { display: none; }
.gall-list-wrap.filter-concept tbody tr[data-ad="1"] { display: none !important; }
.gall-list-wrap.filter-notice tbody tr[data-ad="1"] { display: none !important; }

/* —— 사이드바 —— */
.dc-side-blk {
    border-bottom: 1px solid var(--gp-line-faint);
    padding: 18px 16px 20px;
    background: transparent;
}
.dc-side-blk:last-child { border-bottom: 0; }
.dc-side-hd {
    margin: 0 0 12px;
    padding: 0 0 8px;
    border-bottom: 1px solid var(--gp-line);
    font-size: 12px;
    font-weight: 700;
    color: var(--gp-navy);
    letter-spacing: -0.02em;
}
.dc-side-login input {
    width: 100%;
    box-sizing: border-box;
    margin: 0 0 8px;
    padding: 9px 10px;
    border: 1px solid var(--gp-line);
    border-radius: 2px;
    font-size: 13px;
    font-family: inherit;
    color: var(--gp-text);
    background: var(--gp-surface);
}
.dc-side-login input:focus {
    outline: none;
    border-color: var(--gp-navy-mid);
    box-shadow: 0 0 0 1px rgba(42, 63, 122, 0.15);
}
.dc-side-login .dc-side-act { margin-top: 4px; }
.dc-side-login button {
    padding: 8px 16px;
    border: 1px solid var(--gp-navy);
    border-radius: 2px;
    background: var(--gp-navy);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
}
.dc-side-login button:hover { background: var(--gp-navy-mid); border-color: var(--gp-navy-mid); }
.dc-side-login a { margin-left: 8px; font-size: 12px; }
.dc-side-login-box .dc-side-muted { margin: 0 0 12px; font-size: 12px; color: var(--gp-text-2); line-height: 1.5; }
.dc-side-login-box .dc-side-user-line { margin: 0 0 12px; font-size: 12px; color: var(--gp-text); line-height: 1.5; }
.dc-side-login-box .dc-side-user-line strong { font-size: 13px; color: var(--gp-navy); }
.dc-side-login-actions { display: flex; flex-wrap: wrap; gap: 8px 10px; align-items: center; }
.dc-side-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    border-radius: 3px;
    border: 1px solid var(--gp-navy);
    font-family: inherit;
}
.dc-side-pill--primary {
    background: var(--gp-navy);
    color: #fff !important;
}
.dc-side-pill--primary:hover { background: var(--gp-navy-mid); border-color: var(--gp-navy-mid); color: #fff !important; }
.dc-side-pill--ghost {
    background: var(--gp-surface);
    color: var(--gp-navy) !important;
}
.dc-side-pill--ghost:hover { background: var(--gp-navy-soft); }
.dc-side-rank-list {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 12px;
    counter-reset: srank;
}
.dc-side-rank-list li {
    counter-increment: srank;
    position: relative;
    padding: 8px 0 8px 26px;
    border-bottom: 1px solid var(--gp-line-faint);
    line-height: 1.35;
}
.dc-side-rank-list li:last-child { border-bottom: 0; }
.dc-side-rank-list li::before {
    content: counter(srank);
    position: absolute;
    left: 0;
    top: 8px;
    width: 18px;
    text-align: center;
    font-weight: 800;
    font-size: 11px;
    color: var(--gp-accent);
    font-variant-numeric: tabular-nums;
}
.dc-side-rank-list a {
    color: var(--gp-link);
    font-weight: 600;
    text-decoration: none;
}
.dc-side-rank-list a:hover { text-decoration: underline; }
.dc-side-rank-meta { display: block; font-size: 11px; color: var(--gp-text-3); margin-top: 2px; }
.dc-side-pop-list { list-style: none; margin: 0; padding: 0; font-size: 12px; }
.dc-side-pop-list li {
    padding: 8px 0;
    border-bottom: 1px solid var(--gp-line-faint);
    display: flex;
    gap: 8px;
    align-items: baseline;
    line-height: 1.35;
}
.dc-side-pop-list li:last-child { border-bottom: 0; }
.dc-side-pop-list a {
    flex: 1;
    min-width: 0;
    color: var(--gp-link);
    font-weight: 600;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dc-side-pop-list a:hover { text-decoration: underline; }
.dc-side-pop-meta { font-size: 11px; color: var(--gp-text-3); white-space: nowrap; flex-shrink: 0; }
.dc-side-account-compact {
    margin: 0 0 10px;
    font-size: 12px;
    line-height: 1.5;
    color: var(--gp-text);
}
.dc-side-account-compact strong { color: var(--gp-navy); font-size: 13px; }
.dc-side-inline-actions { font-size: 12px; font-weight: 600; }
.dc-side-inline-actions a { color: var(--gp-link); text-decoration: none; }
.dc-side-inline-actions a:hover { text-decoration: underline; }
.dc-info-box p { margin: 0 0 8px; color: var(--gp-text-2); line-height: 1.5; font-size: 12px; }
.dc-info-box ul { margin: 0; padding-left: 18px; color: var(--gp-text-2); font-size: 12px; }
.dc-info-box li { margin: 0 0 6px; line-height: 1.45; }

@media (max-width: 900px) {
    .dc-body { flex-direction: column; }
    .dc-main { border-right: 0; border-bottom: 1px solid var(--gp-line-faint); }
    .dc-side {
        width: 100%;
        max-width: none;
        flex: none;
        position: static;
        max-height: none;
        overflow-y: visible;
        align-self: stretch;
    }
    .dc-hot-strip-grid { grid-template-columns: repeat(2, 1fr); }
    .dc-nav-cell { justify-content: center; width: 100%; flex-basis: 100%; }
    .dc-topbar__brand { border-right: 0; width: 100%; }
}

/* —— DC-style simple list —— */
.dc-board-head--tight { padding: 12px 14px 10px; }
.dc-board-head--tight .dc-board-title { font-size: 18px; margin: 0 0 6px; }
.dc-board-switch--tight { margin: 0; font-size: 12px; gap: 4px 2px; }
.dc-board-switch--tight .dc-sw-sep { color: var(--gp-text-3); margin: 0 4px; user-select: none; }
.dc-board-switch--tight .dc-board-write {
    display: inline-block;
    margin-left: 6px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    background: var(--gp-navy);
    border: 1px solid var(--gp-navy);
    border-radius: 2px;
    text-decoration: none;
}
.dc-board-switch--tight .dc-board-write:hover { background: var(--gp-navy-mid); color: #fff; }
.dc_tbl { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 13px; }
.dc_tbl th, .dc_tbl td { border-bottom: 1px solid #ccc; padding: 6px 8px; vertical-align: middle; }
.dc_tbl th { background: #f0f0f0; text-align: left; font-size: 12px; font-weight: 800; }
.dc_tbl .w-th { width: 58px; text-align: center; padding: 6px 4px; }
.dc_tbl .dc-thumb-cell { line-height: 0; }
.dc_tbl .dc-thumb-cell img {
    width: 52px;
    height: 40px;
    object-fit: cover;
    display: block;
    border-radius: 2px;
    border: 1px solid #ddd;
}
.dc_tbl .w-no { width: 52px; text-align: right; color: #666; font-variant-numeric: tabular-nums; }
.dc_tbl .w-cc { width: 44px; text-align: right; font-weight: 800; color: #c00; font-variant-numeric: tabular-nums; }
.dc_tbl .w-v { width: 56px; text-align: right; color: #555; font-variant-numeric: tabular-nums; }
.dc_tbl .dc_subj { word-break: break-word; }
.dc_tbl .dc-title-link {
    font-weight: 800;
    font-size: 13px;
    color: var(--gp-text);
    text-decoration: none;
    line-height: 1.45;
    letter-spacing: -0.02em;
}
.dc_tbl .dc-title-link:hover { color: var(--gp-link); text-decoration: underline; }
.dc_row { cursor: pointer; }
.dc_row:hover { background: #fffef0; }
.dc_notice td { background: #faf8f0; font-weight: 600; font-size: 12px; }
.dc_notice .dc_subj { font-weight: 700; }
.dc-gal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 8px 0;
}
.dc-gal-card { border: 1px solid #ccc; background: #fff; font-size: 12px; }
.dc-gal-card a { display: block; color: inherit; text-decoration: none; }
.dc-gal-card a:hover { background: #ffffee; }
.dc-gal-thumb { aspect-ratio: 1; background: #eaeaea; display: flex; align-items: center; justify-content: center; color: #888; font-size: 11px; min-height: 80px; }
.dc-gal-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.dc-gal-cap { padding: 6px 8px; border-top: 1px solid #ddd; line-height: 1.35; display: flex; gap: 6px; justify-content: space-between; align-items: flex-start; }
.dc-gal-cap .n { flex: 1; min-width: 0; word-break: break-word; font-weight: 800; font-size: 13px; letter-spacing: -0.02em; line-height: 1.35; }
.dc-gal-cap .cc { flex-shrink: 0; color: #c00; font-weight: 800; }
@media (max-width: 700px) {
    .dc-gal-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="gall-page">
    <header class="dc-topbar" aria-label="사이트 헤더">
        <div class="dc-topbar__brand">
            <div class="dc-logo">
                <a href="index.php" class="dc-logo-mark" aria-label="부커리 홈" title="부커리">
                    <img class="dc-logo__img" src="<?= h($brandLogoUrl) ?>" alt="부커리" width="380" height="72" decoding="async">
                </a>
                <span class="dc-logo-tagline">집·계약 고민 · 맛집 · 갤러리 커뮤니티</span>
            </div>
        </div>
        <div class="dc-search-cell">
            <form class="dc-search-form" method="get" action="index.php">
                <input type="hidden" name="b" value="<?= h($boardSlug) ?>">
                <input type="search" name="q" class="dc-search-wide" value="<?= h($qVal) ?>" placeholder="검색어를 입력하세요" aria-label="검색">
                <button type="submit">검색</button>
            </form>
        </div>
        <nav class="dc-nav-cell dc-nav-cell--global" aria-label="사이트 메뉴">
            <div class="dc-nav-menus">
                <a href="index.php" class="dc-nav-logo" aria-label="부커리 홈" title="부커리">
                    <img class="dc-nav-logo__img" src="<?= h($brandLogoUrl) ?>" alt="" width="120" height="34" decoding="async">
                </a><span class="dc-nav-sep">|</span>
                <?php foreach ($boards as $slug => $label): ?>
                    <a href="index.php?b=<?= h($slug) ?>"><?= h($label) ?></a>
                <?php endforeach; ?>
                <span class="dc-nav-sep">|</span>
                <a href="checklist.php">체크리스트</a><span class="dc-nav-sep">|</span>
                <a href="mustknow.php">필수정보</a>
            </div>
            <div class="dc-nav-user">
                <?php if ($navGoogleNick !== null): ?>
                    <span class="dc-nav-user-nick" title="계정"><?= h((string) $navGoogleNick) ?></span>
                    <span class="dc-nav-user-sep">|</span>
                    <a href="logout.php" class="dc-account-link">로그아웃</a>
                    <span class="dc-nav-user-sep">|</span>
                <?php else: ?>
                    <a href="google_login.php" class="dc-account-link">로그인</a>
                    <span class="dc-nav-user-sep">|</span>
                <?php endif; ?>
                <a class="dc-nav-write-btn" href="write.php?b=<?= h($boardSlug) ?>">글쓰기</a>
            </div>
        </nav>
    </header>

    <?php adsense_render('below_header', 'dc-ad-leader'); ?>

    <div class="dc-body">
        <div class="dc-main">
            <header class="dc-board-head dc-board-head--tight">
                <h1 class="dc-board-title"><?= h($boardTitle) ?></h1>
                <div class="dc-board-switch dc-board-switch--tight">
                    <?php foreach ($boards as $slug => $label): ?>
                        <a href="index.php?b=<?= h($slug) ?>" class="<?= $slug === $boardSlug ? 'here' : '' ?>"><?= h($label) ?></a>
                    <?php endforeach; ?>
                    <span class="dc-sw-sep">|</span>
                    <a class="dc-board-write" href="write.php?b=<?= h($boardSlug) ?>">글쓰기</a>
                </div>
            </header>

            <?php if ($trending !== []): ?>
            <section class="dc-hot-strip" aria-label="이 게시판 반응 인기 글">
                <h2 class="dc-hot-strip-hd">반응 인기 · 댓글·조회</h2>
                <div class="dc-hot-strip-grid">
                    <?php foreach (array_slice($trending, 0, 8) as $tr): ?>
                        <?php
                        $tid = (int) $tr['id'];
                        $tthumb = $trendingThumbs[$tid] ?? null;
                        ?>
                        <a class="dc-hot-card" href="view.php?id=<?= $tid ?>">
                            <div class="dc-hot-card-im">
                                <?php if ($tthumb): ?>
                                    <img src="<?= h($tthumb) ?>" alt="" loading="lazy" width="160" height="120">
                                <?php else: ?>
                                    <img class="dc-thumb-brand" src="<?= h($brandLogoUrl) ?>" alt="" loading="lazy" width="160" height="120">
                                <?php endif; ?>
                            </div>
                            <span class="dc-hot-card-t"><?= h((string) $tr['title']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($latestStripPosts !== []): ?>
            <section class="dc-latest-strip" aria-label="최신 글 썸네일">
                <h2 class="dc-latest-strip-hd">최신 글 · 썸네일</h2>
                <div class="dc-hot-strip-grid">
                    <?php foreach ($latestStripPosts as $lp): ?>
                        <?php
                        $lid = (int) $lp['id'];
                        $lthumb = $latestStripThumbs[$lid] ?? null;
                        ?>
                        <a class="dc-hot-card" href="view.php?id=<?= $lid ?>">
                            <div class="dc-hot-card-im">
                                <?php if ($lthumb): ?>
                                    <img src="<?= h($lthumb) ?>" alt="" loading="lazy" width="160" height="120">
                                <?php else: ?>
                                    <img class="dc-thumb-brand" src="<?= h($brandLogoUrl) ?>" alt="" loading="lazy" width="160" height="120">
                                <?php endif; ?>
                            </div>
                            <span class="dc-hot-card-t"><?= h((string) $lp['title']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php adsense_render('below_thumbnails', 'dc-ad-after-hot'); ?>

            <div class="gall-list-wrap" id="gall-list-wrap">
                <?php if (!$isGalleryBoard): ?>
                <table class="dc_tbl">
                    <thead>
                        <tr>
                            <th class="w-th" aria-label="썸네일"></th>
                            <th class="w-no">번호</th>
                            <th>제목</th>
                            <th class="w-cc">댓글</th>
                            <th class="w-v">조회</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pinned as $row): ?>
                            <tr class="dc_notice">
                                <td class="w-th dc-thumb-cell"></td>
                                <td class="w-no">공지</td>
                                <td class="dc_subj" colspan="3"><?= h((string) $row['title']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php
                        $postCount = count($posts);
                        $adInsertIndex = -1;
                        if ($postCount >= 8) {
                            $adInsertIndex = 5;
                        } elseif ($postCount >= 6) {
                            $adInsertIndex = 4;
                        } elseif ($postCount >= 4) {
                            $adInsertIndex = 2;
                        }
                        $adInserted = false;
                        $adsOn = adsense_is_enabled();
                        ?>
                        <?php foreach ($posts as $pi => $r): ?>
                            <?php if (!$adInserted && $adsOn && $adInsertIndex >= 0 && $pi === $adInsertIndex): ?>
                                <?php $adInserted = true; ?>
                                <tr class="gall-ad-row">
                                    <td colspan="5" class="dc-tbl-ad-cell">
                                        <?php adsense_render('list_infeed'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php
                            $rid = (int) $r['id'];
                            $rowThumb = $listThumbs[$rid] ?? null;
                            ?>
                            <tr
                                class="dc_row"
                                tabindex="0"
                                onclick="location.href='view.php?id=<?= $rid ?>';"
                                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();location.href='view.php?id=<?= $rid ?>';}"
                            >
                                <td class="w-th dc-thumb-cell" onclick="event.stopPropagation();">
                                    <?php if ($rowThumb): ?>
                                        <a href="view.php?id=<?= $rid ?>" tabindex="-1" aria-hidden="true"><img src="<?= h($rowThumb) ?>" alt="" loading="lazy" width="52" height="40"></a>
                                    <?php endif; ?>
                                </td>
                                <td class="w-no"><?= $rid ?></td>
                                <td class="dc_subj">
                                    <a class="dc-title-link" href="view.php?id=<?= $rid ?>" onclick="event.stopPropagation();"><?= h((string) $r['title']) ?></a>
                                </td>
                                <td class="w-cc"><?= (int) $r['comment_count'] ?></td>
                                <td class="w-v"><?= (int) $r['views'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="dc-gal-grid">
                    <?php
                    $galCount = count($posts);
                    $galAdAt = $galCount >= 8 ? 5 : ($galCount >= 6 ? 4 : -1);
                    $galAdsOn = adsense_is_enabled();
                    foreach ($posts as $gix => $r):
                        if ($galAdsOn && $galAdAt >= 0 && $gix === $galAdAt):
                            ?>
                    <div class="dc-gal-ad-break">
                        <?php adsense_render('list_infeed'); ?>
                    </div>
                            <?php
                        endif;
                        $tid = (int) $r['id'];
                        $th = $thumbByPost[$tid] ?? null;
                        ?>
                    <div class="dc-gal-card">
                        <a href="view.php?id=<?= $tid ?>">
                                <div class="dc-gal-thumb">
                                    <?php if ($th): ?>
                                        <img src="<?= h($th) ?>" alt="" loading="lazy" width="200" height="200">
                                    <?php else: ?>
                                        <img class="dc-thumb-brand" src="<?= h($brandLogoUrl) ?>" alt="" loading="lazy" width="200" height="200">
                                    <?php endif; ?>
                                </div>
                            <div class="dc-gal-cap">
                                <span class="n"><?= h((string) $r['title']) ?></span>
                                <span class="cc"><?= (int) $r['comment_count'] ?></span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="dc-side" aria-label="사이드바">
            <div class="dc-side-blk dc-side-login-box">
                <p class="dc-side-hd">로그인 · 빠른 메뉴</p>
                <?php if ($navGoogleNick !== null): ?>
                    <p class="dc-side-account-compact">
                        <strong title="계정"><?= h((string) $navGoogleNick) ?></strong><br>
                        <span class="dc-side-inline-actions"><a href="logout.php">로그아웃</a> · <a href="write.php?b=<?= h($boardSlug) ?>">글쓰기</a></span>
                    </p>
                <?php else: ?>
                    <p class="dc-side-muted">상단 <strong>로그인</strong> 또는 아래에서 Google 로그인·가입</p>
                    <div class="dc-side-login-actions">
                        <a class="dc-side-pill dc-side-pill--primary" href="google_login.php">로그인</a>
                        <a class="dc-side-pill dc-side-pill--ghost" href="join.php">회원가입</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php adsense_render('side', 'dc-side-blk dc-side-ad-slot adsense'); ?>
            <div class="dc-side-blk">
                <p class="dc-side-hd">조회 인기</p>
                <ul class="dc-side-pop-list">
                    <?php foreach (array_slice($popularByViews, 0, 10) as $tr): ?>
                        <li>
                            <a href="view.php?id=<?= (int) $tr['id'] ?>"><?= h((string) $tr['title']) ?></a>
                            <span class="dc-side-pop-meta">조회 <?= (int) $tr['views'] ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($popularByViews === []): ?>
                        <li><span class="dc-side-pop-meta">글이 없습니다</span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="dc-side-blk">
                <p class="dc-side-hd">댓글 많은 글</p>
                <ul class="dc-side-pop-list">
                    <?php foreach (array_slice($mostCommented, 0, 10) as $tr): ?>
                        <li>
                            <a href="view.php?id=<?= (int) $tr['id'] ?>"><?= h((string) $tr['title']) ?></a>
                            <span class="dc-side-pop-meta">댓글 <?= (int) $tr['comment_count'] ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($mostCommented === []): ?>
                        <li><span class="dc-side-pop-meta">글이 없습니다</span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="dc-side-blk dc-info-box">
                <p class="dc-side-hd">안내</p>
                <p style="margin:0;font-size:12px;line-height:1.4">개인정보·계좌 올리지 마세요.</p>
            </div>
        </aside>
    </div>
</div>

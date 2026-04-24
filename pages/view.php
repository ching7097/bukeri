<?php
declare(strict_types=1);

/** @var int $id */
/** @var array<string, mixed> $post */
/** @var string $sort */
/** @var array<string, mixed> $risk */
/** @var array<int, mixed> $comments */
/** @var array<int, array<string, mixed>> $related */
/** @var array<int, array<string, mixed>> $also */
/** @var bool $agreed */
/** @var string $boardSlug */
/** @var array<int, array<string, mixed>> $postImages */
/** @var string $flashErr */
/** @var string $flashOk */
/** @var string|null $sessNickDisplay */
/** @var bool $googleLogged */
/** @var string $brandLogoUrl */

/**
 * @param array<string, mixed> $row
 */
function viewpg_cmt_text(array $row): string
{
    return (string) ($row['content'] ?? $row['body'] ?? '');
}

$replyThreadCount = 0;
foreach ($comments as $cc) {
    $replyThreadCount += count($cc['replies']);
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function viewpg_render_click_rows(array $rows, string $mode = 'debate'): void
{
    unset($mode);
    foreach ($rows as $r) {
        $cc = (int) $r['comment_count'];
        ?>
        <tr class="dc-v-rec-row" onclick="location.href='view.php?id=<?= (int) $r['id'] ?>';">
            <td class="dc-v-num"><?= (int) $r['id'] ?></td>
            <td class="dc-v-subj"><?= h((string) $r['title']) ?></td>
            <td class="dc-v-cc"><?= $cc ?></td>
            <td class="dc-v-v"><?= (int) $r['views'] ?></td>
        </tr>
        <?php
    }
}
?>
<style>
.dc-v { max-width: 920px; margin: 0 auto; padding: 4px 6px 32px; font-size: 13px; line-height: 1.4; color: #111; }
.dc-v a { color: #00c; }
.dc-v-top { border-bottom: 1px solid #000; padding: 4px 0; margin: 0 0 8px; display: flex; flex-wrap: wrap; gap: 6px 12px; font-size: 12px; font-weight: 800; }
.dc-v-top a { text-decoration: none; color: #00c; }
.dc-v-flash { margin: 0 0 6px; padding: 6px; font-size: 12px; font-weight: 800; }
.dc-v-flash.err { border: 1px solid #c00; background: #fee; color: #900; }
.dc-v-flash.ok { border: 1px solid #080; background: #efe; color: #040; }
.adsense-wrap { margin: 8px 0; text-align: center; min-height: 50px; }
.adsense-wrap--article_after { margin: 12px 0 14px; }
.dc-v-h1 { font-size: 18px; font-weight: 900; margin: 0 0 4px; line-height: 1.25; word-break: break-word; }
.dc-v-meta { font-size: 12px; color: #444; margin: 0 0 6px; display: flex; flex-wrap: wrap; gap: 4px 10px; align-items: center; }
.dc-v-risk { font-size: 12px; border: 1px solid #999; padding: 4px 8px; margin: 0 0 8px; background: #fafafa; line-height: 1.35; }
.dc-v-body { margin: 0 0 8px; white-space: pre-wrap; word-break: break-word; font-size: 13px; line-height: 1.5; }
.dc-v-imgs { margin: 0 0 8px; }
.dc-v-imgs a { display: block; margin-bottom: 6px; }
.dc-v-imgs img { max-width: 100%; width: auto; height: auto; vertical-align: top; border: 1px solid #ccc; }
.dc-v-imgs--brand { padding: 12px 0; text-align: center; background: #f6f8fc; border: 1px solid #e2e8f5; }
.dc-v-imgs-brand { max-width: min(100%, 420px); width: auto; height: auto; vertical-align: top; border: 0; object-fit: contain; }
.dc-v-act { font-size: 12px; margin: 0 0 8px; padding: 4px 0; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; display: flex; flex-wrap: wrap; gap: 8px 14px; align-items: center; }
.dc-v-act form { display: inline; margin: 0; }
.dc-v-btn { font: inherit; font-size: 12px; padding: 2px 8px; cursor: pointer; border: 1px solid #333; background: #f4f4f4; }
.dc-v-btn:hover { background: #e8e8e8; }
.dc-v-cmt-hd { font-weight: 900; font-size: 13px; margin: 10px 0 6px; padding: 4px 0; border-bottom: 1px solid #000; }
.dc-v-sort { font-size: 12px; margin: 0 0 6px; }
.dc-v-sort a { text-decoration: none; }
.dc-v-sort a.on { font-weight: 900; text-decoration: underline; }
.dc-v-cmt-form { margin: 0 0 10px; display: flex; flex-direction: column; gap: 4px; }
.dc-v-cmt-form textarea { width: 100%; box-sizing: border-box; font: inherit; font-size: 13px; padding: 6px; border: 1px solid #888; min-height: 72px; resize: vertical; }
.dc-v-cmt-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.dc-v-cmt-row input[type="text"] { font: inherit; padding: 4px 6px; border: 1px solid #888; width: 160px; }
.dc-v-c-line { border-bottom: 1px solid #e0e0e0; padding: 6px 0; font-size: 13px; }
.dc-v-c-line:last-child { border-bottom: 0; }
.dc-v-c-who { font-weight: 800; color: #29367c; }
.dc-v-rep { margin: 4px 0 0 12px; padding-left: 10px; border-left: 2px solid #ccc; font-size: 12px; }
.dc-v-tools { margin-top: 4px; font-size: 11px; color: #555; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
.dc-v-tools form { display: inline; margin: 0; }
.dc-v-rep-form { margin-top: 6px; padding-top: 6px; border-top: 1px dashed #ddd; }
.dc-v-rep-form textarea { min-height: 52px; font-size: 12px; }
.dc-v-rec { margin-top: 16px; }
.dc-v-rec-hd { font-weight: 900; font-size: 12px; margin: 12px 0 4px; padding: 2px 0; border-bottom: 1px solid #000; }
.dc-v-rec-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.dc-v-rec-tbl th, .dc-v-rec-tbl td { border-bottom: 1px solid #ccc; padding: 3px 6px; text-align: left; }
.dc-v-rec-tbl th { background: #f0f0f0; font-size: 11px; }
.dc-v-rec-row { cursor: pointer; }
.dc-v-rec-row:hover { background: #fffde7; }
.dc-v-num { width: 48px; text-align: right; color: #666; }
.dc-v-subj { word-break: break-word; font-weight: 600; }
.dc-v-cc { width: 40px; text-align: right; font-weight: 800; color: #c00; }
.dc-v-v { width: 48px; text-align: right; color: #555; }
.muted { color: #666; }
</style>

<div class="dc-v">
    <div class="dc-v-top">
        <a href="index.php">홈</a>
        <a href="index.php?b=<?= h($boardSlug) ?>">목록</a>
        <a href="write.php?b=<?= h($boardSlug) ?>">글쓰기</a>
    </div>

    <?php if ($flashErr !== ''): ?>
        <p class="dc-v-flash err" role="alert"><?= h($flashErr) ?></p>
    <?php endif; ?>
    <?php if ($flashOk !== ''): ?>
        <p class="dc-v-flash ok" role="status"><?= h($flashOk) ?></p>
    <?php endif; ?>

    <?php adsense_render('article_before'); ?>

    <article>
        <h1 class="dc-v-h1"><?= h((string) $post['title']) ?></h1>
        <div class="dc-v-meta">
            <?= h((string) $post['author']) ?>
            <span class="muted"><?= h(date('Y-m-d H:i', (int) $post['created_at'])) ?></span>
            <span>조회 <?= (int) $post['views'] ?></span>
            <span>댓글 <?= (int) $post['comment_count'] ?></span>
            <form method="post" style="display:inline;margin:0;" onsubmit="return confirm('신고?');">
                <input type="hidden" name="action" value="report">
                <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                <input type="hidden" name="sort" value="<?= h($sort) ?>">
                <input type="hidden" name="target_type" value="post">
                <input type="hidden" name="target_id" value="<?= (int) $id ?>">
                <button type="submit" class="dc-v-btn" style="font-size:11px;padding:1px 6px">신고</button>
            </form>
        </div>

        <div class="dc-v-risk"><?= h((string) $risk['banner']) ?> · 댓글 <?= (int) $risk['comment_count'] ?></div>

        <div class="dc-v-body"><?= h((string) $post['content']) ?></div>

        <?php if ($postImages !== []): ?>
            <div class="dc-v-imgs" aria-label="이미지">
                <?php foreach ($postImages as $im): ?>
                    <a href="<?= h((string) $im['path']) ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?= h((string) $im['path']) ?>" alt="" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dc-v-imgs dc-v-imgs--brand" aria-label="대표 이미지">
                <img class="dc-v-imgs-brand" src="<?= h($brandLogoUrl) ?>" alt="" loading="lazy" width="420" height="118" decoding="async">
            </div>
        <?php endif; ?>
    </article>

    <?php adsense_render('article_after'); ?>

    <div class="dc-v-act">
        <?php if (!$agreed): ?>
            <form method="post">
                <input type="hidden" name="action" value="post_agree">
                <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                <input type="hidden" name="sort" value="<?= h($sort) ?>">
                <button type="submit" class="dc-v-btn">추천 <?= (int) $post['agrees'] ?></button>
            </form>
        <?php else: ?>
            <span>추천함 <?= (int) $post['agrees'] ?></span>
        <?php endif; ?>
    </div>

    <section id="comments">
        <div class="dc-v-cmt-hd">댓글 <?= (int) $post['comment_count'] ?> · 원글 <?= count($comments) ?> 답글 <?= $replyThreadCount ?></div>

        <p class="dc-v-sort">
            <a href="view.php?id=<?= (int) $id ?>&sort=latest" class="<?= $sort === 'latest' ? 'on' : '' ?>">최신</a>
            /
            <a href="view.php?id=<?= (int) $id ?>&sort=liked" class="<?= $sort === 'liked' ? 'on' : '' ?>">추천</a>
        </p>

        <form id="viewpg-compose" class="viewpg-form dc-v-cmt-form<?= empty($googleLogged) ? ' dc-needs-nick' : '' ?>" method="post">
            <input type="hidden" name="action" value="comment">
            <input type="hidden" name="post_id" value="<?= (int) $id ?>">
            <input type="hidden" name="sort" value="<?= h($sort) ?>">
            <input type="hidden" name="author" value="<?= h((string) ($sessNickDisplay ?? '')) ?>">
            <textarea name="content" placeholder="댓글" required></textarea>
            <div class="dc-v-cmt-row">
                <?php if (empty($googleLogged)): ?>
                    <input type="text" class="dc-cmt-nick-opt" placeholder="닉(비우면 익명)" maxlength="64" autocomplete="off" value="<?= h((string) ($sessNickDisplay ?? '')) ?>">
                <?php endif; ?>
                <button type="submit" class="dc-v-btn">등록</button>
            </div>
        </form>

        <div class="dc-v-c-list">
            <?php foreach ($comments as $c): ?>
                <div class="dc-v-c-line" id="c-<?= (int) $c['id'] ?>">
                    <div>
                        <span class="dc-v-c-who"><?= h((string) $c['author']) ?></span>
                        <?= nl2br(h(viewpg_cmt_text($c))) ?>
                    </div>
                    <?php foreach ($c['replies'] as $r): ?>
                        <div class="dc-v-rep">
                            <span class="dc-v-c-who"><?= h((string) $r['author']) ?></span>
                            <?= nl2br(h(viewpg_cmt_text($r))) ?>
                            <div class="dc-v-tools">
                                <?php if (empty($r['user_agreed'])): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="comment_agree">
                                        <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                                        <input type="hidden" name="sort" value="<?= h($sort) ?>">
                                        <input type="hidden" name="comment_id" value="<?= (int) $r['id'] ?>">
                                        <button type="submit" class="dc-v-btn" style="font-size:11px">↑ <?= (int) ($r['agree_count'] ?? 0) ?></button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">↑<?= (int) ($r['agree_count'] ?? 0) ?></span>
                                <?php endif; ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('신고?');">
                                    <input type="hidden" name="action" value="report">
                                    <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                                    <input type="hidden" name="sort" value="<?= h($sort) ?>">
                                    <input type="hidden" name="target_type" value="comment">
                                    <input type="hidden" name="target_id" value="<?= (int) $r['id'] ?>">
                                    <button type="submit" class="dc-v-btn" style="font-size:11px;padding:1px 6px">신고</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="dc-v-tools">
                        <?php if (empty($c['user_agreed'])): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="comment_agree">
                                <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                                <input type="hidden" name="sort" value="<?= h($sort) ?>">
                                <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="dc-v-btn" style="font-size:11px">↑ <?= (int) ($c['agree_count'] ?? 0) ?></button>
                            </form>
                        <?php else: ?>
                            <span class="muted">↑<?= (int) ($c['agree_count'] ?? 0) ?></span>
                        <?php endif; ?>
                        <form method="post" style="display:inline" onsubmit="return confirm('신고?');">
                            <input type="hidden" name="action" value="report">
                            <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                            <input type="hidden" name="sort" value="<?= h($sort) ?>">
                            <input type="hidden" name="target_type" value="comment">
                            <input type="hidden" name="target_id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="dc-v-btn" style="font-size:11px;padding:1px 6px">신고</button>
                        </form>
                    </div>

                    <form class="viewpg-form dc-v-cmt-form dc-v-rep-form<?= empty($googleLogged) ? ' dc-needs-nick' : '' ?>" method="post">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="post_id" value="<?= (int) $id ?>">
                        <input type="hidden" name="sort" value="<?= h($sort) ?>">
                        <input type="hidden" name="parent_id" value="<?= (int) $c['id'] ?>">
                        <input type="hidden" name="author" value="<?= h((string) ($sessNickDisplay ?? '')) ?>">
                        <textarea name="content" placeholder="답글" required></textarea>
                        <div class="dc-v-cmt-row">
                            <button type="submit" class="dc-v-btn">답글</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dc-v-rec" id="after-cmt">
        <div class="dc-v-rec-hd">관련 글</div>
        <table class="dc-v-rec-tbl">
            <thead>
                <tr>
                    <th class="dc-v-num">번호</th>
                    <th>제목</th>
                    <th class="dc-v-cc">댓글</th>
                    <th class="dc-v-v">조회</th>
                </tr>
            </thead>
            <tbody>
                <?php viewpg_render_click_rows($related, 'debate'); ?>
            </tbody>
        </table>

        <div class="dc-v-rec-hd">함께 본 글</div>
        <table class="dc-v-rec-tbl">
            <thead>
                <tr>
                    <th class="dc-v-num">번호</th>
                    <th>제목</th>
                    <th class="dc-v-cc">댓글</th>
                    <th class="dc-v-v">조회</th>
                </tr>
            </thead>
            <tbody>
                <?php viewpg_render_click_rows($also, 'also'); ?>
            </tbody>
        </table>
    </section>
</div>
<?php if (empty($googleLogged)): ?>
<script>
(function () {
    function fillNick(form) {
        var hid = form.querySelector('input[name="author"]');
        var opt = form.querySelector('.dc-cmt-nick-opt');
        var v = opt && opt.value ? opt.value.trim() : '';
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
    }
    document.querySelectorAll('form.dc-needs-nick').forEach(function (form) {
        form.addEventListener('submit', function () { fillNick(form); });
    });
})();
</script>
<?php endif; ?>

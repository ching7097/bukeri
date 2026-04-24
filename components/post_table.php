<?php
declare(strict_types=1);

/**
 * @param array<int, array<string, mixed>> $rows
 */
function render_post_table(array $rows): void
{
    ?>
    <table class="board board--list">
        <thead>
            <tr>
                <th class="col-num">번호</th>
                <th class="col-subj">제목</th>
                <th class="col-author">작성자</th>
                <th class="col-time">시간</th>
                <th class="col-view">조회</th>
                <th class="col-cc">댓글</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr class="row-go" onclick="location.href='view.php?id=<?= (int) $r['id'] ?>';">
                    <td class="col-num"><?= (int) $r['id'] ?></td>
                    <td class="col-subj"><?= h((string) $r['title']) ?></td>
                    <td class="col-author"><?= h((string) $r['author']) ?></td>
                    <td class="col-time"><?= h(date('m-d H:i', (int) $r['created_at'])) ?></td>
                    <td class="col-view"><?= (int) $r['views'] ?></td>
                    <td class="col-cc"><?= (int) $r['comment_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

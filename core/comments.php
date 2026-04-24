<?php
declare(strict_types=1);

/**
 * @return array<int, array<string, mixed>>
 */
function comments_for_post(int $postId, string $sort, string $voterKey): array
{
    $pid = (int) $postId;
    $st = db()->query(
        'SELECT * FROM comments WHERE post_id = ' . $pid . ' AND parent_id IS NULL ORDER BY created_at ASC'
    );
    $tops = $st->fetchAll(PDO::FETCH_ASSOC);
    $st2 = db()->prepare('SELECT * FROM comments WHERE post_id = ? AND parent_id = ? ORDER BY created_at ASC');
    foreach ($tops as &$t) {
        $cid = (int) $t['id'];
        $t['agree_count'] = comment_agree_count($cid);
        $t['user_agreed'] = comment_user_has_agreed($cid, $voterKey);
        $st2->execute([$postId, $cid]);
        $t['replies'] = $st2->fetchAll(PDO::FETCH_ASSOC);
        foreach ($t['replies'] as &$r) {
            $rid = (int) $r['id'];
            $r['agree_count'] = comment_agree_count($rid);
            $r['user_agreed'] = comment_user_has_agreed($rid, $voterKey);
        }
        unset($r);
    }
    unset($t);

    if ($sort === 'liked') {
        usort($tops, static function (array $a, array $b): int {
            return ((int) ($b['agree_count'] ?? 0)) <=> ((int) ($a['agree_count'] ?? 0));
        });
    }

    return $tops;
}

function comment_add(int $postId, ?int $parentId, string $author, string $content): void
{
    $db = db();
    if ($parentId !== null) {
        $chk = $db->prepare('SELECT id, parent_id FROM comments WHERE id = ? AND post_id = ? LIMIT 1');
        $chk->execute([$parentId, $postId]);
        $p = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$p || $p['parent_id'] !== null) {
            $parentId = null;
        }
    }
    $st = $db->prepare('INSERT INTO comments (post_id, parent_id, author, content, created_at) VALUES (?,?,?,?,?)');
    $st->execute([$postId, $parentId, $author, $content, time()]);
    $u = $db->prepare('UPDATE posts SET comment_count = (SELECT COUNT(*) FROM comments WHERE post_id = ?) WHERE id = ?');
    $u->execute([$postId, $postId]);
}

/**
 * @return list<string>
 */
function comments_bodies_for_post(int $postId): array
{
    $st = db()->prepare('SELECT content FROM comments WHERE post_id = ?');
    $st->execute([$postId]);
    return $st->fetchAll(PDO::FETCH_COLUMN, 0);
}

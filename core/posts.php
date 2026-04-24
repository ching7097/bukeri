<?php
declare(strict_types=1);

/** 개념글: 추천(agrees) 이 값 이상 */
const POST_CONCEPT_MIN_AGREES = 3;

/**
 * @param array<string, string> $valid
 */
function board_normalize(string $slug, array $valid): string
{
    $legacy = [
        'realestate' => 'contract',
        'jeonse' => 'rent',
        'wolse' => 'rent',
        'scam' => 'free',
    ];
    if (isset($legacy[$slug])) {
        $slug = $legacy[$slug];
    }
    if (isset($valid[$slug])) {
        return $slug;
    }
    foreach ($valid as $k => $_) {
        return $k;
    }
    return 'house';
}

function posts_trending_in_board(string $gallery, int $limit): array
{
    $limit = max(1, min(50, $limit));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? ORDER BY comment_count DESC, views DESC LIMIT ' . (int) $limit
    );
    $st->execute([$gallery]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** 댓글 수 우선(동률이면 최신 글) — 사이드 「댓글 많은 글」용 */
function posts_most_commented_in_board(string $gallery, int $limit): array
{
    $limit = max(1, min(50, $limit));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? ORDER BY comment_count DESC, id DESC LIMIT ' . (int) $limit
    );
    $st->execute([$gallery]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** 조회 수 위주 인기 — 사이드 「조회 인기」용 */
function posts_hot_by_views_in_board(string $gallery, int $limit): array
{
    $limit = max(1, min(50, $limit));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? ORDER BY views DESC, comment_count DESC LIMIT ' . (int) $limit
    );
    $st->execute([$gallery]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function posts_latest_in_board(string $gallery, int $limit): array
{
    $limit = max(1, min(80, $limit));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? ORDER BY id DESC LIMIT ' . (int) $limit
    );
    $st->execute([$gallery]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 게시판 내 제목·본문 LIKE 검색 (빈 검색어는 최신 목록과 동일).
 *
 * @return array<int, array<string, mixed>>
 */
function posts_search_in_board(string $gallery, string $query, int $limit): array
{
    $query = trim($query);
    if ($query === '') {
        return posts_latest_in_board($gallery, $limit);
    }
    $limit = max(1, min(80, $limit));
    $like = '%' . addcslashes($query, '%_\\') . '%';
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? AND (title LIKE ? OR content LIKE ?) ORDER BY id DESC LIMIT ' . (int) $limit
    );
    $st->execute([$gallery, $like, $like]);

    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param array<int, int> $excludeIds
 * @return array<int, array<string, mixed>>
 */
function posts_latest_in_board_excluding(string $gallery, array $excludeIds, int $limit): array
{
    $limit = max(1, min(80, $limit));
    $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
    $excludeIds = array_filter($excludeIds, static fn (int $i) => $i > 0);
    if ($excludeIds === []) {
        return posts_latest_in_board($gallery, $limit);
    }
    $place = implode(',', array_fill(0, count($excludeIds), '?'));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery = ? AND id NOT IN (' . $place . ') ORDER BY id DESC LIMIT ' . (int) $limit
    );
    $st->execute(array_merge([$gallery], array_values($excludeIds)));
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function post_find(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function post_increment_views(int $id): void
{
    $st = db()->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
    $st->execute([$id]);
}

function posts_trending(int $limit): array
{
    $limit = max(1, min(50, $limit));
    $sql = 'SELECT * FROM posts ORDER BY comment_count DESC, views DESC LIMIT ' . (int) $limit;
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function posts_latest(int $limit): array
{
    $limit = max(1, min(80, $limit));
    $sql = 'SELECT * FROM posts ORDER BY id DESC LIMIT ' . (int) $limit;
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Latest posts for home main list, excluding IDs already shown in trending (no duplicate rows).
 *
 * @param array<int, int> $excludeIds
 * @return array<int, array<string, mixed>>
 */
function posts_latest_excluding_ids(array $excludeIds, int $limit): array
{
    $limit = max(1, min(80, $limit));
    $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
    $excludeIds = array_filter($excludeIds, static fn (int $i) => $i > 0);
    if ($excludeIds === []) {
        return posts_latest($limit);
    }
    $place = implode(',', array_fill(0, count($excludeIds), '?'));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE id NOT IN (' . $place . ') ORDER BY id DESC LIMIT ' . (int) $limit
    );
    $st->execute(array_values($excludeIds));
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function posts_latest_fill(int $excludeId, array $alreadyIds, int $need): array
{
    if ($need <= 0) {
        return [];
    }
    $alreadyIds = array_values(array_unique(array_map('intval', $alreadyIds)));
    $params = [$excludeId];
    $sql = 'SELECT * FROM posts WHERE id != ?';
    if ($alreadyIds !== []) {
        $place = implode(',', array_fill(0, count($alreadyIds), '?'));
        $sql .= ' AND id NOT IN (' . $place . ')';
        $params = array_merge($params, $alreadyIds);
    }
    $sql .= ' ORDER BY id DESC LIMIT ' . (int) $need;
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function post_create(?int $userId, string $category, string $title, string $content, string $author, string $gallery): int
{
    $st = db()->prepare(
        'INSERT INTO posts (user_id, category, gallery, title, content, author, created_at, views, agrees, comment_count) VALUES (?,?,?,?,?,?,?,0,0,0)'
    );
    $st->execute([$userId, $category, $gallery, $title, $content, $author, time()]);
    return (int) db()->lastInsertId();
}

/**
 * @param list<string> $slugs
 * @return array<int, array<string, mixed>>
 */
function posts_hot_in_galleries(array $slugs, int $limit): array
{
    $slugs = array_values(array_unique(array_map('strval', $slugs)));
    $slugs = array_filter($slugs, static fn (string $s) => $s !== '');
    if ($slugs === []) {
        return [];
    }
    $limit = max(1, min(50, $limit));
    $place = implode(',', array_fill(0, count($slugs), '?'));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery IN (' . $place . ') ORDER BY comment_count DESC, views DESC LIMIT ' . (int) $limit
    );
    $st->execute(array_values($slugs));
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param list<string> $slugs
 * @return array<int, array<string, mixed>>
 */
function posts_latest_in_galleries(array $slugs, int $limit): array
{
    $slugs = array_values(array_unique(array_map('strval', $slugs)));
    $slugs = array_filter($slugs, static fn (string $s) => $s !== '');
    if ($slugs === []) {
        return [];
    }
    $limit = max(1, min(80, $limit));
    $place = implode(',', array_fill(0, count($slugs), '?'));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE gallery IN (' . $place . ') ORDER BY id DESC LIMIT ' . (int) $limit
    );
    $st->execute(array_values($slugs));
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param list<int> $ids
 * @return array<int, string|null> post_id => first image public path
 */
function posts_first_image_paths(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $ids = array_filter($ids, static fn (int $i) => $i > 0);
    if ($ids === []) {
        return [];
    }
    $in = implode(',', $ids);
    $sql = 'SELECT p.post_id, p.path FROM post_images p INNER JOIN (
      SELECT post_id, MIN(id) AS mid FROM post_images WHERE post_id IN (' . $in . ') GROUP BY post_id
    ) t ON p.post_id = t.post_id AND p.id = t.mid';
    $rows = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
        $out[(int) $r['post_id']] = (string) $r['path'];
    }
    return $out;
}

function comment_agree_count(int $commentId): int
{
    $st = db()->prepare(
        "SELECT COUNT(*) FROM votes WHERE target_type = 'comment' AND target_id = ? AND value = 1"
    );
    $st->execute([$commentId]);
    return (int) $st->fetchColumn();
}

function comment_user_has_agreed(int $commentId, string $voterKey): bool
{
    $st = db()->prepare(
        "SELECT 1 FROM votes WHERE target_type = 'comment' AND target_id = ? AND voter_key = ? AND value = 1 LIMIT 1"
    );
    $st->execute([$commentId, $voterKey]);
    return (bool) $st->fetchColumn();
}

function comment_try_agree(int $commentId, string $voterKey): bool
{
    $db = db();
    try {
        $st = $db->prepare(
            'INSERT INTO votes (voter_key, target_type, target_id, value, created_at) VALUES (?,?,?,?,?)'
        );
        $st->execute([$voterKey, 'comment', $commentId, 1, time()]);
    } catch (Throwable $e) {
        return false;
    }
    return true;
}

function post_user_has_agreed(int $postId, string $voterKey): bool
{
    $st = db()->prepare(
        'SELECT 1 FROM votes WHERE target_type = \'post\' AND target_id = ? AND voter_key = ? AND value = 1 LIMIT 1'
    );
    $st->execute([$postId, $voterKey]);
    return (bool) $st->fetchColumn();
}

function post_try_agree(int $postId, string $voterKey): bool
{
    $db = db();
    try {
        $st = $db->prepare(
            'INSERT INTO votes (voter_key, target_type, target_id, value, created_at) VALUES (?,?,?,?,?)'
        );
        $st->execute([$voterKey, 'post', $postId, 1, time()]);
    } catch (Throwable $e) {
        return false;
    }
    $u = $db->prepare('UPDATE posts SET agrees = agrees + 1 WHERE id = ?');
    $u->execute([$postId]);
    return true;
}

function posts_by_ids(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $ids = array_filter($ids, static fn (int $i) => $i > 0);
    if ($ids === []) {
        return [];
    }
    $in = implode(',', $ids);
    $sql = 'SELECT * FROM posts WHERE id IN (' . $in . ')';
    $rows = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $byId = [];
    foreach ($rows as $r) {
        $byId[(int) $r['id']] = $r;
    }
    $ordered = [];
    foreach ($ids as $id) {
        if (isset($byId[$id])) {
            $ordered[] = $byId[$id];
        }
    }
    return $ordered;
}

function posts_related(string $category, int $excludeId, int $limit): array
{
    $limit = max(1, min(20, $limit));
    $st = db()->prepare(
        'SELECT * FROM posts WHERE category = ? AND id != ? ORDER BY comment_count DESC, id DESC LIMIT ' . $limit
    );
    $st->execute([$category, $excludeId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function posts_fill_by_category(string $category, int $excludeId, int $need, array $alreadyIds): array
{
    if ($need <= 0) {
        return [];
    }
    $alreadyIds = array_values(array_unique(array_map('intval', $alreadyIds)));
    $params = [$category, $excludeId];
    $sql = 'SELECT * FROM posts WHERE category = ? AND id != ?';
    if ($alreadyIds !== []) {
        $place = implode(',', array_fill(0, count($alreadyIds), '?'));
        $sql .= ' AND id NOT IN (' . $place . ')';
        $params = array_merge($params, $alreadyIds);
    }
    $sql .= ' ORDER BY views DESC, id DESC LIMIT ' . (int) $need;
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

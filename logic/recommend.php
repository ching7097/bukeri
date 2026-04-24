<?php
declare(strict_types=1);

/**
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function recommend_pad(array $rows, int $excludeId, int $limit): array
{
    $rows = array_values($rows);
    if (count($rows) >= $limit) {
        return array_slice($rows, 0, $limit);
    }
    $have = array_map(static fn (array $r) => (int) $r['id'], $rows);
    $need = $limit - count($rows);
    $more = posts_latest_fill($excludeId, $have, $need);
    return array_merge($rows, $more);
}

function recommend_related(array $post, int $limit = 5): array
{
    $id = (int) $post['id'];
    $cat = (string) $post['category'];
    $rows = posts_related($cat, $id, $limit);
    return recommend_pad($rows, $id, $limit);
}

function recommend_also_viewed(array $post, array $sessionViewedIds, int $limit = 5): array
{
    $id = (int) $post['id'];
    $cat = (string) $post['category'];
    $ids = [];
    foreach ($sessionViewedIds as $vid) {
        $vid = (int) $vid;
        if ($vid > 0 && $vid !== $id) {
            $ids[] = $vid;
        }
    }
    $ids = array_values(array_unique($ids));
    $out = [];
    if ($ids !== []) {
        $out = posts_by_ids($ids);
        $out = array_values(array_filter($out, static fn (array $r) => (int) $r['id'] !== $id));
    }
    if (count($out) < $limit) {
        $have = array_map(static fn (array $r) => (int) $r['id'], $out);
        $need = $limit - count($out);
        $fill = posts_fill_by_category($cat, $id, $need, $have);
        $out = array_merge($out, $fill);
    }
    return recommend_pad($out, $id, $limit);
}

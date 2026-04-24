<?php
declare(strict_types=1);

function report_add(string $targetType, int $targetId, string $reporterKey, string $reason): void
{
    $tt = $targetType === 'comment' ? 'comment' : 'post';
    $tid = max(0, $targetId);
    if ($tid <= 0) {
        return;
    }
    $reason = trim($reason);
    if (function_exists('mb_substr')) {
        $reason = mb_substr($reason, 0, 500, 'UTF-8');
    } elseif (strlen($reason) > 500) {
        $reason = substr($reason, 0, 500);
    }
    $st = db()->prepare(
        'INSERT INTO reports (target_type, target_id, reporter_key, reason, created_at) VALUES (?,?,?,?,?)'
    );
    $st->execute([$tt, $tid, substr($reporterKey, 0, 64), $reason, time()]);
}

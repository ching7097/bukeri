<?php
declare(strict_types=1);

function risk_negative_keywords(): array
{
    return [
        '사기', '먹튀', '연락두절', '허위', '가압류', '깡통', '도주', '브로커', '대포통장',
        '피해', '경찰', '고소', '특경', '몰래', '잠수', '협박',
    ];
}

function risk_count_keyword_hits(string $text): int
{
    $n = 0;
    foreach (risk_negative_keywords() as $w) {
        if ($w !== '' && mb_strpos($text, $w) !== false) {
            $n++;
        }
    }
    return $n;
}

function risk_score_from_texts(array $bodies, int $commentCount, int $postAgrees): array
{
    $blob = implode("\n", $bodies);
    $kwHits = risk_count_keyword_hits($blob);
    $score = ($kwHits * 2.2) + ($commentCount * 0.35) + ($postAgrees * 0.15);
    return ['kw' => $kwHits, 'comments' => $commentCount, 'agrees' => $postAgrees, 'score' => $score];
}

function risk_level(array $metrics): string
{
    $kw = (int) $metrics['kw'];
    $c = (int) $metrics['comments'];
    $s = (float) $metrics['score'];

    if ($kw >= 12 || $c >= 35 || $s >= 42) {
        return 'high';
    }
    if ($kw >= 5 || $c >= 15 || $s >= 22) {
        return 'medium';
    }
    return 'low';
}

function risk_label_ko(string $level): string
{
    return match ($level) {
        'high' => '위험 높음',
        'medium' => '주의',
        default => '낮음',
    };
}

function risk_scam_judgment_markers(): array
{
    return [
        '사기', '먹튀', '깡통', '가압류', '특경', '고소', '신고', '피해', '위험',
        '빠지', '절대', '안 맞', '안돼', '조심', '잠수', '허위', '대포',
    ];
}

function risk_count_scam_judgment_comments(int $postId): int
{
    $bodies = comments_bodies_for_post($postId);
    $markers = risk_scam_judgment_markers();
    $n = 0;
    foreach ($bodies as $b) {
        foreach ($markers as $m) {
            if ($m !== '' && mb_strpos($b, $m) !== false) {
                $n++;
                break;
            }
        }
    }
    return $n;
}

function risk_banner_text(string $level, int $totalComments, int $scamJudges): string
{
    $head = match ($level) {
        'high' => '⚠️ 위험도 높음',
        'medium' => '⚠️ 위험도 보통',
        default => '위험도 낮음',
    };
    if ($totalComments <= 0) {
        return $head . ' (아직 댓글이 없음)';
    }
    return $head . ' (댓글 ' . $totalComments . '개 · 위험·사기 언급 ' . $scamJudges . '건)';
}

function risk_evaluate_post(array $post): array
{
    $pid = (int) $post['id'];
    $bodies = comments_bodies_for_post($pid);
    $cc = count($bodies);
    $agrees = (int) $post['agrees'];
    $m = risk_score_from_texts($bodies, $cc, $agrees);
    $lvl = risk_level($m);
    $scam = risk_count_scam_judgment_comments($pid);
    return [
        'level' => $lvl,
        'label' => risk_label_ko($lvl),
        'kw_hits' => $m['kw'],
        'comment_count' => $cc,
        'agrees' => $agrees,
        'score' => $m['score'],
        'scam_judges' => $scam,
        'banner' => risk_banner_text($lvl, $cc, $scam),
    ];
}

<?php
declare(strict_types=1);

/**
 * Single runtime DB singleton: db() only.
 * A short-lived $temp PDO (no dbname) runs once per request before db() to ensure the database exists.
 * Tables are created inline (no schema file, no config/database.php).
 */
function ensure_community_database_exists(): void
{
    $temp = new PDO(
        'mysql:host=127.0.0.1;port=3307;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    $temp->exec(
        'CREATE DATABASE IF NOT EXISTS community CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'
    );
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3307;dbname=community;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    return $pdo;
}

function create_community_tables(PDO $db): void
{
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  google_id VARCHAR(128) NULL DEFAULT NULL,
  nickname VARCHAR(64) NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_google_id (google_id),
  UNIQUE KEY uq_users_nickname (nickname),
  KEY idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  category VARCHAR(32) NOT NULL,
  gallery VARCHAR(32) NOT NULL DEFAULT 'house',
  title VARCHAR(500) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  author VARCHAR(64) NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  agrees INT UNSIGNED NOT NULL DEFAULT 0,
  comment_count INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_posts_cat (category),
  KEY idx_posts_gallery (gallery),
  KEY idx_posts_created (created_at),
  KEY idx_posts_hot (comment_count, views),
  CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  author VARCHAR(64) NOT NULL,
  content TEXT NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_comments_post (post_id),
  KEY idx_comments_parent (parent_id),
  CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS votes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  voter_key VARCHAR(64) NOT NULL,
  target_type ENUM('post', 'comment') NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  value TINYINT NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vote (voter_key, target_type, target_id),
  KEY idx_votes_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS post_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_post_images_post (post_id),
  CONSTRAINT fk_post_images_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS reports (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_type ENUM('post','comment') NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  reporter_key VARCHAR(64) NOT NULL,
  reason VARCHAR(500) NOT NULL DEFAULT '',
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_reports_target (target_type, target_id),
  KEY idx_reports_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
}

function ensure_comments_table_shape(PDO $db): void
{
    $chk = $db->query("SHOW TABLES LIKE 'comments'");
    if ($chk === false || $chk->rowCount() === 0) {
        return;
    }
    $fields = [];
    foreach ($db->query('SHOW COLUMNS FROM comments') as $row) {
        $fields[(string) $row['Field']] = true;
    }
    if (isset($fields['body']) && !isset($fields['content'])) {
        $db->exec('ALTER TABLE comments CHANGE COLUMN body content TEXT NOT NULL');
        $fields['content'] = true;
        unset($fields['body']);
    }
    $drops = [];
    if (isset($fields['agree'])) {
        $drops[] = 'DROP COLUMN agree';
    }
    if (isset($fields['disagree'])) {
        $drops[] = 'DROP COLUMN disagree';
    }
    if ($drops !== []) {
        $db->exec('ALTER TABLE comments ' . implode(', ', $drops));
    }
}

function ensure_posts_gallery_column(PDO $db): void
{
    $chk = $db->query("SHOW COLUMNS FROM posts LIKE 'gallery'");
    if ($chk !== false && $chk->rowCount() > 0) {
        return;
    }
    $db->exec(
        "ALTER TABLE posts ADD COLUMN gallery VARCHAR(32) NOT NULL DEFAULT 'house' AFTER category"
    );
    $db->exec('ALTER TABLE posts ADD KEY idx_posts_gallery (gallery)');
}

function migrate_legacy_gallery_slugs(PDO $db): void
{
    $map = [
        'realestate' => 'contract',
        'jeonse' => 'rent',
        'wolse' => 'rent',
        'scam' => 'free',
    ];
    foreach ($map as $from => $to) {
        $st = $db->prepare('UPDATE posts SET gallery = ? WHERE gallery = ?');
        $st->execute([$to, $from]);
    }
}

function ensure_users_google_id_column(PDO $db): void
{
    $chk = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($chk !== false && $chk->rowCount() > 0) {
        return;
    }
    $db->exec('ALTER TABLE users ADD COLUMN google_id VARCHAR(128) NULL DEFAULT NULL AFTER id');
    $idx = $db->query("SHOW INDEX FROM users WHERE Key_name = 'uq_users_google_id'");
    if ($idx === false || $idx->rowCount() === 0) {
        try {
            $db->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_google_id (google_id)');
        } catch (Throwable $e) {
            // ignore duplicate key name
        }
    }
}

function ensure_users_nickname_unique(PDO $db): void
{
    $chk = $db->query("SHOW INDEX FROM users WHERE Key_name = 'uq_users_nickname'");
    if ($chk !== false && $chk->rowCount() > 0) {
        return;
    }
    try {
        $db->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_nickname (nickname)');
    } catch (Throwable $e) {
        $db->exec(
            'DELETE u1 FROM users u1 INNER JOIN users u2 ON u1.nickname = u2.nickname AND u1.id > u2.id'
        );
        $db->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_nickname (nickname)');
    }
}

function init_db(): void
{
    ensure_community_database_exists();
    $db = db();
    create_community_tables($db);
    ensure_posts_gallery_column($db);
    ensure_comments_table_shape($db);
    ensure_users_nickname_unique($db);
    ensure_users_google_id_column($db);
    migrate_legacy_gallery_slugs($db);

    $n = (int) $db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    if ($n === 0) {
        seed_database($db);
    }
}

function seed_database(PDO $db): void
{
    $now = time();
    $categories = ['일반'];
    $titles = [
        '[서울] 전세 계약 직전인데 집주인이 갑자기 보증금 더 내라고 합니다',
        '중개사 두 곳에서 같은 매물 가격이 다릅니다. 믿어도 되나요?',
        '[대구] 전입 전 열쇠만 달라는 집주인… 지금 나가야 하나요?',
        '등기부에 근저당이 큰데 깡통전세 가능성 어떻게 보시나요?',
        '분양 문자 온 계좌가 개인 통장입니다. 그냥 무시해도 되죠?',
        '전세보증보험 거절됐다는 연락 왔습니다. 위험 신호일까요?',
        '허위매물 같아서 현장 갔더니 건물이 다른 동네였습니다.',
        '집주인이 제3자 통장에 전세금 넣으래요. 위험한가요?',
        '전세 만기 한 달 남았는데 집주인 잠수 중입니다. 바로 신고해야 하나요?',
        '중개 수수료를 현금으로만 받겠다고 합니다. 거절하면 계약 깨지나요?',
        '[부산] 숨은 맛집 홍보 — 자갈치 근처 백반집 다녀왔습니다',
        '나만의 맛집 리스트(분식·국밥 위주) 공유합니다',
        '착한 가게 추천: 동네 과일 가게 사장님이 진짜 친절하셨어요',
        '커스텀 키보드 첫 작품 자랑합니다 (사진 많음)',
        '레고 타워 브릿지 한 달 만에 완성했습니다',
        '오늘 찍은 하늘 사진 올려봅니다',
    ];

    $galleries = [
        'house', 'contract', 'rent', 'free', 'house', 'contract', 'rent', 'free', 'house', 'contract',
        'food_pr', 'food_list', 'good_store', 'gallery_work', 'gallery_work', 'gallery_img',
    ];
    $stmt = $db->prepare(
        'INSERT INTO posts (user_id, category, gallery, title, content, author, created_at, views, agrees, comment_count) VALUES (?,?,?,?,?,?,?,?,?,?)'
    );
    $postIds = [];
    foreach ($titles as $i => $title) {
        $cat = $categories[$i % count($categories)];
        $gal = $galleries[$i] ?? 'free';
        $author = '익명' . (200 + $i);
        $created = $now - ($i * 7200);
        $views = 80 + $i * 37;
        $agrees = $i * 3;
        $content = $title . "\n\n"
            . "계약·입금·중개 연락 흐름을 적었습니다. 댓글로 의견 부탁드립니다.\n"
            . '개인정보(계좌·주민번호)는 올리지 않았습니다.';
        $stmt->execute([null, $cat, $gal, $title, $content, $author, $created, $views, $agrees, 0]);
        $postIds[] = (int) $db->lastInsertId();
    }

    $bodies = [
        '지금 상황만 보면 사기 가능성이 있습니다. 선금·추가 보증금은 멈추고 문자·통화 기록 남기세요.',
        '등기부등본이랑 근저당 권자 확인부터 다시 하세요. 중개 말만 믿지 마세요.',
        '전입·보증보험 전에 돈 더 내라는 건 빨간 깃발입니다. 계약서 특약으로 막으세요.',
        '깡통이면 대출 한도 대비 집값 역산이 안 맞을 때 많습니다. 은행에 한 번 더 물어보세요.',
        '개인 통장으로 분양비 넣으라는 건 거의 사기 패턴입니다. 공식 계좌인지 확인하세요.',
        '보험 거절 사유를 서면으로 달라고 하고, 중개·집주인 답변도 남기세요.',
        '허위매물이면 국토부·소비자원 루트 알아두세요. 증거 사진이 중요합니다.',
        '제3자 통장은 특히 위험합니다. 집주인 명의 계좌로만 하라고 하세요.',
        '연락두절이면 특경·경찰 상담 번호부터 저장하고, 보증금 회수 루트를 동시에 잡으세요.',
        '수수료 현금 요구는 거절해도 됩니다. 분할·계좌 이체로만 하라고 하세요.',
        '저도 비슷한 수법 당했는데 결국 분쟁이었습니다. 일단 멈추고 검증부터.',
        '가압류 흔적 있으면 그냥 다른 매물 보는 게 심정 건강에 낫습니다.',
        '전세권 설정 거부하면 왜 거부하는지 녹취·문자로 남기세요.',
        '중개 등록번호 협회에서 조회해보세요. 안 나오면 그 자체가 신호입니다.',
        '“지금 안 하면 나간다” 압박은 일부러 불안하게 만드는 겁니다. 시간 벌고 확인하세요.',
        '외국인 집주인이면 통역·확인 절차가 더 필요합니다. 서명만으로 넘어가지 마세요.',
        '법무사 비용을 중개가 뻥튀기하는 경우도 있습니다. 따로 견적 받아보세요.',
        '전세금 일부만 계약서에 쓰는 건 나중에 입증 지옥입니다. 정확히 맞추세요.',
        '댓글만 보고 결정하지 말고 전문가 상담 병행하세요. 여긴 참고용입니다.',
        '사기라고 단정은 못 해도, 위험 신호는 여러 개 겹쳤습니다. 빠르게 증거 확보하세요.',
    ];

    $cstmt = $db->prepare(
        'INSERT INTO comments (post_id, parent_id, author, content, created_at) VALUES (?,?,?,?,?)'
    );
    $bi = 0;
    $firstCid = null;
    foreach ($postIds as $pi => $pid) {
        for ($k = 0; $k < 2; $k++) {
            $body = $bodies[$bi % count($bodies)];
            $cstmt->execute([
                $pid,
                null,
                '익명' . (100 + $bi),
                $body,
                $now - 60 * ($bi + 1),
            ]);
            if ($pi === 0 && $k === 0) {
                $firstCid = (int) $db->lastInsertId();
            }
            $bi++;
        }
    }
    if ($firstCid > 0 && $postIds !== []) {
        $cstmt->execute([
            $postIds[0],
            $firstCid,
            '익명456',
            '저도 비슷해요. 일단 서류부터 다시 확인해 보세요.',
            $now - 120,
        ]);
    }

    $db->exec('UPDATE posts SET comment_count = (SELECT COUNT(*) FROM comments c WHERE c.post_id = posts.id)');
}

-- MySQL schema for 부커리 community
-- Charset: utf8mb4 for full Unicode (Korean)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  google_id VARCHAR(128) NULL DEFAULT NULL,
  nickname VARCHAR(64) NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_google_id (google_id),
  UNIQUE KEY uq_users_nickname (nickname),
  KEY idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_post_images_post (post_id),
  CONSTRAINT fk_post_images_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

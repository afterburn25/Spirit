CREATE TABLE IF NOT EXISTS `{{prefix}}content` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 kind VARCHAR(24) NOT NULL,
 title VARCHAR(180) NOT NULL,
 draft_slug VARCHAR(90) NOT NULL,
 draft_json MEDIUMTEXT NOT NULL,
 published_slug VARCHAR(90) NULL,
 published_json MEDIUMTEXT NULL,
 revision INT UNSIGNED NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 published_at DATETIME NULL,
 updated_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL,
 updated_by BIGINT UNSIGNED NOT NULL,
 deleted_at DATETIME NULL,
 UNIQUE KEY content_draft_slug (kind,draft_slug),
 UNIQUE KEY content_public_slug (kind,published_slug),
 INDEX content_listing (kind,deleted_at,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}media` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 storage_name VARCHAR(80) NOT NULL UNIQUE,
 original_name VARCHAR(180) NOT NULL,
 kind VARCHAR(12) NOT NULL,
 mime VARCHAR(80) NOT NULL,
 byte_size BIGINT UNSIGNED NOT NULL,
 checksum CHAR(64) NOT NULL,
 alt_text VARCHAR(300) NOT NULL DEFAULT '',
 created_by BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}content_media` (
 content_id BIGINT UNSIGNED NOT NULL,
 media_id BIGINT UNSIGNED NOT NULL,
 visibility VARCHAR(12) NOT NULL,
 PRIMARY KEY (content_id,media_id,visibility),
 INDEX media_visibility (media_id,visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}content_revisions` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 content_id BIGINT UNSIGNED NOT NULL,
 revision INT UNSIGNED NOT NULL,
 draft_json MEDIUMTEXT NOT NULL,
 action VARCHAR(20) NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL,
 INDEX content_revision_history (content_id,revision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

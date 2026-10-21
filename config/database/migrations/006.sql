CREATE TABLE IF NOT EXISTS `{{prefix}}staff_tokens` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 token_hash CHAR(64) NOT NULL UNIQUE,
 email VARCHAR(190) NOT NULL,
 name VARCHAR(120) NOT NULL,
 kind VARCHAR(12) NOT NULL,
 target_role VARCHAR(20) NOT NULL,
 user_id BIGINT UNSIGNED NULL,
 issuer_id BIGINT UNSIGNED NULL,
 expected_version INT NULL,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 INDEX staff_token_email (email,kind,created_at),
 INDEX staff_token_expiry (expires_at,used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

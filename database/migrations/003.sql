CREATE TABLE IF NOT EXISTS `{{prefix}}service_settings` (
 namespace VARCHAR(40) PRIMARY KEY,
 settings_json MEDIUMTEXT NOT NULL,
 updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}request_limits` (
 bucket CHAR(64) PRIMARY KEY,
 attempts INT NOT NULL DEFAULT 0,
 expires_at BIGINT NOT NULL,
 INDEX request_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}bookings` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 reference VARCHAR(20) NOT NULL UNIQUE,
 submission_hash CHAR(64) NOT NULL UNIQUE,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL,
 payload_json MEDIUMTEXT NOT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'new',
 private_notes MEDIUMTEXT NOT NULL,
 assigned_to BIGINT UNSIGNED NULL,
 revision INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 INDEX booking_status (status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}subscribers` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(190) NOT NULL UNIQUE,
 status VARCHAR(20) NOT NULL DEFAULT 'pending',
 confirm_hash CHAR(64) NULL,
 confirm_expires_at DATETIME NULL,
 unsubscribe_hash CHAR(64) NULL,
 requested_at DATETIME NOT NULL,
 confirmed_at DATETIME NULL,
 unsubscribed_at DATETIME NULL,
 consent_version VARCHAR(40) NOT NULL,
 source VARCHAR(40) NOT NULL,
 INDEX subscriber_confirmation (confirm_hash),
 INDEX subscriber_unsubscribe (unsubscribe_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `{{prefix}}mail_queue` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 dedupe_key VARCHAR(190) NOT NULL UNIQUE,
 recipient VARCHAR(190) NOT NULL,
 subject VARCHAR(200) NOT NULL,
 encrypted_body MEDIUMTEXT NOT NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'queued',
 attempts INT NOT NULL DEFAULT 0,
 available_at DATETIME NOT NULL,
 claimed_at DATETIME NULL,
 claim_token CHAR(32) NULL,
 last_error VARCHAR(80) NULL,
 created_at DATETIME NOT NULL,
 sent_at DATETIME NULL,
 INDEX mail_delivery (status,available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

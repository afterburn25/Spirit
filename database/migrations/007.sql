CREATE TABLE IF NOT EXISTS `{{prefix}}analytics_visits` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_key CHAR(64) NOT NULL UNIQUE,
 started_at DATETIME NOT NULL,
 last_seen DATETIME NOT NULL,
 active_clock_at DATETIME NOT NULL,
 page_count INT UNSIGNED NOT NULL DEFAULT 0,
 active_seconds INT UNSIGNED NOT NULL DEFAULT 0,
 referrer_domain VARCHAR(190) NOT NULL DEFAULT '',
 search_term VARCHAR(160) NOT NULL DEFAULT '',
 utm_source VARCHAR(80) NOT NULL DEFAULT '',
 utm_medium VARCHAR(80) NOT NULL DEFAULT '',
 utm_campaign VARCHAR(80) NOT NULL DEFAULT '',
 browser_family VARCHAR(32) NOT NULL DEFAULT 'Other',
 os_family VARCHAR(24) NOT NULL DEFAULT 'Other',
 device_family VARCHAR(12) NOT NULL DEFAULT 'Desktop',
 country_code VARCHAR(2) NOT NULL DEFAULT '',
 country_name VARCHAR(80) NOT NULL DEFAULT '',
 region VARCHAR(100) NOT NULL DEFAULT '',
 city VARCHAR(100) NOT NULL DEFAULT '',
 INDEX analytics_visit_started (started_at),
 INDEX analytics_visit_seen (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}analytics_pageviews` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id BIGINT UNSIGNED NOT NULL,
 page_path VARCHAR(255) NOT NULL,
 viewed_at DATETIME NOT NULL,
 last_seen DATETIME NOT NULL,
 active_seconds INT UNSIGNED NOT NULL DEFAULT 0,
 activity_samples INT UNSIGNED NOT NULL DEFAULT 0,
 is_visible TINYINT UNSIGNED NOT NULL DEFAULT 0,
 INDEX analytics_page_date (viewed_at),
 INDEX analytics_page_visit (visit_id,id),
 INDEX analytics_page_live (is_visible,last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{prefix}}analytics_crawlers` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_date DATE NOT NULL,
 family VARCHAR(32) NOT NULL,
 verification VARCHAR(24) NOT NULL,
 page_path VARCHAR(255) NOT NULL,
 hits INT UNSIGNED NOT NULL DEFAULT 1,
 last_seen DATETIME NOT NULL,
 UNIQUE KEY analytics_crawler_day (visit_date,family,verification,page_path),
 INDEX analytics_crawler_seen (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

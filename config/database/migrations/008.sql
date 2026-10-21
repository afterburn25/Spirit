CREATE TABLE IF NOT EXISTS `{{prefix}}analytics_search_queries` (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 period_start DATE NOT NULL,
 period_end DATE NOT NULL,
 query_text VARCHAR(255) NOT NULL,
 clicks INT UNSIGNED NOT NULL,
 impressions INT UNSIGNED NOT NULL,
 average_position DECIMAL(10,4) NOT NULL,
 imported_at DATETIME NOT NULL,
 INDEX analytics_query_clicks (clicks)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

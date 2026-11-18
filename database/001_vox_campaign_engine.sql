CREATE TABLE IF NOT EXISTS vox_campaigns (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  platform ENUM('instagram','facebook','youtube','tiktok','telegram') NOT NULL,
  goal ENUM('publish_content') NOT NULL DEFAULT 'publish_content',
  timezone VARCHAR(64) NOT NULL,
  status ENUM('draft','scheduled','paused','completed','cancelled') NOT NULL DEFAULT 'draft',
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  settings_json JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vox_campaign_owner_status (user_id, status),
  KEY idx_vox_campaign_dates (starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vox_campaign_tasks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id BIGINT UNSIGNED NOT NULL,
  sequence_no INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NOT NULL,
  task_type ENUM('publish_content') NOT NULL DEFAULT 'publish_content',
  payload_json JSON NOT NULL,
  status ENUM('preview','queued','processing','published','failed','cancelled') NOT NULL DEFAULT 'preview',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  idempotency_key CHAR(64) NOT NULL,
  last_error VARCHAR(1000) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vox_task_idempotency (idempotency_key),
  KEY idx_vox_task_due (status, scheduled_at),
  CONSTRAINT fk_vox_task_campaign FOREIGN KEY (campaign_id) REFERENCES vox_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vox_campaign_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id BIGINT UNSIGNED NOT NULL,
  task_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(64) NOT NULL,
  detail_json JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vox_events_campaign (campaign_id, created_at),
  CONSTRAINT fk_vox_event_campaign FOREIGN KEY (campaign_id) REFERENCES vox_campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_vox_event_task FOREIGN KEY (task_id) REFERENCES vox_campaign_tasks(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

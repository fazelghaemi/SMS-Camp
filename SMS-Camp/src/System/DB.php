<?php
namespace RSMSP\System;

class DB {
  public static function install(): void { self::maybe_install(true); }

  public static function maybe_install(bool $force=false): void {
    global $wpdb;
    $tables = [
      $wpdb->prefix.'rsms_queue' => self::sql_queue($wpdb),
      $wpdb->prefix.'rsms_logs'  => self::sql_logs($wpdb),
      $wpdb->prefix.'rsms_segments' => self::sql_segments($wpdb),
      $wpdb->prefix.'rsms_campaigns' => self::sql_campaigns($wpdb),
    ];
    foreach ($tables as $name => $sql) {
      $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $name));
      if (!$exists) { $wpdb->query($sql); }
    }
  }

  protected static function common_charset(\wpdb $wpdb): string {
    return $wpdb->get_charset_collate();
  }

  protected static function sql_queue(\wpdb $wpdb): string {
    $charset = self::common_charset($wpdb);
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rsms_queue (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      campaign_id BIGINT UNSIGNED NULL,
      user_id BIGINT UNSIGNED NULL,
      phone VARCHAR(32) NOT NULL,
      channel VARCHAR(20) NOT NULL DEFAULT 'sms',
      template_code VARCHAR(64) NOT NULL,
      params_json LONGTEXT NULL,
      status VARCHAR(20) NOT NULL DEFAULT 'queued',
      attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
      provider_msg_id VARCHAR(64) NULL,
      last_error TEXT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NULL,
      PRIMARY KEY (id),
      KEY idx_status (status),
      KEY idx_campaign (campaign_id),
      KEY idx_created (created_at)
    ) $charset;";
  }

  protected static function sql_logs(\wpdb $wpdb): string {
    $charset = self::common_charset($wpdb);
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rsms_logs (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      level VARCHAR(10) NOT NULL DEFAULT 'info',
      message TEXT NULL,
      context_json LONGTEXT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY idx_level (level),
      KEY idx_created (created_at)
    ) $charset;";
  }

  protected static function sql_segments(\wpdb $wpdb): string {
    $charset = self::common_charset($wpdb);
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rsms_segments (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      name VARCHAR(191) NOT NULL,
      slug VARCHAR(191) UNIQUE,
      definition_json LONGTEXT NOT NULL,
      count_cache BIGINT UNSIGNED NOT NULL DEFAULT 0,
      last_built_at DATETIME NULL,
      created_by BIGINT UNSIGNED NULL,
      is_dynamic TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NULL,
      PRIMARY KEY (id)
    ) $charset;";
  }

  protected static function sql_campaigns(\wpdb $wpdb): string {
    $charset = self::common_charset($wpdb);
    return "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rsms_campaigns (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      name VARCHAR(191) NOT NULL,
      segment_id BIGINT UNSIGNED NULL,
      channel VARCHAR(20) NOT NULL DEFAULT 'sms',
      template_code VARCHAR(64) NOT NULL,
      params_map_json LONGTEXT NULL,
      schedule_at DATETIME NULL,
      rate_limit_per_min SMALLINT UNSIGNED NOT NULL DEFAULT 60,
      status VARCHAR(20) NOT NULL DEFAULT 'draft',
      ab_group VARCHAR(1) NOT NULL DEFAULT 'A',
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NULL,
      PRIMARY KEY (id),
      KEY idx_segment (segment_id),
      KEY idx_status (status)
    ) $charset;";
  }
}

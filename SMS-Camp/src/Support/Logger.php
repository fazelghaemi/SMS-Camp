<?php
namespace RSMSP\Support;
class Logger {
  protected static function upload_dir(): string {
    $u = wp_upload_dir();
    $dir = trailingslashit($u['basedir']).'sms-camp/logs';
    if (!file_exists($dir)) wp_mkdir_p($dir);
    return $dir;
  }
  protected static function logfile(): string {
    $dir = self::upload_dir();
    $file = $dir.'/sms-camp-'.date('Y-m-d').'.log';
    return $file;
  }
  public static function log(string $level,string $message,array $context=[]): void {
    global $wpdb; $tbl=$wpdb->prefix.'rsms_logs';
    $wpdb->insert($tbl,['level'=>$level,'message'=>$message,'context_json'=>wp_json_encode($context, JSON_UNESCAPED_UNICODE)]);
    // file log
    $line = sprintf('[%s] %s: %s %s', date('Y-m-d H:i:s'), strtoupper($level), $message, $context?json_encode($context, JSON_UNESCAPED_UNICODE):'');
    @file_put_contents(self::logfile(), $line.PHP_EOL, FILE_APPEND | LOCK_EX);
  }
  public static function info(string $m,array $c=[]): void { self::log('info',$m,$c); }
  public static function warn(string $m,array $c=[]): void { self::log('warn',$m,$c); }
  public static function error(string $m,array $c=[]): void { self::log('error',$m,$c); }

  // Cleanup logs older than 3 days (DB + files)
  public static function schedule_cleanup(): void {
    if (!wp_next_scheduled('rsms_log_cleanup')) {
      wp_schedule_event(time()+3600, 'daily', 'rsms_log_cleanup');
    }
    add_action('rsms_log_cleanup', [__CLASS__, 'cleanup']);
  }
  public static function cleanup(): void {
    global $wpdb; $tbl=$wpdb->prefix.'rsms_logs';
    $wpdb->query("DELETE FROM $tbl WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
    $dir = self::upload_dir();
    if (is_dir($dir)){
      foreach (glob($dir.'/sms-camp-*.log') as $file){
        // parse date from filename
        if (preg_match('/sms-camp-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)){
          $d = strtotime($m[1]);
          if ($d && $d < strtotime('-3 days')) { @unlink($file); }
        }
      }
    }
  }
}

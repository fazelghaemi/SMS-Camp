<?php
namespace RSMSP\Support;
class Logger {
  public static function log(string $level,string $message,array $context=[]): void {
    global $wpdb; $tbl=$wpdb->prefix.'rsms_logs';
    $wpdb->insert($tbl,['level'=>$level,'message'=>$message,'context_json'=>wp_json_encode($context, JSON_UNESCAPED_UNICODE)]);
  }
  public static function info(string $m,array $c=[]): void { self::log('info',$m,$c); }
  public static function warn(string $m,array $c=[]): void { self::log('warn',$m,$c); }
  public static function error(string $m,array $c=[]): void { self::log('error',$m,$c); }
}

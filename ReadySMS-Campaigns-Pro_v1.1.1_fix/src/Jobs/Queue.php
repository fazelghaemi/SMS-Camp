<?php
namespace RSMSP\Jobs;
use RSMSP\Providers\Msgway; use RSMSP\Support\Logger;
class Queue {
  const CRON_HOOK='rsms_dispatch_tick';
  public static function init(): void { add_action(self::CRON_HOOK,[__CLASS__,'dispatch']); self::schedule(); }
  public static function schedule(): void { if (!wp_next_scheduled(self::CRON_HOOK)) wp_schedule_event(time()+60,'rsms_minute',self::CRON_HOOK); }
  public static function clear_schedule(): void { $ts=wp_next_scheduled(self::CRON_HOOK); if($ts) wp_unschedule_event($ts,self::CRON_HOOK); }
  public static function enqueue(string $phone,string $template,array $orderedParams,string $channel='sms',?int $campaign_id=null,?int $user_id=null): int {
    global $wpdb; $tbl=$wpdb->prefix.'rsms_queue';
    $wpdb->insert($tbl,['phone'=>$phone,'template_code'=>$template,'params_json'=>wp_json_encode(array_values($orderedParams), JSON_UNESCAPED_UNICODE),'channel'=>$channel,'campaign_id'=>$campaign_id,'user_id'=>$user_id,'status'=>'queued']); return (int)$wpdb->insert_id;
  }
  public static function next_batch(int $limit=200): array { global $wpdb; $tbl=$wpdb->prefix.'rsms_queue'; return $wpdb->get_results($wpdb->prepare("SELECT * FROM $tbl WHERE status=%s ORDER BY id ASC LIMIT %d",'queued',$limit), ARRAY_A) ?: []; }
  public static function dispatch(): void {
    $items=self::next_batch(); if(!$items) return; global $wpdb; $tbl=$wpdb->prefix.'rsms_queue';
    foreach($items as $row){ $params=json_decode($row['params_json']??'[]',true)?:[];
      try{ $resp=Msgway::send_template($row['template_code'],$row['phone'],$params,$row['channel']);
        if($resp['status']==='ok'){ $wpdb->update($tbl,['status'=>'sent','provider_msg_id'=>$resp['provider_id'],'updated_at'=>current_time('mysql')],['id'=>$row['id']]); }
        else{ $wpdb->update($tbl,['status'=>'failed','last_error'=>$resp['message'],'updated_at'=>current_time('mysql')],['id'=>$row['id']]); }
      }catch(\Throwable $e){ $wpdb->update($tbl,['status'=>'failed','last_error'=>$e->getMessage(),'updated_at'=>current_time('mysql')],['id'=>$row['id']]); Logger::error('dispatch_exception',['id'=>$row['id'],'e'=>$e->getMessage()]); }
    }
  }
}

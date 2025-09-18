<?php
namespace RSMSP\Providers;
class Msgway {
  public static function api_send(): string { return get_option('rsms_msgway_send_url','https://api.msgway.com/send'); }
  public static function api_template_get(): string { return get_option('rsms_msgway_tpl_url','https://api.msgway.com/template/get'); }
  public static function default_channel(): string { return get_option('rsms_default_channel','sms'); }
  public static function rate_limit(): int { return (int)get_option('rsms_rate_limit',60); }
  public static function auth_headers(): array { $token=trim((string)get_option('rsms_auth_token','')); $h=['Content-Type'=>'application/json']; if($token!=='') $h['Authorization']='Bearer '.$token; return $h; }
  public static function get_template(int $template_id): array {
    $resp=wp_remote_post(self::api_template_get(),['timeout'=>20,'headers'=>self::auth_headers(),'body'=>wp_json_encode(['templateID'=>$template_id], JSON_UNESCAPED_UNICODE)]);
    if(is_wp_error($resp)) return ['status'=>'error','message'=>$resp->get_error_message()];
    $body=json_decode(wp_remote_retrieve_body($resp),true);
    if(isset($body['status']) && $body['status']==='success') return ['status'=>'ok','data'=>$body['data']??null];
    return ['status'=>'error','message'=>$body['error']['message']??'Unknown error'];
  }
  public static function send_template(string $template_code,string $mobile,array $params,string $channel=''): array {
    if($channel==='') $channel=self::default_channel();
    $payload=['mobile'=>$mobile,'templateID'=>$template_code,'params'=>array_values($params),'channel'=>$channel];
    $resp=wp_remote_post(self::api_send(),['timeout'=>30,'headers'=>self::auth_headers(),'body'=>wp_json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    if(is_wp_error($resp)) return ['status'=>'error','message'=>$resp->get_error_message()];
    $body=json_decode(wp_remote_retrieve_body($resp),true);
    if(isset($body['status']) && $body['status']==='success') return ['status'=>'ok','provider_id'=>($body['data']['messageID']??'')];
    return ['status'=>'error','message'=>$body['error']['message']??'Unknown error'];
  }
}

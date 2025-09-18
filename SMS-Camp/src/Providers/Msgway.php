<?php
namespace RSMSP\Providers;

class Msgway {
  const API_SEND = 'https://api.msgway.com/send';
  const API_TEMPLATE_GET = 'https://api.msgway.com/template/get';

  protected static function headers(): array {
    // Use 'apiKey' header per provider requirement
    $apiKey = trim((string) get_option('rsms_auth_token',''));
    $h = ['Content-Type'=>'application/json'];
    if ($apiKey !== '') $h['apiKey'] = $apiKey;
    return $h;
  }

  public static function get_template(int $template_id): array {
    $payload = ['templateID' => $template_id];
    $resp = wp_remote_post(self::API_TEMPLATE_GET, [
      'timeout'=>20,
      'headers'=> self::headers(),
      'body'=> wp_json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);
    if (is_wp_error($resp)) return ['status'=>'error','message'=>$resp->get_error_message()];
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (isset($body['status']) && $body['status']==='success') return ['status'=>'ok','data'=>$body['data'] ?? null];
    return ['status'=>'error','message'=>$body['error']['message'] ?? 'Unknown error','raw'=>$body];
  }

  /** $params ordered (positional) */
  public static function send_template(string $template_code, string $mobile, array $params, string $channel='sms'): array {
    $payload = [
      'mobile' => $mobile,
      'templateID' => $template_code,
      'params' => array_values($params),
      'channel' => $channel
    ];
    $resp = wp_remote_post(self::API_SEND, [
      'timeout'=>30,
      'headers'=> self::headers(),
      'body'=> wp_json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);
    if (is_wp_error($resp)) return ['status'=>'error','message'=>$resp->get_error_message()];
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (isset($body['status']) && $body['status']==='success') return ['status'=>'ok','provider_id'=>($body['data']['messageID'] ?? '')];
    return ['status'=>'error','message'=>$body['error']['message'] ?? 'Unknown error','raw'=>$body];
  }
}

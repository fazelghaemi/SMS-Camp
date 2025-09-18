<?php
namespace RSMSP\Providers;

class Msgway {
  const API_SEND = 'https://api.msgway.com/send';
  const API_TEMPLATE_GET = 'https://api.msgway.com/template/get';

  protected static function headers(): array {
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

  protected static function map_method_and_provider(string $channel): array {
    // Map UI channel to API method/provider
    $ch = strtolower($channel);
    switch ($ch) {
      case 'sms': return ['method'=>'sms', 'provider'=>null];
      case 'voice': return ['method'=>'ivr', 'provider'=>null];
      case 'gap': return ['method'=>'messenger', 'provider'=>2];
      case 'igap': return ['method'=>'messenger', 'provider'=>3];
      case 'bale': return ['method'=>'messenger', 'provider'=>4];
      case 'eitaa': return ['method'=>'messenger', 'provider'=>5];
      default: return ['method'=>'sms', 'provider'=>null];
    }
  }

  /** $params ordered (positional) */
  public static function send_template(string $template_code, string $mobile, array $params, string $channel='sms'): array {
    $map = self::map_method_and_provider($channel);
    $payload = [
      'mobile' => $mobile,
      'method' => $map['method'],
      'templateID' => is_numeric($template_code) ? intval($template_code) : $template_code,
    ];
    if (!empty($params)) { $payload['params'] = array_values($params); }
    if ($map['provider']) { $payload['provider'] = $map['provider']; }

    $resp = wp_remote_post(self::API_SEND, [
      'timeout'=>30,
      'headers'=> self::headers(),
      'body'=> wp_json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);
    if (is_wp_error($resp)) return ['status'=>'error','message'=>$resp->get_error_message()];
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (isset($body['status']) && $body['status']==='success') return ['status'=>'ok','provider_id'=>($body['data']['messageID'] ?? '')];
    return ['status'=>'error','message'=>$body['error']['message'] ?? 'Unknown error','raw'=>$body,'payload'=>$payload];
  }
}

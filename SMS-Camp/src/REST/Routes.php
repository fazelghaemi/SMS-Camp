<?php
namespace RSMSP\REST;
use WP_REST_Server;
use RSMSP\Jobs\Queue;
class Routes {
  public static function init(): void {
    add_action('rest_api_init', function(){
      register_rest_route('readysms/v1','/enqueue',[
        'methods'=>WP_REST_Server::CREATABLE,
        'permission_callback'=>function(){ return current_user_can('manage_options'); },
        'args'=>['phone'=>['required'=>true],'template'=>['required'=>true],'params'=>['required'=>false]],
        'callback'=>function($req){ $p=(array)($req['params']??[]); $id=Queue::enqueue((string)$req['phone'], (string)$req['template'], $p); return ['status'=>'ok','queue_id'=>$id]; }
      ]);
    });
  }
}

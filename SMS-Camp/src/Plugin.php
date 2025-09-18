<?php
namespace RSMSP;
use RSMSP\System\DB;
use RSMSP\Admin\Menu;
use RSMSP\Jobs\Queue;
use RSMSP\REST\Routes;
use RSMSP\Support\Brand;
use RSMSP\Support\Logger;

class Plugin {
  public static function init(): void {
    if (!class_exists('WooCommerce')) {
      add_action('admin_notices', function(){
        echo '<div class="notice notice-error"><p>افزونه «کمپ اس ام اس (SMS Camp)» نیاز به فعال بودن <b>WooCommerce</b> دارد.</p></div>';
      });
      return;
    }
    DB::maybe_install();
    Menu::init();
    Queue::init();
    if (class_exists('RSMSP\\REST\\Routes')) { Routes::init(); }
    Logger::schedule_cleanup();
    add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
    add_action('wp_ajax_rsms_fetch_template', [__CLASS__, 'ajax_fetch_template']);
  }

  public static function assets(string $hook): void {
    // Always load on our pages (both old/new slugs)
    $ok = (strpos($hook,'smscamp')!==false) || (strpos($hook,'readysms')!==false);
    if (!$ok) {
      // fallback: check current screen id
      $screen = function_exists('get_current_screen') ? get_current_screen() : null;
      $sid = $screen ? $screen->id : '';
      if (strpos((string)$sid, 'smscamp')===false && strpos((string)$sid, 'readysms')===false) return;
    }
    wp_enqueue_style('rsms-admin', RSMSP_URL.'assets/admin.css', [], RSMSP_VERSION);
    wp_enqueue_script('rsms-admin', RSMSP_URL.'assets/admin.js', ['jquery'], RSMSP_VERSION, true);
    wp_localize_script('rsms-admin','rsms', [
      'ajax'=>admin_url('admin-ajax.php'),
      'nonce'=>wp_create_nonce('rsms_nonce'),
      'brandColor'=>Brand::color()
    ]);
  }

  public static function ajax_fetch_template(){
    check_ajax_referer('rsms_nonce');
    $tpl = isset($_POST['template']) ? intval($_POST['template']) : 0;
    if(!$tpl){ wp_send_json(['status'=>'error','message'=>'template missing']); }
    $res = \RSMSP\Providers\Msgway::get_template($tpl);
    if($res['status']==='ok'){
      $preview = is_array($res['data']) ? print_r($res['data'], true) : (string) $res['data'];
      wp_send_json(['status'=>'ok','preview'=>$preview]);
    }
    wp_send_json(['status'=>'error','message'=>$res['message'] ?? 'unknown']);
  }
}

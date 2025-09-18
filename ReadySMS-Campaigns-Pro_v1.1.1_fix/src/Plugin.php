<?php
namespace RSMSP;
use RSMSP\System\DB;
use RSMSP\Admin\Menu;
use RSMSP\Jobs\Queue;
use RSMSP\REST\Routes;
use RSMSP\Support\Brand;

class Plugin {
  public static function init(): void {
    if (!class_exists('WooCommerce')) {
      add_action('admin_notices', function(){
        echo '<div class="notice notice-error"><p>افزونه «کمپین‌های پیامکی ReadySMS» نیاز به فعال بودن <b>WooCommerce</b> دارد.</p></div>';
      });
      return;
    }
    DB::maybe_install();
    Menu::init();
    Queue::init();
    if (class_exists('RSMSP\\REST\\Routes')) { Routes::init(); }
    add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
  }
  public static function assets(string $hook): void {
    if (strpos($hook, 'readysms') === false) return;
    wp_enqueue_style('rsms-admin', RSMSP_URL.'assets/admin.css', [], RSMSP_VERSION);
    wp_enqueue_script('rsms-admin', RSMSP_URL.'assets/admin.js', ['jquery'], RSMSP_VERSION, true);
    wp_localize_script('rsms-admin','rsms', [
      'ajax'=>admin_url('admin-ajax.php'),
      'nonce'=>wp_create_nonce('rsms_nonce'),
      'brandColor'=>Brand::color()
    ]);
  }
}

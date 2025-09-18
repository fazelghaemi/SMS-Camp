<?php
/**
 * Plugin Name: کمپین‌های پیامکی ReadySMS (راه‌پیام) برای ووکامرس
 * Description: لانچر حرفه‌ای کمپین‌های پیامکی راه‌پیام با سگمنتیشن رفتاری مشتریان ووکامرس + ساخت کوپن + گزارش و صف ارسال.
 * Version: 1.1.1
 * Author: Ready Studio
 * Requires PHP: 8.1
 * Requires at least: 6.3
 * Text Domain: readysms-campaigns-pro
 * Domain Path: /languages
 */
declare(strict_types=1);
if (!defined('ABSPATH')) exit;

define('RSMSP_FILE', __FILE__);
define('RSMSP_DIR', plugin_dir_path(__FILE__));
define('RSMSP_URL', plugin_dir_url(__FILE__));
define('RSMSP_VERSION', '1.1.0');
define('RSMSP_BRAND_COLOR', '#00b0a4');

spl_autoload_register(function($class){
  if (strpos($class, 'RSMSP\\') !== 0) return;
  $rel = str_replace('RSMSP\\','',$class);
  $rel = str_replace('\\', DIRECTORY_SEPARATOR, $rel);
  $file = RSMSP_DIR . 'src/' . $rel . '.php';
  if (is_readable($file)) require_once $file;
});

add_filter('cron_schedules', function($s){
  $s['rsms_minute'] = ['interval'=>60,'display'=>'هر ۱ دقیقه (ReadySMS)'];
  return $s;
});

register_activation_hook(__FILE__, function(){ RSMSP\System\Activator::activate(); });
register_deactivation_hook(__FILE__, function(){ RSMSP\System\Deactivator::deactivate(); });

add_action('plugins_loaded', function(){
  load_plugin_textdomain('readysms-campaigns-pro', false, dirname(plugin_basename(__FILE__)).'/languages');
  RSMSP\Plugin::init();
});

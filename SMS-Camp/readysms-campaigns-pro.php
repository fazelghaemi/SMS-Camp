<?php
/**
 * Plugin Name: کمپ اس ام اس (SMS Camp) برای ووکامرس
 * Description: کمپ اس ام اس (SMS Camp)؛ لانچر حرفه‌ای کمپین‌های پیامکی راه‌پیام با سگمنتیشن رفتاری ووکامرس + ارسال زنده + گزارش چارت.
 * Version: 1.3.4
 * Author: Ready Studio
 * Requires PHP: 8.1
 * Requires at least: 6.3
 * Text Domain: readysms-campaigns-pro
 * Domain Path: /languages
 */
declare(strict_types=1);
if (!defined('ABSPATH')) exit;

// Prevent double bootstrap if file is loaded twice for any reason
if (defined('RSMSP_BOOTSTRAPPED')) { return; }
define('RSMSP_BOOTSTRAPPED', true);

if (!defined('RSMSP_FILE')) define('RSMSP_FILE', __FILE__);
if (!defined('RSMSP_DIR')) define('RSMSP_DIR', plugin_dir_path(__FILE__));
if (!defined('RSMSP_URL')) define('RSMSP_URL', plugin_dir_url(__FILE__));
if (!defined('RSMSP_VERSION')) define('RSMSP_VERSION', '1.3.4');
if (!defined('RSMSP_BRAND_COLOR')) define('RSMSP_BRAND_COLOR', '#00b0a4');

// Autoload (PSR-4-like), safe to register multiple times
spl_autoload_register(function($class){
  if (strpos($class, 'RSMSP\\') !== 0) return;
  $rel = str_replace('RSMSP\\','',$class);
  $rel = str_replace('\\', DIRECTORY_SEPARATOR, $rel);
  $file = RSMSP_DIR . 'src/' . $rel . '.php';
  if (is_readable($file)) require_once $file;
});

// CRON schedule
add_filter('cron_schedules', function($s){
  $s['rsms_minute'] = ['interval'=>60,'display'=>'هر ۱ دقیقه (SMS Camp)'];
  return $s;
});

register_activation_hook(__FILE__, function(){
  RSMSP\System\Activator::activate();
});
register_deactivation_hook(__FILE__, function(){
  RSMSP\System\Deactivator::deactivate();
});

add_action('plugins_loaded', function(){
  load_plugin_textdomain('readysms-campaigns-pro', false, dirname(plugin_basename(__FILE__)).'/languages');
  if (class_exists('RSMSP\\Plugin')) { RSMSP\Plugin::init(); }
});

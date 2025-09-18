<?php
namespace RSMSP\Admin;
class Menu {
  public static function init(): void { add_action('admin_menu',[__CLASS__,'menu']); }
  public static function menu(): void {
    add_menu_page('SMS Camp','SMS Camp','manage_options','smscamp',[__CLASS__,'dashboard'],'dashicons-megaphone',56);
    add_submenu_page('smscamp','داشبورد','داشبورد','manage_options','smscamp',[__CLASS__,'dashboard']);
    add_submenu_page('smscamp','سگمنت‌ها','سگمنت‌ها','manage_options','smscamp-segments',[__CLASS__,'segments']);
    add_submenu_page('smscamp','کمپین‌ها','کمپین‌ها','manage_options','smscamp-campaigns',[__CLASS__,'campaigns']);
    add_submenu_page('smscamp','ارسال زنده','ارسال زنده','manage_options','smscamp-live',[__CLASS__,'live']);
    add_submenu_page('smscamp','گزارش‌ها','گزارش‌ها','manage_options','smscamp-logs',[__CLASS__,'logs']);
    add_submenu_page('smscamp','تنظیمات راه‌پیام','تنظیمات راه‌پیام','manage_options','smscamp-settings',[__CLASS__,'settings']);
  }
  public static function dashboard(): void { require RSMSP_DIR.'views/dashboard.php'; }
  public static function segments(): void { require RSMSP_DIR.'views/segments.php'; }
  public static function campaigns(): void { require RSMSP_DIR.'views/campaigns.php'; }
  public static function live(): void { require RSMSP_DIR.'views/live.php'; }
  public static function logs(): void { require RSMSP_DIR.'views/logs.php'; }
  public static function settings(): void { require RSMSP_DIR.'views/settings.php'; }
}

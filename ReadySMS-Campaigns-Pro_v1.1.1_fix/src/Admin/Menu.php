<?php
namespace RSMSP\Admin;
class Menu {
  public static function init(): void { add_action('admin_menu',[__CLASS__,'menu']); }
  public static function menu(): void {
    add_menu_page('ReadySMS','ReadySMS','manage_options','readysms',[__CLASS__,'dashboard'],'dashicons-megaphone',56);
    add_submenu_page('readysms','سگمنت‌ها','سگمنت‌ها','manage_options','readysms-segments',[__CLASS__,'segments']);
    add_submenu_page('readysms','کمپین‌ها','کمپین‌ها','manage_options','readysms-campaigns',[__CLASS__,'campaigns']);
    add_submenu_page('readysms','ارسال زنده','ارسال زنده','manage_options','readysms-live-send',[__CLASS__,'live']);
    add_submenu_page('readysms','گزارش‌ها','گزارش‌ها','manage_options','readysms-logs',[__CLASS__,'logs']);
    add_submenu_page('readysms','تنظیمات راه‌پیام','تنظیمات راه‌پیام','manage_options','readysms-settings',[__CLASS__,'settings']);
  }
  public static function dashboard(): void { require RSMSP_DIR.'views/dashboard.php'; }
  public static function segments(): void { require RSMSP_DIR.'views/segments.php'; }
  public static function campaigns(): void { require RSMSP_DIR.'views/campaigns.php'; }
  public static function live(): void { require RSMSP_DIR.'views/live.php'; }
  public static function logs(): void { require RSMSP_DIR.'views/logs.php'; }
  public static function settings(): void { require RSMSP_DIR.'views/settings.php'; }
}

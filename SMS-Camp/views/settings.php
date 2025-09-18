<?php if (!defined('ABSPATH')) exit;
if (isset($_POST['rsms_settings_save']) && check_admin_referer('rsms_settings','rsms_settings_nonce')) {
  update_option('rsms_auth_token', sanitize_text_field($_POST['auth_token'] ?? ''));
  update_option('rsms_default_channel', sanitize_text_field($_POST['default_channel'] ?? 'sms'));
  update_option('rsms_rate_limit', intval($_POST['rate_limit'] ?? 60));
  echo '<div class="updated notice"><p>تنظیمات ذخیره شد.</p></div>';
}
$auth=get_option('rsms_auth_token',''); $channel=get_option('rsms_default_channel','sms'); $rate=intval(get_option('rsms_rate_limit',60));
?><div class="wrap rsms-rtl"><h1 class="rsms-title">تنظیمات راه‌پیام</h1><div class="rsms-card"><form method="post"><?php wp_nonce_field('rsms_settings','rsms_settings_nonce'); ?><table class="form-table">
<tr><th>کلید API</th><td><input type="password" name="auth_token" class="rsms-input" value="<?php echo esc_attr($auth); ?>"></td></tr>


<tr><th>کانال پیش‌فرض</th><td><select name="default_channel" class="rsms-input"><?php foreach(['sms','voice','gap','igap','bale','eitaa'] as $ch){ $sel=$ch===$channel?'selected':''; echo '<option '.$sel.' value="'.$ch.'">'.$ch.'</option>'; } ?></select></td></tr>
<tr><th>نرخ ارسال در دقیقه</th><td><input type="number" name="rate_limit" class="rsms-input small" value="<?php echo esc_attr($rate); ?>"></td></tr>
</table><p><button class="button button-primary" name="rsms_settings_save" value="1">ذخیره تنظیمات</button></p></form></div></div>
<?php if (!defined('ABSPATH')) exit;
use RSMSP\Jobs\Queue;
if (isset($_POST['rsms_campaign_enqueue']) && check_admin_referer('rsms_campaign','rsms_campaign_nonce')) {
  $phone=sanitize_text_field($_POST['phone']??''); $tpl=sanitize_text_field($_POST['template']??''); $channel=sanitize_text_field($_POST['channel']??''); $p=[];
  for($i=1;$i<=7;$i++){ $k='p'.$i; if(isset($_POST[$k])) $p[] = sanitize_text_field($_POST[$k]); }
  if ($phone && $tpl){ $id=Queue::enqueue($phone,$tpl,$p,$channel); echo '<div class="updated notice"><p>در صف ارسال قرار گرفت. شناسه: '.intval($id).'</p></div>'; }
  else { echo '<div class="error notice"><p>شماره و کد الگو الزامی است.</p></div>'; }
}
?>
<div class="wrap rsms-rtl">
  <h1 class="rsms-title">کمپین‌ها</h1>
  <div class="rsms-card">
    <form method="post"><?php wp_nonce_field('rsms_campaign','rsms_campaign_nonce'); ?>
      <table class="form-table">
        <tr><th>شماره موبایل</th><td><input type="text" name="phone" class="rsms-input" placeholder="+98912… یا 0912…"></td></tr>
        <tr><th>کد الگو (TemplateID)</th><td><input type="text" name="template" class="rsms-input"></td></tr>
        <tr><th>کانال</th><td><select name="channel" class="rsms-input"><?php foreach(['sms','voice','gap','igap','bale','eitaa'] as $ch) echo '<option value="'.$ch.'">'.$ch.'</option>'; ?></select></td></tr>
        <tr><th>پارامترها (به ترتیب)</th><td><?php for($i=1;$i<=7;$i++) echo '<input class="rsms-input small" type="text" name="p'.$i.'" placeholder="param'.$i.'"> '; ?></td></tr>
      </table>
      <p><button class="button button-primary" name="rsms_campaign_enqueue" value="1">قرار دادن در صف</button></p>
    </form>
  </div>
</div>

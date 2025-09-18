<?php if (!defined('ABSPATH')) exit;
use RSMSP\Jobs\Queue;
use RSMSP\Providers\Msgway;
use RSMSP\Integrations\Woo;

// Fetch template preview via classic submit (AJAX handled in JS too for dynamic)
$template_preview = '';
if (isset($_POST['rsms_fetch_tpl']) && check_admin_referer('rsms_campaign','rsms_campaign_nonce')) {
  $tpl = intval($_POST['template'] ?? 0);
  if ($tpl) {
    $res = Msgway::get_template($tpl);
    if ($res['status']==='ok') { $template_preview = wp_kses_post(print_r($res['data'], true)); }
    else { $template_preview = 'خطا در دریافت الگو: '.esc_html($res['message'] ?? 'نامشخص'); }
  }
}

// Handle CSV/XLSX upload (first column as phone)
$uploaded_phones = [];
if (!empty($_FILES['aud_file']['name']) && check_admin_referer('rsms_campaign','rsms_campaign_nonce')) {
  require_once ABSPATH.'wp-admin/includes/file.php';
  $res = wp_handle_upload($_FILES['aud_file'], ['test_form'=>false]);
  if (!isset($res['error'])) {
    $path = $res['file'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext==='csv') {
      $lines = file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
      foreach ($lines as $ln){ $cols = str_getcsv($ln); if (!empty($cols[0])) $uploaded_phones[] = trim($cols[0]); }
    } elseif ($ext==='xlsx') {
      $zip = new ZipArchive();
      if ($zip->open($path)===true){
        $data = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($data){
          if (preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $data, $rm)){
            foreach ($rm[1] as $rowxml){
              if (preg_match('/<v>(.*?)<\/v>/', $rowxml, $vm)){
                $uploaded_phones[] = trim($vm[1]);
              }
            }
          }
        }
        $zip->close();
      }
    }
  }
}

// Handle enqueue
if (isset($_POST['rsms_campaign_enqueue']) && check_admin_referer('rsms_campaign','rsms_campaign_nonce')) {
  $tpl   = sanitize_text_field($_POST['template'] ?? '');
  $channel = sanitize_text_field($_POST['channel'] ?? 'sms');
  $p = [];
  for ($i=1;$i<=10;$i++){ $key='p'.$i; if(isset($_POST[$key])) $p[] = substr(sanitize_text_field($_POST[$key]),0,38); }

  $aud_source = sanitize_text_field($_POST['aud_source'] ?? 'site_all');
  $phones = [];

  if ($aud_source==='site_all'){
    $r1 = Woo::registered_with_phone(5000);
    $r2 = Woo::buyers_with_phone(5000);
    foreach ($r1 as $r){ $phones[] = $r['phone']; }
    foreach ($r2 as $r){ $phones[] = $r['phone']; }
  } elseif ($aud_source==='registered'){
    $rows = Woo::registered_with_phone(5000);
    foreach ($rows as $r){ $phones[] = $r['phone']; }
  } elseif ($aud_source==='buyers'){
    $rows = Woo::buyers_with_phone(5000);
    foreach ($rows as $r){ $phones[] = $r['phone']; }
  } elseif ($aud_source==='excel'){
    if ($uploaded_phones) $phones = array_merge($phones, $uploaded_phones);
  } elseif ($aud_source==='manual'){
    $manual = wp_unslash($_POST['phones_manual'] ?? '');
    if ($manual){
      $parts = preg_split('/[\s,]+/', $manual);
      foreach ($parts as $m){ if ($m) $phones[] = trim($m); }
    }
  }

  $phones = array_values(array_unique(array_filter($phones)));

  $cnt=0;
  if ($tpl && $phones){
    foreach ($phones as $mob){ Queue::enqueue($mob, $tpl, $p, $channel); $cnt++; }
    echo '<div class="updated notice"><p>'.intval($cnt).' مخاطب به صف اضافه شد.</p></div>';
  } else {
    echo '<div class="error notice"><p>کد الگو و انتخاب منبع مخاطب (یا فایل/شماره‌ها) لازم است.</p></div>';
  }
}
?>
<div class="wrap rsms-rtl">
  <h1 class="rsms-title">کمپین‌ها</h1>
  <div class="rsms-card">
    <form method="post" enctype="multipart/form-data">
      <?php wp_nonce_field('rsms_campaign','rsms_campaign_nonce'); ?>
      <table class="form-table">
        <tr><th>منبع مخاطب</th><td>
          <label class="rsms-pill"><input type="radio" name="aud_source" value="site_all" checked> از سایت (مشترک‌ها/مشتریان)</label>
          <label class="rsms-pill"><input type="radio" name="aud_source" value="excel"> از اکسل/CSV</label>
          <label class="rsms-pill"><input type="radio" name="aud_source" value="manual"> تایپ دستی</label>
          <label class="rsms-pill"><input type="radio" name="aud_source" value="registered"> مشترکین سایت (ثبت‌نامی‌ها)</label>
          <label class="rsms-pill"><input type="radio" name="aud_source" value="buyers"> مشتریان سایت (خریداران)</label>
        </td></tr>
        <tr id="aud-excel" class="aud-block" style="display:none"><th>آپلود فایل</th><td>
          <input type="file" name="aud_file" accept=".csv,.xlsx">
          <small>ستون اول = شماره موبایل. CSV یا XLSX.</small>
        </td></tr>
        <tr id="aud-manual" class="aud-block" style="display:none"><th>شماره‌ها</th><td>
          <textarea name="phones_manual" rows="3" class="rsms-textarea" placeholder="+98912..., 0912..., یک شماره در هر خط یا با ویرگول جدا کنید"></textarea>
        </td></tr>
        <tr><th>کد الگو (TemplateID)</th><td>
          <input type="number" name="template" class="rsms-input small">
          <button class="button rsms-btn" id="rsms-fetch-tpl">دریافت متن الگو</button>
          <button class="button" name="rsms_fetch_tpl" value="1">(غیرداینامیک)</button>
          <pre id="rsms-tpl-preview" style="display:none;background:#f7f7f7;border-radius:12px;padding:10px;max-height:220px;overflow:auto"></pre>
          <?php if($template_preview){ echo '<pre style="background:#f7f7f7;border-radius:12px;padding:10px;max-height:220px;overflow:auto">'. $template_preview .'</pre>'; } ?>
        </td></tr>
        <tr><th>کانال</th><td>
          <select name="channel" class="rsms-input">
            <?php foreach(['sms','voice','gap','igap','bale','eitaa'] as $ch) echo '<option value="'.$ch.'">'.$ch.'</option>'; ?>
          </select>
        </td></tr>
        <tr><th>پارامترها (به ترتیب)</th><td>
          <div id="rsms-params-wrap">
            <?php for($i=1;$i<=10;$i++) echo '<input type="text" class="rsms-input small rsms-param" name="p'.$i.'" placeholder="param'.$i.'" maxlength="38"> '; ?>
          </div>
          <small>حداکثر ۱۰ پارامتر؛ هر پارامتر حداکثر ۳۸ کاراکتر.</small>
        </td></tr>
      </table>
      <p><button class="button button-primary rsms-btn" name="rsms_campaign_enqueue" value="1">قرار دادن در صف</button></p>
    </form>
  </div>

  <h2>صف ارسال</h2>
  <?php
  global $wpdb; $tbl=$wpdb->prefix.'rsms_queue';
  $rows = $wpdb->get_results("SELECT id, phone, template_code, channel, status, attempts, created_at FROM $tbl ORDER BY id DESC LIMIT 100", ARRAY_A);
  echo '<table class="widefat striped" style="border-radius:12px;overflow:hidden"><thead><tr><th>ID</th><th>Phone</th><th>Template</th><th>Channel</th><th>Status</th><th>Attempts</th><th>Created</th></tr></thead><tbody>';
  foreach ($rows as $r){
    echo '<tr><td>'.intval($r['id']).'</td><td>'.esc_html($r['phone']).'</td><td>'.esc_html($r['template_code']).'</td><td>'.esc_html($r['channel']).'</td><td>'.esc_html($r['status']).'</td><td>'.intval($r['attempts']).'</td><td>'.esc_html($r['created_at']).'</td></tr>';
  }
  echo '</tbody></table>';
  ?>
</div>

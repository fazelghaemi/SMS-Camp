<?php if (!defined('ABSPATH')) exit;
global $wpdb; $tbl=$wpdb->prefix.'rsms_segments';
if (isset($_POST['rsms_segment_save']) && check_admin_referer('rsms_segment','rsms_segment_nonce')) {
  $name=sanitize_text_field($_POST['name']??''); $slug=sanitize_title($_POST['slug']??''); $def=wp_unslash($_POST['definition_json']??'');
  if($name && $def){ if(!empty($_POST['id'])) $wpdb->update($tbl,['name'=>$name,'slug'=>$slug,'definition_json'=>$def],['id'=>intval($_POST['id'])]); else $wpdb->insert($tbl,['name'=>$name,'slug'=>$slug,'definition_json'=>$def]); echo '<div class="updated notice"><p>سگمنت ذخیره شد.</p></div>'; }
}
$items=$wpdb->get_results("SELECT * FROM $tbl ORDER BY id DESC", ARRAY_A) ?: [];
?>
<div class="wrap rsms-rtl">
  <h1 class="rsms-title">سگمنت‌ها</h1>
  <div class="rsms-card">
    <form method="post"><?php wp_nonce_field('rsms_segment','rsms_segment_nonce'); ?>
      <table class="form-table">
        <tr><th>نام</th><td><input type="text" name="name" class="rsms-input"></td></tr>
        <tr><th>اسلاگ</th><td><input type="text" name="slug" class="rsms-input"></td></tr>
        <tr><th>تعریف JSON</th><td><textarea name="definition_json" rows="8" class="rsms-textarea" placeholder='{"logic":"AND","rules":[{"field":"days_since_last_order","op":"<=","value":30}]}'></textarea></td></tr>
      </table>
      <p><button class="button button-primary" name="rsms_segment_save" value="1">ذخیره</button></p>
    </form>
  
  <div class="rsms-card" style="border-radius:12px">
    <h2>راهنمای تعریف سگمنت (MVP)</h2>
    <p>ساختار JSON شامل <code>logic</code> (AND/OR) و آرایه <code>rules</code> است. فیلدهای متداول:</p>
    <ul>
      <li><b>days_since_last_order</b> : تعداد روز از آخرین خرید</li>
      <li><b>orders_count</b> : تعداد سفارش</li>
      <li><b>total_spent</b> : مجموع خرید</li>
      <li><b>bought_category</b> : اسلاگ دسته کالا</li>
      <li><b>city</b> : شهر در آدرس صورت‌حساب</li>
    </ul>
    <pre style="background:#f7f7f7;border-radius:12px;padding:10px;overflow:auto">{ "logic":"AND","rules":[{"field":"days_since_last_order","op":"<=","value":30},{"field":"orders_count","op":">=","value":3},{"field":"total_spent","op":">=","value":5000000}] }</pre>
  </div>
</div>
  <h2>لیست سگمنت‌ها</h2>
  <table class="widefat striped"><thead><tr><th>ID</th><th>نام</th><th>اسلاگ</th><th>بروزرسانی</th></tr></thead><tbody>
    <?php foreach($items as $it){ echo '<tr><td>'.intval($it['id']).'</td><td>'.esc_html($it['name']).'</td><td>'.esc_html($it['slug']).'</td><td>'.esc_html($it['updated_at']).'</td></tr>'; } ?>
  </tbody></table>
</div>

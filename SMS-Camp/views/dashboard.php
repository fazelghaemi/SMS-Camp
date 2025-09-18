<?php if (!defined('ABSPATH')) exit;
global $wpdb;
$q_tbl = $wpdb->prefix.'rsms_queue';
$sent = (int) $wpdb->get_var("SELECT COUNT(*) FROM $q_tbl WHERE status='sent'");
$failed = (int) $wpdb->get_var("SELECT COUNT(*) FROM $q_tbl WHERE status='failed'");
$queued = (int) $wpdb->get_var("SELECT COUNT(*) FROM $q_tbl WHERE status='queued'");
$rows = $wpdb->get_results("SELECT DATE(created_at) d, SUM(status='sent') s, SUM(status='failed') f FROM $q_tbl GROUP BY DATE(created_at) ORDER BY d ASC LIMIT 60", ARRAY_A);
$labels = array_map(fn($r)=>$r['d'],$rows ?: []);
$seriesS = array_map(fn($r)=>intval($r['s']), $rows ?: []);
$seriesF = array_map(fn($r)=>intval($r['f']), $rows ?: []);
?>
<div class="wrap rsms-rtl">
  <h1 class="rsms-title">
    <img class="rsms-logo" src="<?php echo esc_url(plugins_url('assets/img/readystudio.svg', dirname(__FILE__))); ?>" alt="Ready Studio">
    داشبورد «کمپ اس ام اس (SMS Camp)»
  </h1>

  <div class="rsms-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
    <div class="rsms-card" style="border-radius:16px"><h3>ارسال موفق</h3><div style="font-size:28px"><?php echo $sent; ?></div></div>
    <div class="rsms-card" style="border-radius:16px"><h3>در صف</h3><div style="font-size:28px"><?php echo $queued; ?></div></div>
    <div class="rsms-card" style="border-radius:16px"><h3>ناموفق</h3><div style="font-size:28px"><?php echo $failed; ?></div></div>
  </div>

  <div class="rsms-card" style="margin-top:16px;border-radius:16px">
    <h2>روند ارسال (۶۰ روز گذشته)</h2>
    <canvas id="rsmsChart" height="100"></canvas>
  </div>

  <div class="rsms-card" style="border-radius:16px">
    <h2>پیشنهاد رشد</h2>
    <p>اگر به دنبال افزایش نرخ تبدیل پیامک هستید، از <a href="https://www.msgway.com/r/lr" target="_blank">راه‌پیام</a> استفاده کنید — هماهنگ با SMS Camp.</p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  try{
    const ctx = document.getElementById('rsmsChart');
    if(!ctx) return;
    const labels = <?php echo wp_json_encode($labels); ?>;
    const dataS = <?php echo wp_json_encode($seriesS); ?>;
    const dataF = <?php echo wp_json_encode($seriesF); ?>;
    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'ارسال موفق', data: dataS, borderWidth: 2, tension: .3 },
          { label: 'ناموفق', data: dataF, borderWidth: 2, tension: .3 }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: { type: 'category' },
          y: { type: 'linear', beginAtZero: true }
        },
        plugins:{ legend:{ position: 'bottom' } }
      }
    });
  }catch(e){ console.error(e); }
})();
</script>

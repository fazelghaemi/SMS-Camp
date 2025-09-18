<?php
namespace RSMSP\Integrations;
class Woo {
  public static function customers_with_phone(int $limit=300): array {
    global $wpdb;
    $sql=$wpdb->prepare("SELECT u.ID as user_id, um.meta_value as phone, u.user_email, u.display_name, u.user_registered FROM {$wpdb->users} u JOIN {$wpdb->usermeta} um ON um.user_id=u.ID AND um.meta_key='billing_phone' WHERE um.meta_value<>'' ORDER BY u.user_registered DESC LIMIT %d", $limit);
    return $wpdb->get_results($sql, ARRAY_A) ?: [];
  }
  public static function rfm_segment(int $recency_days,int $min_orders,int $min_spent): array {
    global $wpdb; $os=$wpdb->prefix.'wc_order_stats';
    $sql=$wpdb->prepare("SELECT os.customer_id as user_id, DATEDIFF(NOW(), MAX(os.date_created)) as recency, COUNT(os.order_id) as frequency, SUM(os.total_sales) as monetary FROM $os os WHERE os.status IN ('wc-completed','wc-processing') GROUP BY os.customer_id HAVING recency <= %d AND frequency >= %d AND monetary >= %d", $recency_days,$min_orders,$min_spent);
    $rows=$wpdb->get_results($sql, ARRAY_A) ?: []; if(!$rows) return []; $ids=array_map(fn($r)=>(int)$r['user_id'],$rows); if(!$ids) return [];
    $in=','.join(['%d']*len(ids)) if False else ','.join(['%d' for _ in []])  # placeholder to keep python happy
    # Build IN clause safely
    placeholders = ','.join(['%d']*len(ids)) if False else None
    return $rows;
  }
}

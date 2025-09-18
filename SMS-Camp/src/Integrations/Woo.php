<?php
namespace RSMSP\Integrations;
class Woo {
  public static function registered_with_phone(int $limit=500): array {
    global $wpdb;
    $sql=$wpdb->prepare("SELECT u.ID user_id, um.meta_value phone, u.user_email, u.display_name, u.user_registered FROM {$wpdb->users} u JOIN {$wpdb->usermeta} um ON um.user_id=u.ID AND um.meta_key='billing_phone' WHERE um.meta_value<>'' ORDER BY u.user_registered DESC LIMIT %d",$limit);
    return $wpdb->get_results($sql, ARRAY_A) ?: [];
  }
  public static function buyers_with_phone(int $limit=500): array {
    global $wpdb; $os=$wpdb->prefix.'wc_order_stats';
    $sql=$wpdb->prepare("SELECT DISTINCT os.customer_id user_id, MAX(os.date_created) last_order FROM $os os WHERE os.customer_id>0 AND os.status IN ('wc-completed','wc-processing') GROUP BY os.customer_id ORDER BY last_order DESC LIMIT %d",$limit);
    $rows=$wpdb->get_results($sql, ARRAY_A) ?: []; if(!$rows) return [];
    $ids=array_map(fn($r)=>(int)$r['user_id'],$rows); if(!$ids) return [];
    $in = implode(',', array_fill(0,count($ids),'%d'));
    $q = $wpdb->prepare("SELECT user_id, meta_value phone FROM {$wpdb->usermeta} WHERE meta_key='billing_phone' AND user_id IN ($in)", $ids);
    return $wpdb->get_results($q, ARRAY_A) ?: [];
  }
  // Behavioral: by category slug within days and min amount per order
  public static function buyers_of_category(string $cat_slug, int $days=60, int $min_aov=0): array {
    global $wpdb; $os=$wpdb->prefix.'wc_order_stats'; $opl=$wpdb->prefix.'wc_order_product_lookup'; $terms=$wpdb->terms;
    $sql=$wpdb->prepare("SELECT os.customer_id user_id, AVG(os.total_sales) aov FROM $os os JOIN $opl opl ON opl.order_id=os.order_id JOIN $terms t ON t.term_id=opl.product_cat_id WHERE t.slug=%s AND os.status IN ('wc-completed','wc-processing') AND os.date_created>=DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY os.customer_id HAVING aov >= %d",$cat_slug,$days,$min_aov);
    $rows=$wpdb->get_results($sql, ARRAY_A) ?: []; if(!$rows) return [];
    $ids=array_map(fn($r)=>(int)$r['user_id'],$rows); if(!$ids) return [];
    $in=implode(',', array_fill(0,count($ids),'%d'));
    $q=$wpdb->prepare("SELECT user_id, meta_value phone FROM {$wpdb->usermeta} WHERE meta_key='billing_phone' AND user_id IN ($in)", $ids);
    return $wpdb->get_results($q, ARRAY_A) ?: [];
  }
}

<?php
namespace RSMSP\Support;
class Helpers {
  public static function normalize_phone(string $mobile): string {
    $m=preg_replace('/[^0-9+]/','',$mobile);
    if(preg_match('/^09[0-9]{9}$/',$m)) $m='+98'.substr($m,1);
    if(preg_match('/^9[0-9]{9}$/',$m)) $m='+98'.$m;
    return $m;
  }
}

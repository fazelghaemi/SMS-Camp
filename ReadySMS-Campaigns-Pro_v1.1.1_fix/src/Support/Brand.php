<?php
namespace RSMSP\Support;
class Brand { public static function color(): string { return defined('RSMSP_BRAND_COLOR') ? RSMSP_BRAND_COLOR : '#00b0a4'; } }

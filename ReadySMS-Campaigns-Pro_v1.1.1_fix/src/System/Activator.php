<?php
namespace RSMSP\System; use RSMSP\Jobs\Queue;
class Activator { public static function activate(): void { DB::install(); Queue::schedule(); } }

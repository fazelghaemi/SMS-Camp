<?php
namespace RSMSP\System; use RSMSP\Jobs\Queue;
class Deactivator { public static function deactivate(): void { Queue::clear_schedule(); } }

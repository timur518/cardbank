<?php
// Периодическая очистка истории выполняется независимо от входящего трафика.
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
appStore()->prune((int)setting('HISTORY_TTL_DAYS','30'));
echo "Очистка завершена.\n";

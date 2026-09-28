#!/usr/bin/env bash
# Процесс запускается в текущем каталоге проекта; блокировка находится в bot.php.
set -euo pipefail
BOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"
cd "$BOT_DIR"
nohup "$PHP_BIN" bot.php >> storage/bot.log 2>&1 </dev/null &
echo "Запуск запрошен. Журнал: storage/bot.log"

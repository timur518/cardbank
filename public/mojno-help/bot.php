<?php
// Telegram-адаптер: один long-polling процесс, общий консультант и отдельные истории.
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
$token=setting('TELEGRAM_BOT_TOKEN');
if ($token==='') { fwrite(STDERR,"Заполните TELEGRAM_BOT_TOKEN в .env\n");exit(1); }
$processLock=fopen(__DIR__.'/storage/telegram.lock','c');
if (!$processLock || !flock($processLock,LOCK_EX|LOCK_NB)) { fwrite(STDERR,"Бот уже запущен.\n");exit(1); }
// TELEGRAM_API_PROXY_URL — необязательный адрес прокси (POST JSON {token, method, ...полезная нагрузка}),
// который отдаёт наружу ответ Telegram Bot API без изменений. Нужен, если у сервера нет
// прямого доступа к api.telegram.org. Если не задан — обращаемся к Telegram напрямую, как раньше.
$proxyUrl=rtrim(setting('TELEGRAM_API_PROXY_URL'),'/');
// Таймаут с запасом: getUpdates ждёт до 30 секунд (long polling), плюс возможные
// повторные попытки на стороне прокси при сетевых сбоях.
$http=new GuzzleHttp\Client(['timeout'=>90,'connect_timeout'=>10]);
$telegram=function(string $method,array $payload=[]) use ($http,$token,$proxyUrl): array {
    $r=$proxyUrl!==''
        ? $http->post($proxyUrl,['json'=>array_merge(['token'=>$token,'method'=>$method],$payload)])
        : $http->post('https://api.telegram.org/bot'.$token.'/'.$method,['json'=>$payload]);
    $d=json_decode((string)$r->getBody(),true,512,JSON_THROW_ON_ERROR);
    if (!($d['ok']??false)) throw new RuntimeException('Telegram API error: '.($d['description']??'unknown'));
    return $d['result'];
};
function sendReply(callable $telegram,int $chat,string $text,int $replyTo): void {
    // Запас учитывает лимит Telegram в UTF-16 для символов вне BMP.
    foreach(mb_str_split($text,1800) as $chunk) $telegram('sendMessage',['chat_id'=>$chat,'text'=>$chunk,'reply_parameters'=>['message_id'=>$replyTo,'allow_sending_without_reply'=>true]]);
}
try { $me=$telegram('getMe'); } catch(Throwable $e) { fwrite(STDERR,"Не удалось подключить Telegram. Проверьте токен и сеть.\n");exit(1); }
$offsetPath=__DIR__.'/storage/telegram-offset';
$offset=is_file($offsetPath)?(int)file_get_contents($offsetPath):0;
$store=appStore();$assistant=consultant();
echo "Консультант МОЖНО запущен.\n";
while(true) {
    try {
        // 20, а не 30: должно быть меньше REQUEST_TIMEOUT прокси (если используется),
        // иначе прокси будет обрывать long-poll раньше ответа Telegram.
        $updates=$telegram('getUpdates',['offset'=>$offset,'timeout'=>20,'allowed_updates'=>['message']]);
        $store->prune((int)setting('HISTORY_TTL_DAYS','30'));
        foreach($updates as $update) {
            try {
                $m=$update['message']??[];
                if (!isset($m['text']) || ($m['from']['is_bot']??false)) continue;
                $text=trim($m['text']);$chat=(int)$m['chat']['id'];$user=(int)$m['from']['id'];
                $private=($m['chat']['type']??'')==='private';$mention='@'.$me['username'];
                if (!$private) {
                    if (stripos($text,$mention)===false && ($m['reply_to_message']['from']['id']??null)!==$me['id']) continue;
                    $text=trim(str_ireplace($mention,'',$text));
                }
                $id='telegram:'.$chat.':'.($m['message_thread_id']??0).':'.$user;
                if (!$store->allow($id,max(1,(int)setting('RATE_LIMIT_PER_MINUTE','8')))) $answer='Слишком много запросов. Подождите минуту.';
                elseif(preg_match('/^\/start(?:@\w+)?(?:\s.*)?$/u',$text)) $answer='Здравствуйте! Я ИИ-консультант МОЖНО. Помогу разобраться с виртуальными картами, покупкой и оплатой сервисов. Что хотите оплатить? Не присылайте реквизиты и коды. Команды: /clear — очистить историю; /support — поддержка.';
                elseif(preg_match('/^\/clear(?:@\w+)?$/u',$text)) { $store->clear($id);$answer='История очищена.'; }
                elseif($text==='') $answer='Какой сервис или покупку хотите оплатить?';
                else $answer=$assistant->reply($id,$text)['reply'];
                sendReply($telegram,$chat,$answer,(int)$m['message_id']);
            } catch(InvalidArgumentException $e) { sendReply($telegram,$chat,$e->getMessage(),(int)$m['message_id']); }
            catch(Throwable $e) { error_log('mojno: telegram_update_failed ['.get_class($e).']'); }
            finally {
                // Не воспроизводится вся очередь после перезапуска; ошибку отправки видно в журнале.
                $offset=(int)$update['update_id']+1;file_put_contents($offsetPath,(string)$offset,LOCK_EX);
            }
        }
    } catch(Throwable $e) { error_log('mojno: telegram_poll_failed ['.get_class($e).']');sleep(3); }
}

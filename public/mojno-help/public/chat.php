<?php
// Веб-канал использует серверную сессию; клиент не задаёт идентификатор диалога.
declare(strict_types=1);
require __DIR__.'/../bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(int $status,array $data): never { http_response_code($status); echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit; }
try {
    $method=$_SERVER['REQUEST_METHOD'];
    if (!in_array($method,['GET','POST'],true)) { header('Allow: GET, POST');respond(405,['error'=>'Метод не поддерживается.']); }
    // Для разработки задаётся точный локальный origin; CORS намеренно не включён.
    $origin=rtrim(setting('WEB_ORIGIN'),'/');
    if ($method==='POST' && ($origin==='' || ($_SERVER['HTTP_ORIGIN']??'')!==$origin)) respond(403,['error'=>'Недопустимый источник запроса.']);
    session_name('mojno_chat');
    ini_set('session.use_strict_mode','1');
    session_set_cookie_params(['httponly'=>true,'secure'=>setting('SESSION_SECURE','1')==='1','samesite'=>'Lax','path'=>'/']);
    session_start();
    $_SESSION['conversation']??=bin2hex(random_bytes(24));
    $_SESSION['csrf']??=bin2hex(random_bytes(24));
    $conversation='web:'.$_SESSION['conversation']; $csrf=$_SESSION['csrf'];
    session_write_close();
    if ($method==='GET') respond(200,['csrf_token'=>$csrf]);
    if (!hash_equals($csrf,$_SERVER['HTTP_X_CSRF_TOKEN']??'')) respond(403,['error'=>'Обновите страницу чата.']);
    if (!str_starts_with(strtolower($_SERVER['CONTENT_TYPE']??''),'application/json')) respond(415,['error'=>'Ожидается JSON.']);
    $raw=file_get_contents('php://input',false,null,0,16385);
    if (strlen($raw)>16384) respond(413,['error'=>'Слишком большой запрос.']);
    try { $body=json_decode($raw,true,16,JSON_THROW_ON_ERROR); } catch(JsonException $e) { respond(400,['error'=>'Некорректный JSON.']); }
    if (!is_array($body)) respond(400,['error'=>'Ожидается объект.']);
    $store=appStore();$store->prune((int)setting('HISTORY_TTL_DAYS','30'));
    $ip=hash('sha256',$_SERVER['REMOTE_ADDR']??'unknown');
    if (!$store->allow('web-ip:'.$ip,max(1,(int)setting('WEB_IP_LIMIT_PER_MINUTE','20')))
        || !$store->allow('web-global',max(1,(int)setting('WEB_GLOBAL_LIMIT_PER_MINUTE','60')))
        || !$store->allow($conversation,max(1,(int)setting('RATE_LIMIT_PER_MINUTE','8')))) {
        header('Retry-After: 60');respond(429,['error'=>'Слишком много запросов. Подождите минуту.']);
    }
    if (($body['action']??'message')==='clear') {
        // Новый диалог исключает возврат старого ответа в очищенную историю.
        $store->clear($conversation);
        session_start();$_SESSION['conversation']=bin2hex(random_bytes(24));session_write_close();
        respond(200,['reply'=>'История очищена.']);
    }
    if (!is_string($body['message']??null)) respond(400,['error'=>'Нужно текстовое сообщение.']);
    respond(200,consultant()->reply($conversation,$body['message']));
} catch(InvalidArgumentException $e) { respond(422,['error'=>$e->getMessage()]); }
catch(Throwable $e) { error_log('mojno: web_failed ['.get_class($e).']');respond(503,['error'=>'Чат временно недоступен. Поддержка: https://t.me/mojno_support']); }

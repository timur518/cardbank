<?php
// Локальные проверки выполняются с подменой HTTP без платных запросов.
declare(strict_types=1);
require __DIR__.'/../bootstrap.php';
function check(bool $ok,string $label): void { if(!$ok) throw new RuntimeException($label);echo "OK: $label\n"; }
$path=tempnam(sys_get_temp_dir(),'mojno-test-');
try {
    $store=new Store($path);
    $store->pair('web:a','Вопрос A','Ответ A',4);
    $store->pair('telegram:a','Вопрос B','Ответ B',4);
    check($store->history('web:a',20)[0]['content']==='Вопрос A','Изоляция каналов');
    $store->pair('web:a','Вопрос 2','Ответ 2',4);$store->pair('web:a','Вопрос 3','Ответ 3',4);
    check(count($store->history('web:a',20))===4,'Ограничение истории');
    check($store->allow('limit',1) && !$store->allow('limit',1),'Ограничение частоты');
    $store->clear('web:a');check($store->history('web:a',20)===[],'Очистка истории');
    $requests=[];
    $mock=new GuzzleHttp\Handler\MockHandler([
        new GuzzleHttp\Psr7\Response(200,[],json_encode(['choices'=>[['message'=>['content'=>'Общий ответ из знаний модели.']]]])),
        new GuzzleHttp\Psr7\Response(200,[],'{"choices":[]}'),
        new GuzzleHttp\Psr7\Response(500,[],'{}'),
    ]);
    $stack=GuzzleHttp\HandlerStack::create($mock);$stack->push(GuzzleHttp\Middleware::history($requests));
    $bot=new Consultant($store,new GuzzleHttp\Client(['handler'=>$stack]),'test-key','gpt-4o-mini',4);
    check($bot->reply('test:smoke','Что такое виртуальная карта?')['reply']==='Общий ответ из знаний модели.','Ответ API');
    $sent=json_decode((string)$requests[0]['request']->getBody(),true);
    check($sent['messages'][0]['role']==='system' && str_contains($sent['messages'][0]['content'],'МОЖНО'),'Передача промпта');
    check($sent['store']===false,'Отключение сохранения completion');
    check($bot->reply('test:smoke','Нужен оператор')['handoff']===true && count($requests)===1,'Оператор без запроса модели');
    check($bot->reply('test:smoke','Ещё вопрос')['handoff']===true,'Пустой ответ API');
    check($bot->reply('test:smoke','Повторный вопрос')['handoff']===true,'Ошибка API');
    $invalid=false;try{$bot->reply('test:smoke','');}catch(InvalidArgumentException $e){$invalid=true;}
    check($invalid,'Пустой ввод');
    echo "Все проверки пройдены.\n";
} finally {
    unset($bot,$store);
    foreach([$path,$path.'-wal',$path.'-shm'] as $f) if(is_file($f)) unlink($f);
    $lock=__DIR__.'/../storage/'.hash('sha256','test:smoke').'.lock';if(is_file($lock))unlink($lock);
}

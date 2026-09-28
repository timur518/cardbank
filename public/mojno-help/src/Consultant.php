<?php
// Ядро не зависит от Telegram и HTTP-контроллера сайта.
declare(strict_types=1);
final class Consultant {
    public const SUPPORT='https://t.me/mojno_support';
    public function __construct(private Store $store, private GuzzleHttp\ClientInterface $http, private string $key, private string $model, private int $historyLimit=20) {}
    public function reply(string $conversation, string $message): array {
        $message=trim($message);
        if ($message==='' || mb_strlen($message)>max(1,(int)setting('MAX_MESSAGE_CHARS','3000'))) {
            throw new InvalidArgumentException('Сообщение пустое или слишком длинное.');
        }
        // Блокировка удерживает порядок сообщений только внутри одного диалога.
        $lock=fopen(__DIR__.'/../storage/'.hash('sha256',$conversation).'.lock','c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) {
            if ($lock) fclose($lock);
            return ['reply'=>'Предыдущий ответ ещё готовится. Подождите немного.','handoff'=>false,'support_url'=>self::SUPPORT];
        }
        try {
            if (preg_match('/(?:^\/support$|(?:позови|позовите|соедини|соедините|нужен|хочу)\s+(?:с\s+)?(?:жив\p{L}*\s+)?(?:оператор|человек|сотрудник)|^(?:оператор|поддержка)[!?.\s]*$)/ui',$message)) {
                $reply='Напишите специалисту поддержки: '.self::SUPPORT.'. В этом чате оператор автоматически не подключается.';
            } else {
                if ($this->key==='') throw new RuntimeException('API key missing');
                $messages=array_merge([['role'=>'system','content'=>getSystemPrompt()]],$this->store->history($conversation,$this->historyLimit),[['role'=>'user','content'=>$message]]);
                $response=$this->http->request('POST','https://api.openai.com/v1/chat/completions',[
                    'headers'=>['Authorization'=>'Bearer '.$this->key],
                    'json'=>['model'=>$this->model,'messages'=>$messages,'max_completion_tokens'=>1600,'store'=>false]]);
                $data=json_decode((string)$response->getBody(),true,512,JSON_THROW_ON_ERROR);
                $reply=$data['choices'][0]['message']['content']??null;
                if (!is_string($reply)||trim($reply)==='') throw new RuntimeException('Empty answer');
            }
            $this->store->pair($conversation,$message,$reply,$this->historyLimit);
            return ['reply'=>$reply,'handoff'=>str_contains($reply,self::SUPPORT),'support_url'=>self::SUPPORT];
        } catch(Throwable $e) {
            // Текст исключения может содержать токены и сообщения, поэтому не журналируется.
            error_log('mojno: response_failed ['.get_class($e).']');
            return ['reply'=>'Сейчас не удалось подготовить ответ. Попробуйте позже или напишите в поддержку: '.self::SUPPORT,'handoff'=>true,'support_url'=>self::SUPPORT];
        } finally { flock($lock,LOCK_UN);fclose($lock); }
    }
}

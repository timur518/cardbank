// Текст модели рендерится как ограниченный безопасный Markdown (см. renderMarkdown):
// экранируем HTML целиком, затем размечаем только конкретный список конструкций —
// произвольный HTML от модели вставить в страницу так нельзя.
const form=document.querySelector('#form'), input=document.querySelector('#message'), log=document.querySelector('#messages');
let token, busy=false;
function escapeHtml(s){return s.replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
// Поддерживаются: **жирный**, *курсив*, `код`, [текст](url), голые http(s)-ссылки,
// маркированные (-/*) и нумерованные списки, переносы строк. Всё остальное — обычный текст.
function renderMarkdown(text){
    const lines=text.split('\n');
    let html='', inList=false, listTag='';
    const inline=s=>escapeHtml(s)
        .replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,'<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
        .replace(/(^|[^"'>])\b(https?:\/\/[^\s<]+)/g,'$1<a href="$2" target="_blank" rel="noopener noreferrer">$2</a>')
        .replace(/`([^`]+)`/g,'<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>')
        .replace(/(?<!\*)\*([^*\n]+)\*(?!\*)/g,'<em>$1</em>');
    const closeList=()=>{if(inList){html+=`</${listTag}>`;inList=false;}};
    for(const raw of lines){
        const line=raw.trim();
        const bullet=line.match(/^[-*]\s+(.+)/);
        const numbered=line.match(/^\d+[.)]\s+(.+)/);
        if(bullet||numbered){
            const tag=bullet?'ul':'ol';
            if(!inList||listTag!==tag){closeList();html+=`<${tag}>`;inList=true;listTag=tag;}
            html+=`<li>${inline(bullet?bullet[1]:numbered[1])}</li>`;
        } else {
            closeList();
            html+=line===''?'<br>':`<p>${inline(line)}</p>`;
        }
    }
    closeList();
    return html;
}
function add(text,role='bot') { const p=document.createElement('div'); p.className=role;p.innerHTML=role==='bot'?renderMarkdown(text):escapeHtml(text);log.append(p);log.scrollTop=log.scrollHeight; }
function toggle(value){busy=value;document.querySelectorAll('button').forEach(b=>b.disabled=value);}
async function request(body){
    if(!token){const init=await fetch('chat.php',{credentials:'same-origin'});const data=await init.json();if(!init.ok)throw Error(data.error||'Не удалось открыть чат.');token=data.csrf_token;}
    const response=await fetch('chat.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':token},body:JSON.stringify(body)});
    const data=await response.json();if(!response.ok){if(response.status===403)token=null;throw Error(data.error||'Ошибка запроса.');}return data;
}
form.addEventListener('submit',async e=>{e.preventDefault();if(busy)return;const message=input.value.trim();if(!message)return;toggle(true);add(message,'user');input.value='';try{const data=await request({message});add(data.reply);}catch(e){add(e.message+' Поддержка: https://t.me/mojno_support');}finally{toggle(false);input.focus();}});
document.querySelector('#clear').addEventListener('click',async()=>{if(busy)return;toggle(true);try{const data=await request({action:'clear'});log.replaceChildren();add(data.reply);}catch(e){add(e.message);}finally{toggle(false);}});
// Классический чат: Enter отправляет сообщение, Shift+Enter — перенос строки; поле растёт по содержимому в пределах max-height из chat.css.
input.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form.requestSubmit();}});
input.addEventListener('input',()=>{input.style.height='auto';input.style.height=input.scrollHeight+'px';});

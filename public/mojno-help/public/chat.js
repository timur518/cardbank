// Текст модели выводится безопасно, без интерпретации HTML.
const form=document.querySelector('#form'), input=document.querySelector('#message'), log=document.querySelector('#messages');
let token, busy=false;
function add(text,role='bot') { const p=document.createElement('p'); p.className=role;p.textContent=text;log.append(p);log.scrollTop=log.scrollHeight; }
function toggle(value){busy=value;document.querySelectorAll('button').forEach(b=>b.disabled=value);}
async function request(body){
    if(!token){const init=await fetch('chat.php',{credentials:'same-origin'});const data=await init.json();if(!init.ok)throw Error(data.error||'Не удалось открыть чат.');token=data.csrf_token;}
    const response=await fetch('chat.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':token},body:JSON.stringify(body)});
    const data=await response.json();if(!response.ok){if(response.status===403)token=null;throw Error(data.error||'Ошибка запроса.');}return data;
}
form.addEventListener('submit',async e=>{e.preventDefault();if(busy)return;const message=input.value.trim();if(!message)return;toggle(true);add(message,'user');input.value='';try{const data=await request({message});add(data.reply);}catch(e){add(e.message+' Поддержка: https://t.me/mojno_support');}finally{toggle(false);input.focus();}});
document.querySelector('#clear').addEventListener('click',async()=>{if(busy)return;toggle(true);try{const data=await request({action:'clear'});log.replaceChildren();add(data.reply);}catch(e){add(e.message);}finally{toggle(false);}});

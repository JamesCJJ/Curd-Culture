(()=>{
  const apiUrl = (path)=> ((window.CakeWebroot || '/') + path.replace(/^\//,'')).replace(/\/+$/,'').replace(/([^:])\/\/+/, '$1/');

  function el(html){ const t=document.createElement('template'); t.innerHTML=html.trim(); return t.content.firstChild; }

  const ui = el(`
    <div id="copilot" class="copilot" aria-live="polite">
      <button class="copilot__toggle" aria-expanded="false" aria-controls="copilot__panel" aria-label="Open chat assistant">
        <svg class="copilot__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>
      <div id="copilot__panel" class="copilot__panel" hidden>
        <div class="copilot__head">
          <div class="copilot__title">
            <strong>🧀 Curd & Culture Assistant</strong>
            <small>Ask me about orders & products</small>
          </div>
          <div class="copilot__head-actions">
            <button class="copilot__clear" aria-label="Clear chat history" title="Clear chat">🗑️</button>
            <button class="copilot__close" aria-label="Close chat">✕</button>
          </div>
        </div>
        <div class="copilot__feed" role="log"></div>
        <form class="copilot__form" autocomplete="off">
          <input class="copilot__input" type="text" name="message" placeholder="Type your question..." required>
          <button class="copilot__send" type="submit" aria-label="Send message">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </form>
      </div>
    </div>`);

  const style = el(`<style>
    .copilot{position:fixed;right:20px;bottom:20px;z-index:9999;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .copilot__toggle{width:60px;height:60px;background:#1e3a8a;color:#fff;border:none;border-radius:50%;box-shadow:0 4px 20px rgba(30,58,138,.4);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s ease}
    .copilot__toggle:hover{background:#1e40af;transform:scale(1.05);box-shadow:0 6px 30px rgba(30,58,138,.5)}
    .copilot__toggle:active{transform:scale(.95)}
    .copilot__icon{filter:drop-shadow(0 1px 2px rgba(0,0,0,.2))}
    .copilot__panel{position:absolute;right:0;bottom:80px;width:min(380px,92vw);height:520px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.3);display:flex;flex-direction:column;overflow:hidden;opacity:0;transform:translateY(20px) scale(.95);transition:opacity .2s ease,transform .2s ease;pointer-events:none}
    .copilot__panel:not([hidden]){opacity:1;transform:translateY(0) scale(1);pointer-events:auto}
    .copilot__head{display:flex;justify-content:space-between;align-items:center;padding:.8rem 1rem;background:linear-gradient(135deg,#1e3a8a,#1e40af);color:#fff;border-bottom:1px solid rgba(255,255,255,.1)}
    .copilot__title{display:flex;flex-direction:column;gap:.1rem}
    .copilot__title strong{font-size:1rem;font-weight:600}
    .copilot__title small{font-size:.75rem;opacity:.85}
    .copilot__head-actions{display:flex;gap:.4rem}
    .copilot__clear,.copilot__close{background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;width:28px;height:28px;cursor:pointer;font-size:1rem;line-height:1;transition:background .2s;display:flex;align-items:center;justify-content:center}
    .copilot__clear:hover,.copilot__close:hover{background:rgba(255,255,255,.25)}
    .copilot__close{font-size:1.2rem}
    .copilot__feed{flex:1;overflow-y:auto;padding:1rem;background:#f9fafb;display:flex;flex-direction:column;gap:.75rem}
    .copilot__feed::-webkit-scrollbar{width:6px}
    .copilot__feed::-webkit-scrollbar-track{background:transparent}
    .copilot__feed::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}
    .copilot__msg{display:flex;animation:slideIn .3s ease}
    .copilot__msg--you{justify-content:flex-end}
    .copilot__msg--bot{justify-content:flex-start}
    .copilot__bubble{max-width:85%;padding:.6rem .9rem;border-radius:12px;word-wrap:break-word;line-height:1.5;font-size:.9rem}
    .copilot__msg--you .copilot__bubble{background:#1e3a8a;color:#fff;border-bottom-right-radius:4px}
    .copilot__msg--bot .copilot__bubble{background:#fff;color:#1f2937;border:1px solid #e5e7eb;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
    .copilot__form{display:flex;gap:.5rem;padding:.8rem;border-top:1px solid #e5e7eb;background:#fff}
    .copilot__input{flex:1;padding:.65rem .8rem;border:1px solid #d1d5db;border-radius:10px;font-size:.9rem;transition:border-color .2s}
    .copilot__input:focus{outline:none;border-color:#1e3a8a;box-shadow:0 0 0 3px rgba(30,58,138,.1)}
    .copilot__send{background:#1e3a8a;color:#fff;border:none;border-radius:10px;padding:.65rem .8rem;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center}
    .copilot__send:hover{background:#1e40af}
    .copilot__send:active{transform:scale(.95)}
    @keyframes slideIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    .theme-dark .copilot__panel{background:#0f172a;border-color:#1f2937}
    .theme-dark .copilot__feed{background:#0b1220}
    .theme-dark .copilot__msg--bot .copilot__bubble{background:#1f2937;color:#e5e7eb;border-color:#374155}
    .theme-dark .copilot__input{background:#0b1220;color:#e5e7eb;border-color:#374155}
    .theme-dark .copilot__form{background:#0f172a;border-color:#1f2937}
    .page.hc .copilot__panel{background:#0f172a;border-color:#334155}
    .page.hc .copilot__feed{background:#0b1220}
    .page.hc .copilot__msg--bot .copilot__bubble{background:#1f2937;color:#e5e7eb;border-color:#475569}
    .page.hc .copilot__input{background:#0b1220;color:#e5e7eb;border-color:#475569}
    @media (max-width:480px){.copilot__panel{height:calc(100vh - 120px);width:calc(100vw - 40px)}}
  </style>`);

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.appendChild(style);
    document.body.appendChild(ui);

    const toggle = ui.querySelector('.copilot__toggle');
    const panel = ui.querySelector('#copilot__panel');
    const closeBtn = ui.querySelector('.copilot__close');
    const clearBtn = ui.querySelector('.copilot__clear');
    const feed = ui.querySelector('.copilot__feed');
    const form = ui.querySelector('.copilot__form');
    const input = ui.querySelector('.copilot__input');

    function showPanel(show){
      panel.hidden = !show;
      toggle.setAttribute('aria-expanded', String(show));
      if (show) input.focus();
    }
    toggle.addEventListener('click', ()=> showPanel(panel.hidden));
    closeBtn.addEventListener('click', ()=> showPanel(false));

    clearBtn.addEventListener('click', ()=> {
      if (confirm('Clear all chat history?')) {
        feed.innerHTML = '';
        localStorage.removeItem('copilot_history');
        addMsg("Hi! I can help with orders and products.", 'bot');
      }
    });

    function addMsg(text, who){
      const msg = el(`<div class="copilot__msg copilot__msg--${who}"><div class="copilot__bubble"></div></div>`);
      msg.querySelector('.copilot__bubble').textContent = text;
      feed.appendChild(msg); feed.scrollTop = feed.scrollHeight;
      saveHistory();
    }

    function saveHistory(){
      const messages = [];
      feed.querySelectorAll('.copilot__msg').forEach(msg => {
        const bubble = msg.querySelector('.copilot__bubble');
        const who = msg.classList.contains('copilot__msg--you') ? 'you' : 'bot';
        messages.push({ text: bubble.textContent, who: who });
      });
      localStorage.setItem('copilot_history', JSON.stringify(messages));
    }

    function loadHistory(){
      try {
        const saved = localStorage.getItem('copilot_history');
        if (saved) {
          const messages = JSON.parse(saved);
          messages.forEach(m => {
            const msg = el(`<div class="copilot__msg copilot__msg--${m.who}"><div class="copilot__bubble"></div></div>`);
            msg.querySelector('.copilot__bubble').textContent = m.text;
            feed.appendChild(msg);
          });
          feed.scrollTop = feed.scrollHeight;
        }
      } catch(e) {
        console.error('Failed to load chat history:', e);
      }
    }

    // Load existing history or show welcome message
    loadHistory();
    if (feed.children.length === 0) {
      addMsg("Welcome to Curd & Culture! I'm here to assist you with information about our artisan cheeses, delivery options, payment methods, and order tracking. How may I help you today?", 'bot');
    }

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = input.value.trim(); if (!text) return; input.value='';
      addMsg(text, 'you');

      // Get CSRF token from meta tag or cookie
      const csrfToken = document.querySelector('meta[name="csrfToken"]')?.content ||
                        document.cookie.split('; ').find(row => row.startsWith('csrfToken='))?.split('=')[1];

      try{
        const formData = new URLSearchParams({message:text});
        const headers = {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfToken) {
          headers['X-CSRF-Token'] = csrfToken;
        }

          const res = await fetch(window.CopilotTalkUrl, {

          method:'POST',
          headers: headers,
          body: formData,
          credentials: 'same-origin'
        });

        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }

        const data = await res.json();
        addMsg(data.reply || 'No response received.', 'bot');

        // If there's a product link, open it automatically
        if (data.data && data.data.open_url) {
          setTimeout(() => {
            window.open(data.data.open_url, '_blank');
          }, 500);
        }
      }catch(err){
        console.error('Copilot error:', err);
        addMsg('Sorry, I had trouble reaching the server. Please try again.', 'bot');
      }
    });
  });
})();




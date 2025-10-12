// webroot/js/accessibility.js
(function(){

    if (window.__A11Y_INIT__) return;
    window.__A11Y_INIT__ = true;

    const setCookie = (k, v) => {
        document.cookie = `${k}=${encodeURIComponent(v)}; Max-Age=${180*24*60*60}; Path=/`;
    };
    const getCookie = (k) => document.cookie
        .split(';')
        .reduce((a,c)=>{const [K,V]=c.trim().split('='); a[K]=decodeURIComponent(V||''); return a;}, {})[k];

    const clamp = (n, min, max) => Math.min(max, Math.max(min, n));
    const contentEls = () => Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));

    function applyFontScale(scale){
        const s = clamp(parseFloat(scale||1)||1, 0.9, 1.25);
        contentEls().forEach(el => el.style.fontSize = (16 * s) + 'px');
        setCookie('pref_font_scale', String(Number(s.toFixed(2))));
        const rng = document.querySelector('input[name="font_scale"]');
        const lab = document.getElementById('font-val');
        if (rng) rng.value = s.toFixed(2);
        if (lab) lab.textContent = `(${s.toFixed(2)}×)`;
    }

    function applyContrast(mode){
        const on = (mode === 'high');
        document.body.classList.toggle('hc', on);
        setCookie('pref_contrast', on ? 'high' : 'normal');
        const sel = document.querySelector('select[name="contrast"]');
        const btn = document.getElementById('contrast-toggle');
        if (sel) sel.value = on ? 'high' : 'normal';
        if (btn) btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    }


    applyFontScale(parseFloat(getCookie('pref_font_scale') || '1') || 1);
    applyContrast(getCookie('pref_contrast') === 'high' ? 'high' : 'normal');


    const btnPlus  = document.getElementById('font-plus');
    const btnMinus = document.getElementById('font-minus');
    const btnHC    = document.getElementById('contrast-toggle');

    if (btnPlus)  btnPlus.addEventListener('click', () => {
        const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
        applyFontScale(curr + 0.05);
    });

    if (btnMinus) btnMinus.addEventListener('click', () => {
        const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
        applyFontScale(curr - 0.05);
    });

    if (btnHC)    btnHC.addEventListener('click', () => {
        const on = document.body.classList.contains('hc');
        applyContrast(on ? 'normal' : 'high');
    });
})();

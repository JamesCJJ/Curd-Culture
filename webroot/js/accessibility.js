(function () {
    const btn = document.getElementById('btn-read');
    if (!btn) return;

    if (!('speechSynthesis' in window)) {
        btn.disabled = true;
        btn.title = 'Text-to-speech not supported.';
        return;
    }

    let state = 'idle';      // idle | reading | paused
    let utter = null;

    const label = btn.querySelector('.label');

    function setState(s) {
        state = s;
        btn.classList.toggle('is-reading', s === 'reading');
        btn.classList.toggle('is-paused',  s === 'paused');
        if (label) {
            label.textContent = s === 'reading' ? 'Pause' : s === 'paused' ? 'Resume' : 'Read';
        }
        btn.setAttribute('aria-pressed', s === 'reading' ? 'true' : 'false');
    }

    function textFromPage() {
        const root = document.querySelector('main') || document.body;
        return (root.innerText || '')
            .replace(/\s+\n/g, '\n')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function chooseVoice(pref) {
        const voices = window.speechSynthesis.getVoices() || [];
        const low = s => (s || '').toLowerCase();
        return voices.find(v => low(v.lang).startsWith(low(pref)))
            || voices.find(v => low(v.lang).startsWith('en'))
            || voices[0] || null;
    }

    function start() {
        const text = textFromPage();
        if (!text) return;

        utter = new SpeechSynthesisUtterance(text);
        const pref = navigator.language || 'en-AU';
        const voice = chooseVoice(pref);
        if (voice) { utter.voice = voice; utter.lang = voice.lang || pref; } else { utter.lang = 'en-AU'; }
        utter.rate = 1.0;
        utter.pitch = 1.0;

        utter.onend = () => setState('idle');
        utter.onerror = () => setState('idle');

        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(utter);

        // force immediate visual feedback
        setState('reading');
    }

    btn.addEventListener('click', () => {
        if (state === 'idle') start();
        else if (state === 'reading') { window.speechSynthesis.pause(); setState('paused'); }
        else if (state === 'paused')  { window.speechSynthesis.resume(); setState('reading'); }
    });

    window.speechSynthesis.addEventListener('voiceschanged', function(){});
    window.addEventListener('pagehide', () => window.speechSynthesis.cancel());
    window.addEventListener('beforeunload', () => window.speechSynthesis.cancel());
})();

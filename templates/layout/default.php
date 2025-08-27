<?php
/**
 * App default layout with global topbar + a11y tools
 * templates/layout/default.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'Curd & Culture') ?></title>

    <?= $this->fetch('meta') ?>

    <?= $this->Html->css('home') ?>
    <?= $this->Html->css('app') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>

<header class="topbar" role="navigation" aria-label="Global">
    <div class="topbar__inner">
        <div class="brand">
            <?= $this->Html->link(
                $this->Html->image('logo.png', [
                    'alt' => 'Curd & Culture',
                    'class' => 'brand-logo'
                ]) . '<span class="brand-name">Curd &amp; Culture</span>',
                ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                ['escape' => false, 'class' => 'brand-link']
            ) ?>
        </div>

        <div class="nav-actions">
            <?= $this->Html->link(
                'Contact Us',
                ['prefix' => false, 'controller' => 'ContactMessages', 'action' => 'add'],
                ['class' => 'btn btn-primary', 'aria-label' => 'Go to contact form']
            ) ?>

            <?php

            $adminUser = $this->getRequest()->getSession()->read('Auth.AdminUser');
            if ($adminUser):
                echo $this->Html->link(
                    'Admin',
                    ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
                    ['class' => 'btn', 'aria-label' => 'Open admin inbox']
                );
                echo $this->Html->link(
                    'Logout',
                    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'btn', 'aria-label' => 'Logout admin']
                );
            else:
                echo $this->Html->link(
                    'Admin Login',
                    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'login'],
                    ['class' => 'btn', 'aria-label' => 'Go to admin login']
                );
            endif;
            ?>

            <!-- Read this page aloud -->
            <button id="btn-read" class="btn btn-subtle" type="button"
                    aria-pressed="false" aria-label="Read page aloud">
                <span class="glyph glyph--play" aria-hidden="true"></span>
                <span class="glyph glyph--pause-square" aria-hidden="true"></span>
                <span class="label">Read</span>
            </button>

            <!-- Accessibility tools -->
            <div class="a11y-tools" aria-label="Accessibility tools">
                <button class="btn small" id="font-plus" type="button" title="Increase font size">A+</button>
                <button class="btn small" id="font-minus" type="button" title="Decrease font size">A−</button>
                <button class="btn small" id="contrast-toggle" type="button" title="High contrast">High Contrast</button>
            </div>
        </div>
    </div>
</header>

<main id="content" class="page">
    <?= $this->fetch('content') ?>
</main>

<footer class="footer" role="contentinfo">
    <small>© <?= date('Y') ?> Curd &amp; Culture</small>
</footer>

<style>
    /* ====== Topbar ====== */
    .topbar{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid #e5e7eb}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
    .nav-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}

    .brand-link{display:flex;align-items:center;gap:.5rem;text-decoration:none}
    .brand-logo{height:28px;width:auto;border-radius:.25rem}
    .brand-name{font-weight:700;color:#0f172a}

    /* Buttons */
    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none}
    .btn:hover{filter:brightness(.98)}
    .btn:focus-visible{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .btn-primary{background:#2c7be5;color:#fff}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .small{font-size:.9rem;padding:.35rem .55rem}

    /* Read glyphs */
    .glyph{display:inline-block;width:12px;height:12px;margin-right:.35rem;vertical-align:-1px}
    .glyph--play{clip-path:polygon(0 0,100% 50%,0 100%);background:currentColor}
    .glyph--pause-square{display:none;position:relative;width:12px;height:12px;border-radius:2px;background:transparent;border:1.5px solid currentColor}
    .glyph--pause-square::before,.glyph--pause-square::after{
        content:"";position:absolute;top:2px;bottom:2px;width:2px;background:currentColor
    }
    .glyph--pause-square::before{left:3px}
    .glyph--pause-square::after{right:3px}

    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .footer{text-align:center;color:#6b7280;padding:1.25rem 1rem}

    /* ====== High contrast mode ====== */
    .page.hc{background:#0b1220;color:#e5e7eb}
    .page.hc a{color:#93c5fd}
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .page.hc .brand-name{color:#e5e7eb}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
    .page.hc .btn-subtle{background:transparent;border-color:#475569;color:#e5e7eb}

    @media (max-width:680px){
        .topbar__inner{padding:.5rem .75rem}
        .nav-actions{gap:.4rem}
    }
</style>

<script>
    /* ===== A11y tools (font size & contrast) ===== */
    (function(){
        const root = document.querySelector('.page') || document.body;
        const plus = document.getElementById('font-plus');
        const minus = document.getElementById('font-minus');
        const contrast = document.getElementById('contrast-toggle');

        let scale = parseFloat(getComputedStyle(document.documentElement).fontSize)/16 || 1;
        plus && plus.addEventListener('click', function(){
            scale = Math.min(1.25, +(scale + 0.05).toFixed(2));
            document.documentElement.style.fontSize = (16 * scale) + 'px';
        });
        minus && minus.addEventListener('click', function(){
            scale = Math.max(0.9, +(scale - 0.05).toFixed(2));
            document.documentElement.style.fontSize = (16 * scale) + 'px';
        });
        contrast && contrast.addEventListener('click', function(){
            (root.classList || document.body.classList).toggle('hc');
        });
    })();

    /* ===== Read this page aloud (SpeechSynthesis) ===== */
    (function(){
        const btn = document.getElementById('btn-read');
        if (!btn) return;

        const playIcon  = btn.querySelector('.glyph--play');
        const pauseIcon = btn.querySelector('.glyph--pause-square');

        let speaking = false;
        let utterance = null;

        function updateUI(){
            btn.setAttribute('aria-pressed', speaking ? 'true' : 'false');
            if (speaking) {
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'inline-block';
            } else {
                playIcon.style.display = 'inline-block';
                pauseIcon.style.display = 'none';
            }
        }
        updateUI();

        function buildText(){

            const region = document.getElementById('content');
            if (!region) return document.title || 'Curd and Culture';

            return (region.innerText || region.textContent || '').replace(/\s+\n/g, '\n').trim();
        }

        btn.addEventListener('click', () => {
            try {
                if (!speaking) {
                    window.speechSynthesis.cancel();
                    utterance = new SpeechSynthesisUtterance(buildText());
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    utterance.onend = () => { speaking = false; updateUI(); };
                    utterance.onerror = () => { speaking = false; updateUI(); };
                    speaking = true; updateUI();
                    window.speechSynthesis.speak(utterance);
                } else {
                    window.speechSynthesis.cancel();
                    speaking = false; updateUI();
                }
            } catch(e) {

                speaking = false; updateUI();
            }
        });


        window.addEventListener('beforeunload', ()=> {
            try { window.speechSynthesis.cancel(); } catch(e) {}
        });
    })();
</script>


<script>
    /* ===== Smooth page transitions & micro‑interactions ===== */
    (function(){
        const html = document.documentElement;
        const page = document.querySelector('.page');

        // Mark ready to trigger enter transition
        window.addEventListener('DOMContentLoaded', () => {
            html.classList.add('is-ready');

            // Flash messages: animate in & auto‑dismiss
            document.querySelectorAll('.message').forEach(msg => {
                requestAnimationFrame(() => msg.classList.add('show'));
                // Auto dismiss after 4.5s (click to close sooner)
                setTimeout(() => msg.classList.add('hidden'), 4500);
                msg.addEventListener('click', () => msg.classList.add('hidden'));
            });
        }, {once:true});

        // Topbar shadow on scroll
        const topbar = document.querySelector('.topbar');
        if (topbar){
            const onScroll = () => topbar.classList.toggle('is-scrolled', window.scrollY > 2);
            window.addEventListener('scroll', onScroll, {passive:true});
            onScroll();
        }

        // Graceful page leave on same‑origin links
        document.addEventListener('click', function(e){
            const a = e.target.closest('a');
            if (!a) return;
            if (a.hasAttribute('data-no-transition')) return;
            if (a.hasAttribute('download') || a.getAttribute('href')?.startsWith('#')) return;
            if (a.target && a.target !== '_self') return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

            const url = new URL(a.href, location.href);
            if (url.origin !== location.origin) return;

            // Skip if it's a JS link or no href
            if (!a.href || a.getAttribute('href') === 'javascript:void(0)') return;

            e.preventDefault();
            html.classList.add('is-leaving');
            setTimeout(() => { location.href = a.href; }, 120);
        });

        // Show loading veil on form submits
        document.addEventListener('submit', function(e){
            html.classList.add('is-loading');
        }, true);

        // If the browser is navigating anyway, try to cancel speech and let CSS fade do its job
        window.addEventListener('beforeunload', () => {
            try { window.speechSynthesis && window.speechSynthesis.cancel(); } catch(e) {}
        });
    })();
</script>

<?= $this->Html->script('accessibility.js') ?>
</body>
</html>

<?php
/**
 * App default layout — visual style matched to auth pages
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
<?php
$cookies   = $this->getRequest()->getCookieParams();
$theme     = $cookies['pref_theme'] ?? 'auto'; // auto | light | dark
$bodyClass = $theme === 'dark' ? 'theme-dark' : ($theme === 'light' ? 'theme-light' : '');
?>
<body class="<?= h($bodyClass) ?>">

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

            <?= $this->Html->link(
                'Products',
                ['prefix' => false, 'controller' => 'Products', 'action' => 'index'],
                ['class' => 'btn', 'aria-label' => 'Browse products']
            ) ?>

            <?= $this->Html->link(
                'Settings',
                ['prefix' => false, 'controller' => 'Settings', 'action' => 'index'],
                ['class' => 'btn', 'aria-label' => 'Open settings']
            ) ?>

            <?php
            $identity  = $this->getRequest()->getAttribute('identity');
            $adminSess = $this->getRequest()->getSession()->read('Auth.AdminUser');
            $role      = $identity ? strtolower((string)$identity->get('role')) : strtolower((string)($adminSess['role'] ?? ''));

            if ($identity || $adminSess):
                if ($role === 'admin'):
                    echo $this->Html->link(
                        'Admin',
                        ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
                        ['class' => 'btn', 'aria-label' => 'Open admin dashboard']
                    );
                endif;

                echo $this->Html->link(
                    'Logout',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'btn', 'aria-label' => 'Logout']
                );
            else:
                echo $this->Html->link(
                    'Sign in',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'login'],
                    ['class' => 'btn', 'aria-label' => 'Sign in']
                );
            endif;
            ?>

            <button id="btn-read" class="btn btn-subtle" type="button"
                    aria-pressed="false" aria-label="Read page aloud">
                <span class="glyph glyph--play" aria-hidden="true"></span>
                <span class="glyph glyph--pause-square" aria-hidden="true"></span>
                <span class="label">Read</span>
            </button>

            <div class="a11y-tools" aria-label="Accessibility tools">
                <button class="btn small" id="font-plus" type="button" title="Increase font size">A+</button>
                <button class="btn small" id="font-minus" type="button" title="Decrease font size">A−</button>
                <button class="btn small" id="contrast-toggle" type="button" title="High contrast">High Contrast</button>
            </div>
        </div>
    </div>
</header>

<main id="content" class="page">
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</main>

<footer class="footer" role="contentinfo">
    <small>© <?= date('Y') ?> Curd &amp; Culture</small>
</footer>

<style>
    /* ===== Base container (aligns with auth pages) ===== */
    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .footer{text-align:center;color:#6b7280;padding:1.25rem 1rem}

    /* ===== Topbar (geometry & tones) ===== */
    .topbar{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid #e5e7eb}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
    .nav-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}

    .brand-link{display:flex;align-items:center;gap:.5rem;text-decoration:none}
    .brand-logo{height:28px;width:auto;border-radius:.25rem}
    .brand-name{font-weight:700;color:#0f172a}

    /* ===== Buttons (keep identical rhythm as auth) ===== */
    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none;font-size:.95rem}
    .btn:hover{filter:brightness(.97)}
    .btn:focus-visible{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .small{font-size:.85rem;padding:.3rem .6rem}

    /* Prevent Contact Us stretching (exact fix) */
    .topbar .btn-primary{
        background:#2563eb;color:#fff;border-color:transparent;
        width:auto !important;display:inline-block !important;
        border-radius:.6rem;padding:.55rem .9rem;box-shadow:none;
    }

    /* Glyphs for Read */
    .glyph{display:inline-block;width:12px;height:12px;margin-right:.35rem;vertical-align:-1px}
    .glyph--play{clip-path:polygon(0 0,100% 50%,0 100%);background:currentColor}
    .glyph--pause-square{display:none;position:relative;width:12px;height:12px;border-radius:2px;background:transparent;border:1.5px solid currentColor}
    .glyph--pause-square::before,.glyph--pause-square::after{content:"";position:absolute;top:2px;bottom:2px;width:2px;background:currentColor}
    .glyph--pause-square::before{left:3px}
    .glyph--pause-square::after{right:3px}

    /* ===== Dark / HC themes (match auth palette) ===== */
    .theme-dark{background:#0b1220;color:#e5e7eb}
    .theme-dark .topbar{background:#111827;border-color:#1f2937}
    .theme-dark .brand-name{color:#e5e7eb}
    .theme-dark .btn{background:#374151;color:#f9fafb;border-color:#475569}
    .theme-dark .btn-subtle{background:transparent;border-color:#475569;color:#e5e7eb}
    .theme-dark .topbar .btn-primary{background:#60a5fa;color:#111}
    .theme-dark .footer{color:#cbd5e1}

    .page.hc{background:#0b1220;color:#e5e7eb}
    .page.hc a{color:#93c5fd}
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .page.hc .brand-name{color:#e5e7eb}
    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .topbar .btn-primary{background:#60a5fa;color:#111}

    @media (max-width:680px){
        .topbar__inner{padding:.5rem .75rem}
        .nav-actions{gap:.4rem}
    }
</style>

<script>
    /* A11y tools */
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

    /* Read aloud */
    (function(){
        const btn = document.getElementById('btn-read');
        if (!btn) return;
        const playIcon  = btn.querySelector('.glyph--play');
        const pauseIcon = btn.querySelector('.glyph--pause-square');
        let speaking = false, utterance = null;

        function updateUI(){
            btn.setAttribute('aria-pressed', speaking ? 'true' : 'false');
            playIcon.style.display = speaking ? 'none' : 'inline-block';
            pauseIcon.style.display = speaking ? 'inline-block' : 'none';
        }
        updateUI();

        function buildText(){
            const region = document.getElementById('content');
            return region ? (region.innerText || region.textContent || '').trim() : document.title;
        }

        btn.addEventListener('click', () => {
            try{
                if(!speaking){
                    window.speechSynthesis.cancel();
                    utterance = new SpeechSynthesisUtterance(buildText());
                    utterance.rate = 1; utterance.pitch = 1;
                    utterance.onend = () => { speaking = false; updateUI(); };
                    utterance.onerror = () => { speaking = false; updateUI(); };
                    speaking = true; updateUI();
                    window.speechSynthesis.speak(utterance);
                } else {
                    window.speechSynthesis.cancel();
                    speaking = false; updateUI();
                }
            }catch(e){ speaking = false; updateUI(); }
        });
        window.addEventListener('beforeunload', () => { try{ window.speechSynthesis.cancel(); }catch(e){} });
    })();

    /* Flash auto-dismiss */
    (function(){
        const html = document.documentElement;
        window.addEventListener('DOMContentLoaded', () => {
            html.classList.add('is-ready');
            document.querySelectorAll('.message').forEach(msg => {
                requestAnimationFrame(() => msg.classList.add('show'));
                setTimeout(() => msg.classList.add('hidden'), 4500);
                msg.addEventListener('click', () => msg.classList.add('hidden'));
            });
        }, {once:true});
    })();
</script>

<?= $this->Html->script('accessibility.js') ?>
</body>
</html>

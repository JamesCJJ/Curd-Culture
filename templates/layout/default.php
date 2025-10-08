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

    <?= $this->Html->meta('csrfToken', $this->getRequest()->getAttribute('csrfToken')) ?>
    <?= $this->fetch('meta') ?>

    <?= $this->Html->css('home') ?>
    <?= $this->Html->css('app') ?>
    <?= $this->fetch('css') ?>
    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('script') ?>
</head>
<?php
$cookies   = $this->getRequest()->getCookieParams();
$theme     = $cookies['pref_theme'] ?? 'auto';
$bodyClass = $theme === 'dark' ? 'theme-dark' : ($theme === 'light' ? 'theme-light' : '');

$identity  = $this->getRequest()->getAttribute('identity');
$role      = $identity ? strtolower((string)$identity->get('role')) : '';

$cartQty = 0;
if ($identity && $role === 'customer') {
    try {
        $locator   = \Cake\ORM\TableRegistry::getTableLocator();
        $Carts     = $locator->get('Carts');
        $CartItems = $locator->get('CartItems');

        $cart = $Carts->find()
            ->select(['id'])
            ->where(['user_id' => (int)$identity->get('id'), 'status' => 'open'])
            ->first();

        if ($cart) {
            $cartQty = $CartItems->find()
                ->where(['cart_id' => $cart->id])
                ->count(); // row count
        }
    } catch (\Throwable $e) {
        $cartQty = 0;
    }
}
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
            <?php
            $currentController = $this->request->getParam('controller');
            $currentPrefix     = $this->request->getParam('prefix');

            $identity = $this->getRequest()->getAttribute('identity');
            $isAdmin  = ($identity && strtolower((string)$identity->get('role')) === 'admin');
            ?>

            <?= $this->Html->link(
                'Contact Us',
                ['prefix' => false, 'controller' => 'ContactMessages', 'action' => 'add'],
                ['class' => 'btn' . ($currentController === 'ContactMessages' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Go to contact form']
            ) ?>

            <?php if (!$isAdmin): ?>
                <?= $this->Html->link(
                    'Products',
                    ['prefix' => false, 'controller' => 'Products', 'action' => 'index'],
                    ['class' => 'btn' . ($currentController === 'Products' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Browse products']
                ) ?>
            <?php endif; ?>

            <?php if ($identity && strtolower((string)$identity->get('role')) === 'customer'): ?>
                <?= $this->Html->link(
                    '<span class="cart-icon" aria-hidden="true"></span><span class="label">Cart</span>' .
                    (!empty($cartQty) ? '<span class="cart-badge">'.(int)$cartQty.'</span>' : ''),
                    ['prefix' => false, 'controller' => 'Cart', 'action' => 'index'],
                    ['escape' => false, 'class' => 'btn btn-subtle cart-link' . ($currentController === 'Cart' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Open shopping cart']
                ) ?>
            <?php endif; ?>

            <?php
            if ($isAdmin):
                echo $this->Html->link(
                    'Admin',
                    ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
                    ['class' => 'btn' . ($currentPrefix === 'Admin' ? ' btn-primary' : ''), 'aria-label' => 'Open admin dashboard']
                );
                echo $this->Html->link(
                    'Logout',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'btn', 'aria-label' => 'Admin logout']
                );
            endif;

            $role = $identity ? strtolower((string)$identity->get('role')) : '';
            if ($identity && $role === 'customer'):
                echo $this->Html->link(
                    'My Account',
                    ['prefix' => false, 'controller' => 'Customer', 'action' => 'index'],
                    ['class' => 'btn' . ($currentController === 'Customer' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Go to customer dashboard']
                );
            elseif (!$isAdmin):
                echo $this->Html->link(
                    'My Account',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'login'],
                    ['class' => 'btn' . ($currentController === 'Users' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Sign in to your account']
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

<footer class="site-footer" role="contentinfo">
    <div class="footer-content">
        <div class="footer-grid">
            <div class="footer-section">
                <div class="footer-brand">
                    <?= $this->Html->image('logo.png', ['alt' => 'Curd & Culture', 'class' => 'footer-logo']) ?>
                    <h3>Curd &amp; Culture</h3>
                </div>
                <p class="footer-tagline">Premium artisan cheese, handcrafted with love since 1985. From our family farm to your table.</p>
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook" title="Follow us on Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram" title="Follow us on Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 3.675c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162z"/></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter" title="Follow us on Twitter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h4 class="footer-heading">Shop</h4>
                <ul class="footer-links">
                    <li><?= $this->Html->link('All Cheeses', ['prefix' => false, 'controller' => 'Products', 'action' => 'index']) ?></li>
                    <li><?= $this->Html->link('New Arrivals', ['prefix' => false, 'controller' => 'Products', 'action' => 'index']) ?></li>
                    <li><?= $this->Html->link('Best Sellers', ['prefix' => false, 'controller' => 'Products', 'action' => 'index']) ?></li>
                    <li><?= $this->Html->link('Gift Sets', ['prefix' => false, 'controller' => 'Products', 'action' => 'index']) ?></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-heading">Customer Service</h4>
                <ul class="footer-links">
                    <li><?= $this->Html->link('Contact Us', ['prefix' => false, 'controller' => 'ContactMessages', 'action' => 'add']) ?></li>
                    <li><?= $this->Html->link('Delivery Info', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?></li>
                    <li><?= $this->Html->link('My Account', ['prefix' => false, 'controller' => 'Users', 'action' => 'login']) ?></li>
                    <li><?= $this->Html->link('Track Order', ['prefix' => false, 'controller' => 'Customer', 'action' => 'orders']) ?></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-heading">About</h4>
                <ul class="footer-links">
                    <li><?= $this->Html->link('Our Story', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?></li>
                    <li><?= $this->Html->link('Our Farm', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?></li>
                    <li><?= $this->Html->link('Sustainability', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?></li>
                    <li><?= $this->Html->link('Privacy Policy', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']) ?></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="footer-copyright">&copy; <?= date('Y') ?> Curd &amp; Culture. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<style>
    :root{
        --nav-font-px:14px;
        --nav-pad-x:12px;
        --nav-pad-y:10px;
        --nav-radius:12px;
    }

    /* Content wrapper */
    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem;position:relative;z-index:1}

    /* -------- Topbar: decoupled from A+/A− (uses px) -------- */
    .topbar,
    .topbar *{
        font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
    }
    .topbar{
        position:sticky;top:0;z-index:1001;background:#fff;border-bottom:1px solid #e5e7eb;
        font-size:var(--nav-font-px)!important;line-height:1;
    }
    .topbar__inner{
        max-width:1100px;margin:0 auto;padding:8px 16px;
        display:flex;align-items:center;justify-content:space-between;gap:8px;
    }
    .brand-link{display:flex;align-items:center;gap:8px;text-decoration:none;white-space:nowrap}
    .brand-logo{height:28px;width:auto;border-radius:4px}
    .brand-name{font-weight:800;color:#0f172a;font-size:var(--nav-font-px);letter-spacing:.2px}
    .theme-dark .brand-name{color:#e5e7eb}

    /* only navbar buttons */
    .topbar .btn{
        display:inline-flex;align-items:center;justify-content:center;
        padding:var(--nav-pad-y) var(--nav-pad-x);
        border-radius:var(--nav-radius);border:1px solid #d1d5db;background:#fff;color:#111;
        font-size:var(--nav-font-px)!important;line-height:1!important;text-decoration:none;white-space:nowrap;
        box-shadow:none;transition:filter .15s ease;
    }
    .topbar .btn:hover{filter:brightness(.98)}
    .topbar .btn-subtle{background:transparent}
    .topbar .small{font-size:12px!important;padding:6px 10px}
    .topbar .btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
    .theme-dark .topbar{background:#111827;border-color:#1f2937}
    .theme-dark .topbar .btn{background:#374151;color:#f9fafb;border-color:#475569}
    .theme-dark .topbar .btn-primary{background:#60a5fa;color:#111;border-color:#60a5fa}


    .nav-actions{
        display:flex;align-items:center;gap:8px;flex-wrap:nowrap;
        overflow:auto hidden;-webkit-overflow-scrolling:touch;scrollbar-width:none;
        max-width:unset;
    }
    .nav-actions::-webkit-scrollbar{display:none}


    @media (max-width:600px){
        .topbar__inner{flex-wrap:wrap;align-items:flex-start;gap:6px 8px}
        .brand{flex:1 0 100%}
        .nav-actions{
            flex:1 0 100%;max-width:100%;
            overflow:visible;flex-wrap:wrap;gap:6px
        }
        .nav-actions .btn{flex:0 0 auto;padding:10px 12px}
        #btn-read, .a11y-tools{order:2}
        .a11y-tools{width:100%;display:flex;gap:6px;justify-content:flex-start}
    }

    .glyph{display:inline-block;width:12px;height:12px;margin-right:.35rem;vertical-align:-1px}
    .glyph--play{clip-path:polygon(0 0,100% 50%,0 100%);background:currentColor}
    .glyph--pause-square{display:none;position:relative;width:12px;height:12px;border-radius:2px;background:transparent;border:1.5px solid currentColor}
    .glyph--pause-square::before,.glyph--pause-square::after{content:"";position:absolute;top:2px;bottom:2px;width:2px;background:currentColor}
    .glyph--pause-square::before{left:3px}.glyph--pause-square::after{right:3px}

    /* High Contrast bits affecting header colors only */
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .page.hc .brand-name{color:#e5e7eb}
    .page.hc .topbar .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .topbar .btn-primary{background:#60a5fa;color:#111}

    /* ----- Footer (unchanged) ----- */
    .site-footer{background:#1f2937;color:#e5e7eb;margin-top:auto}
    .footer-content{max-width:1200px;margin:0 auto;padding:3rem 2rem 1.5rem}
    .footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:3rem;margin-bottom:3rem}
    .footer-brand{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem}
    .footer-logo{width:36px;height:36px;border-radius:6px}
    .footer-brand h3{font-size:1.25rem;font-weight:700;color:#fff;margin:0}
    .footer-tagline{color:#9ca3af;line-height:1.6;margin:0 0 1.5rem;max-width:320px}
    .footer-social{display:flex;gap:.75rem}
    .social-link{display:flex;align-items:center;justify-content:center;width:40px;height:40px;background:#374151;border-radius:8px;color:#e5e7eb;transition:all .2s}
    .social-link:hover{background:#f59e0b;color:#fff;transform:translateY(-2px)}
    .footer-heading{font-size:1rem;font-weight:700;color:#fff;margin:0 0 1rem;text-transform:uppercase;letter-spacing:.5px}
    .footer-links{list-style:none;padding:0;margin:0}
    .footer-links li{margin-bottom:.75rem}
    .footer-links a{color:#9ca3af;text-decoration:none;transition:color .2s;font-size:.9375rem}
    .footer-links a:hover{color:#f59e0b}
    .footer-bottom{border-top:1px solid #374151;padding-top:1.5rem}
    .footer-bottom-content{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
    .footer-copyright{color:#9ca3af;font-size:.875rem;margin:0}

    @media (max-width:1024px){.footer-grid{grid-template-columns:1fr 1fr;gap:2rem}}
    @media (max-width:640px){
        .footer-content{padding:2rem 1.5rem 1rem}
        .footer-grid{grid-template-columns:1fr;gap:2rem;margin-bottom:2rem}
        .footer-bottom-content{flex-direction:column;align-items:flex-start}
    }

    /* Cart badge */
    .cart-link{position:relative;display:inline-flex;align-items:center;gap:.35rem}
    .cart-icon{width:16px;height:14px;border:1.5px solid currentColor;border-radius:3px;position:relative;display:inline-block}
    .cart-icon::before{content:"";position:absolute;left:2px;top:-6px;width:12px;height:6px;border:1.5px solid currentColor;border-bottom:none;border-radius:3px 3px 0 0}
    .cart-badge{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;line-height:18px;padding:0 6px;border-radius:9px;background:#ef4444;color:#fff;font-size:12px;font-weight:700;text-align:center}
    .theme-dark .cart-badge{background:#f87171;color:#111}
    .page.hc .cart-badge{background:#fca5a5;color:#111}

    /* ---- Fix AI/copilot overlay eating taps ---- */
    #copilot, .copilot, .ai-live-popup, .copilot-mask, [data-ai-overlay], .grammarly-desktop-integration{
        pointer-events:none !important;
    }
    #copilot button, #copilot .copilot-fab, .copilot button, .copilot .copilot-fab, .copilot [role="button"]{
        pointer-events:auto !important;
    }
    /* mobile: hide copilot completely */
    @media (max-width:900px){
        #copilot, .copilot{ display:none !important; }
    }

</style>

<script>
    (function(){
        const root = document.querySelector('.page') || document.body;
        const plus = document.getElementById('font-plus');
        const minus = document.getElementById('font-minus');
        const contrast = document.getElementById('contrast-toggle');

        // restore high contrast
        const isHighContrast = localStorage.getItem('highContrast') === 'true';
        if (isHighContrast) root.classList.add('hc');

        // font scaling (rem)
        let scale = parseFloat(localStorage.getItem('fontSize')) || 1;
        if (scale !== 1) document.documentElement.style.fontSize = (16 * scale) + 'px';

        plus && plus.addEventListener('click', function(){
            scale = Math.min(1.25, +(scale + 0.05).toFixed(2));
            document.documentElement.style.fontSize = (16 * scale) + 'px';
            localStorage.setItem('fontSize', scale);
        });
        minus && minus.addEventListener('click', function(){
            scale = Math.max(0.9, +(scale - 0.05).toFixed(2));
            document.documentElement.style.fontSize = (16 * scale) + 'px';
            localStorage.setItem('fontSize', scale);
        });

        contrast && contrast.addEventListener('click', function(){
            root.classList.toggle('hc');
            localStorage.setItem('highContrast', root.classList.contains('hc'));
        });
    })();

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
<?= $this->Html->script('copilot.js') ?>
</body>
</html>

<?php
/**
 * App default layout (global, safer)
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
    <?= $this->Html->css('overlay-guard') ?>

    <!-- Failsafe: make content visible no matter what -->
    <script>
        // If your CSS used `html.is-ready .page{opacity:1}`, set it right away.
        document.documentElement.classList.add('is-ready');
    </script>

    <!-- Apply user preferences from cookies (guarded) -->
    <script>
        (function () {
            try {

                const C = document.cookie.split(';').reduce((m, c) => {
                    const s = c.trim(); if (!s) return m;
                    const i = s.indexOf('=');
                    const k = decodeURIComponent(i >= 0 ? s.slice(0, i) : s);
                    const v = decodeURIComponent(i >= 0 ? s.slice(i + 1) : '');
                    m[k] = v; return m;
                }, {});


                const clamp = (n, lo, hi) => Math.min(hi, Math.max(lo, n));
                const applyContentFontScale = (s) => {
                    const sc = clamp(parseFloat(s || '1') || 1, 0.9, 1.25);
                    document.querySelectorAll('.page, .dashboard-content, .admin-content')
                        .forEach(el => el.style.fontSize = (16 * sc) + 'px');
                };

                const contrast = (C.pref_contrast || 'normal');
                const theme    =  C.pref_theme || 'auto';
                const fs       =  parseFloat(C.pref_font_scale || '1.0');

                const applyTheme = (t) => {
                    if (!document.body) return;
                    if (t === 'dark') {
                        document.body.classList.add('theme-dark');
                        document.body.classList.remove('theme-light');
                    } else if (t === 'light') {
                        document.body.classList.add('theme-light');
                        document.body.classList.remove('theme-dark');
                    } else {
                        const prefersDark = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                        document.body.classList.toggle('theme-dark', !!prefersDark);
                        document.body.classList.toggle('theme-light', !prefersDark);
                    }
                };


                document.addEventListener('DOMContentLoaded', function(){
                    try {
                        applyTheme(theme);

                        const page = document.querySelector('.page') || document.body;
                        if (contrast === 'high') page.classList.add('hc');

                        if (!isNaN(fs) && fs !== 1.0) applyContentFontScale(fs);
                    } catch (_) {}
                });
            } catch (_) {}
        })();
    </script>

    <?= $this->fetch('css') ?>
    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('script') ?>
</head>
<?php
$cookies   = $this->getRequest()->getCookieParams();
$theme = $cookies['pref_theme'] ?? 'auto';
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
            $cartQty = $CartItems->find()->where(['cart_id' => $cart->id])->count();
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
                $this->Html->image('logo.png', ['alt' => 'Curd & Culture','class' => 'brand-logo'])
                . '<span class="brand-name">Curd &amp; Culture</span>',
                ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home'],
                ['escape' => false, 'class' => 'brand-link']
            ) ?>
        </div>

        <div class="nav-actions">
            <?php
            $currentController = $this->request->getParam('controller');
            $currentPrefix     = $this->request->getParam('prefix');
            $identity          = $this->getRequest()->getAttribute('identity');
            $isAdmin           = ($identity && strtolower((string)$identity->get('role')) === 'admin');
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
            if ($isAdmin) {
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
            }

            $role = $identity ? strtolower((string)$identity->get('role')) : '';
            if ($identity && $role === 'customer') {
                echo $this->Html->link(
                    'My Account',
                    ['prefix' => false, 'controller' => 'Customer', 'action' => 'index'],
                    ['class' => 'btn' . ($currentController === 'Customer' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Go to customer dashboard']
                );
            } elseif (!$isAdmin) {
                echo $this->Html->link(
                    'My Account',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'login'],
                    ['class' => 'btn' . ($currentController === 'Users' && !$currentPrefix ? ' btn-primary' : ''), 'aria-label' => 'Sign in to your account']
                );
            }
            ?>

            <button id="btn-read" class="btn btn-subtle" type="button" aria-pressed="false" aria-label="Read page aloud">
                <span class="glyph glyph--play" aria-hidden="true"></span>
                <span class="glyph glyph--pause-square" aria-hidden="true"></span>
                <span class="label">Read</span>
            </button>

            <div class="a11y-tools" aria-label="Accessibility tools">
                <button class="btn small" id="font-plus"  type="button" title="Increase font size">A+</button>
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
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
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
        --nav-radius:12px;
        --nav-h:40px;
        --nav-h-sm:32px;
        --z-modal:1070;
        --z-modal-backdrop:1060;
        --z-header:1030;
        --z-floating:1020;
    }

    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}

    /* Topbar uses rem so root font-size scaling takes effect */
    .topbar,.topbar *{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif}
    .topbar{position:sticky;top:0;z-index:var(--z-header);background:#fff;border-bottom:1px solid #e5e7eb;font-size:.875rem;line-height:1}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:8px 16px;display:flex;align-items:center;justify-content:space-between;gap:8px}
    .brand-link{display:flex;align-items:center;gap:8px;text-decoration:none;white-space:nowrap}
    .brand-logo{height:28px;width:auto;border-radius:4px}
    .brand-name{font-weight:800;color:#0f172a;font-size:.875rem}
    .nav-actions{flex:1 1 auto;display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;min-width:0}

    .topbar .btn{
        display:inline-flex;align-items:center;justify-content:center;
        height:var(--nav-h);min-height:var(--nav-h);padding:0 14px;border-radius:var(--nav-radius);
        border:1px solid #d1d5db;background:#fff;color:#111;font-size:.875rem;
        white-space:nowrap;flex:0 0 auto;line-height:1;text-decoration:none;box-shadow:none;transition:filter .15s
    }
    .topbar .btn:hover{filter:brightness(.98)}
    .topbar .btn-subtle{background:transparent}
    .topbar .btn-primary{background:#2563eb;border-color:#2563eb;color:#fff}
    .topbar .btn.small,.a11y-tools .btn{height:var(--nav-h-sm);min-height:var(--nav-h-sm);padding:0 10px;font-size:.8125rem}

    @media (max-width:600px){
        .topbar__inner{flex-wrap:wrap;align-items:flex-start;gap:6px 8px}
        .brand{flex:1 0 100%}
        .nav-actions{flex:1 0 100%;justify-content:flex-start}
    }

    .glyph{display:inline-block;width:12px;height:12px;margin-right:.35rem;vertical-align:-1px}
    .glyph--play{clip-path:polygon(0 0,100% 50%,0 100%);background:currentColor}
    .glyph--pause-square{display:none;position:relative;width:12px;height:12px;border-radius:2px;border:1.5px solid currentColor}
    .glyph--pause-square::before,.glyph--pause-square::after{content:"";position:absolute;top:2px;bottom:2px;width:2px;background:currentColor}
    .glyph--pause-square::before{left:3px}.glyph--pause-square::after{right:3px}

    .cart-link{position:relative;display:inline-flex;align-items:center;gap:.35rem}
    .cart-icon{width:16px;height:14px;border:1.5px solid currentColor;border-radius:3px;position:relative;display:inline-block}
    .cart-icon::before{content:"";position:absolute;left:2px;top:-6px;width:12px;height:6px;border:1.5px solid currentColor;border-bottom:none;border-radius:3px 3px 0 0}
    .cart-badge{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;line-height:18px;padding:0 6px;border-radius:9px;background:#ef4444;color:#fff;font-size:.75rem;font-weight:700;text-align:center}

    /* Theme / high-contrast (existing) */
    .theme-dark .topbar{background:#111827;border-color:#1f2937}
    .theme-dark .topbar .btn{background:#374151;color:#f9fafb;border-color:#475569}
    .theme-dark .topbar .btn-primary{background:#60a5fa;color:#111;border-color:#60a5fa}
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .page.hc .brand-name{color:#e5e7eb}
    .page.hc .topbar .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .topbar .btn-primary{background:#60a5fa;color:#111}

    /* Footer */
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

    /* Modal z-index fix */
    body .modal { position: fixed; z-index: 3000 !important; }
    body .modal-dialog { z-index: 3001 !important; }
    body .modal-backdrop {
        z-index: 2990 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        background: rgba(0,0,0,.45) !important;
        opacity: .45 !important;
    }

    /* Safety: content must never be hidden by default */
    .page { opacity: 1 !important; filter: none !important; -webkit-filter:none !important; }
    /* ============================
   High Contrast – global tokens
   Scope: .hc on <body> or .page
   ============================ */
    .hc {
        /* dark surfaces + bright text with AAA-ish contrast on common UIs */
        --hc-bg:        #0b111b;    /* page background */
        --hc-surface:   #0f172a;    /* cards / panels / inputs */
        --hc-border:    #3b455a;    /* neutral borders */
        --hc-text:      #f5f7fa;    /* body text */
        --hc-muted:     #cdd6e1;    /* secondary text */
        --hc-link:      #9dd1ff;    /* links (always underlined) */
        --hc-primary:   #5fb0ff;    /* primary button/brand */
        --hc-primary-t: #08101b;    /* primary text on button */
        --hc-accent:    #ffd166;    /* focus ring/alerts accent */
        --hc-danger:    #ff6b6b;
        --hc-success:   #22d3a6;
        color-scheme: dark;
    }

    /* Base & typography */
    .hc,
    .hc .page,
    .hc body {
        background: var(--hc-bg) !important;
        color: var(--hc-text) !important;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .hc * { text-shadow: none !important; }

    /* Headings slightly heavier for legibility */
    .hc h1, .hc h2, .hc h3, .hc .page-title { color: var(--hc-text); font-weight: 750; }
    .hc .text-muted, .hc .hint, .hc .form-text, .hc .small { color: var(--hc-muted) !important; }

    /* Links: brighter + underline with offset for clarity */
    .hc a { color: var(--hc-link) !important; text-decoration: underline; text-underline-offset: 2px; }
    .hc a:hover { filter: brightness(1.08); }

    /* Cards / panels (Customer/Admin/Settings/Auth shared) */
    .hc .card,
    .hc .group,
    .hc .settings-card,
    .hc .auth-card,
    .hc .sec-box,
    .hc .admin-content .card,
    .hc .dashboard-content .card {
        background: var(--hc-surface) !important;
        border: 1px solid var(--hc-border) !important;
        color: var(--hc-text) !important;
    }
    .hc .card-header,
    .hc .group__title { background: transparent; color: var(--hc-text); border-bottom: 1px solid var(--hc-border); }

    /* Inputs / selects / textareas (Bootstrap + custom) */
    .hc .form-control,
    .hc .form-select,
    .hc select.input,
    .hc .auth-input,
    .hc input[type="text"],
    .hc input[type="email"],
    .hc input[type="password"],
    .hc input[type="tel"],
    .hc textarea {
        background: var(--hc-surface) !important;
        color: var(--hc-text) !important;
        border: 1px solid var(--hc-border) !important;
    }
    .hc .form-control::placeholder,
    .hc .auth-input::placeholder { color: #a7b1c0 !important; }

    /* Toggles / switches (Bootstrap & custom) */
    .hc .form-check-input { background-color: #0b1220; border-color: var(--hc-border); }
    .hc .form-check-input:checked { background-color: var(--hc-primary); border-color: var(--hc-primary); }

    /* Buttons */
    .hc .btn {
        background: #141c2b;
        color: var(--hc-text);
        border: 1px solid var(--hc-border);
    }
    .hc .btn:hover { filter: brightness(1.08); }
    .hc .btn.btn-primary,
    .hc .btn-primary {
        background: var(--hc-primary) !important;
        border-color: var(--hc-primary) !important;
        color: var(--hc-primary-t) !important;
        font-weight: 700;
    }
    .hc .btn.btn-outline,
    .hc .btn-outline-secondary,
    .hc .btn-ghost {
        background: transparent !important;
        color: var(--hc-text) !important;
        border-color: var(--hc-border) !important;
    }

    /* Focus visibility – keyboard friendly, thick & offset ring */
    .hc a:focus-visible,
    .hc button:focus-visible,
    .hc .btn:focus-visible,
    .hc .form-control:focus,
    .hc .form-select:focus,
    .hc select.input:focus,
    .hc .auth-input:focus {
        outline: 3px solid var(--hc-accent) !important;
        outline-offset: 2px !important;
        box-shadow: none !important;
    }

    /* Sidebar (Customer/Admin) */
    .hc .dashboard-sidebar,
    .hc .admin-sidebar {
        background: #060a12 !important;
        border-right: 1px solid var(--hc-border);
    }
    .hc .dashboard-nav .nav-link,
    .hc .admin-sidebar .nav-link {
        color: var(--hc-muted) !important;
        border-left: 3px solid transparent;
    }
    .hc .dashboard-nav .nav-link:hover,
    .hc .admin-sidebar .nav-link:hover {
        background: #0f172a !important;
        color: var(--hc-text) !important;
        border-left-color: var(--hc-border);
    }
    .hc .dashboard-nav .nav-link.active,
    .hc .admin-sidebar .nav-link.active {
        background: #132033 !important;
        color: var(--hc-text) !important;
        border-left-color: var(--hc-primary);
        font-weight: 700;
    }

    /* Top bar buttons in default layout */
    .hc .topbar { background: #0f172a !important; border-color: var(--hc-border) !important; }
    .hc .topbar .btn { background: #131c2c; color: var(--hc-text); border-color: var(--hc-border); }
    .hc .topbar .btn.btn-primary { background: var(--hc-primary); color: var(--hc-primary-t); border-color: var(--hc-primary); }

    /* Range slider knob is clearly visible */
    .hc input[type="range"]::-webkit-slider-thumb { background: var(--hc-primary); }
    .hc input[type="range"]::-moz-range-thumb { background: var(--hc-primary); }
    .hc input[type="range"]::-webkit-slider-runnable-track,
    .hc input[type="range"]::-moz-range-track { background: #22304a; }

    /* Alerts/badges */
    .hc .alert-info    { background:#0f2236; border-color:#284b72; color:#cfe8ff; }
    .hc .alert-success { background:#072b27; border-color:#116f62; color:#bef5ea; }
    .hc .alert-danger  { background:#3a0b13; border-color:#7a1b2b; color:#ffdfe3; }
    .hc .badge.bg-primary { background: var(--hc-primary) !important; color: var(--hc-primary-t) !important; }

    /* Tables (if any) */
    .hc .table { color: var(--hc-text); }
    .hc .table thead { color: var(--hc-text); border-bottom: 1px solid var(--hc-border); }
    .hc .table tbody tr { border-color: var(--hc-border); }
    .hc .table tbody tr:hover { background: #132033; }

    /* Small separators/HR */
    .hc hr { border-color: var(--hc-border); }

    /* Make tiny helper text a hair larger for legibility */
    @media (min-width: 0) {
        .hc .form-text, .hc .hint, .hc .small { font-size: 0.95em; }
    }

</style>

<script>
    /* Read-aloud button */
    (function(){
        const btn = document.getElementById('btn-read');
        if (!btn) return;
        const playIcon  = btn.querySelector('.glyph--play');
        const pauseIcon = btn.querySelector('.glyph--pause-square');
        let speaking = false, utterance = null;

        function updateUI(){
            btn.setAttribute('aria-pressed', speaking ? 'true' : 'false');
            playIcon.style.display  = speaking ? 'none'  : 'inline-block';
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
</script>

<!-- Accessibility tools write cookies → global effect -->
<script>
    (function(){
        // -------- helpers: cookie --------
        const setCookie = (k, v) => {
            document.cookie = `${k}=${encodeURIComponent(v)}; Max-Age=${180*24*60*60}; Path=/`;
        };
        const getCookie = (k) => {
            const map = document.cookie.split(';').reduce((a, c) => {
                const [K,V] = c.trim().split('=');
                a[K] = decodeURIComponent(V || '');
                return a;
            }, {});
            return map[k];
        };
        const clamp = (n, min, max) => Math.min(max, Math.max(min, n));


        const contentEls = Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));


        const btnContrast = document.getElementById('contrast-toggle');
        const btnPlus     = document.getElementById('font-plus');
        const btnMinus    = document.getElementById('font-minus');


        const selContrast = document.querySelector('select[name="contrast"]');
        const rngFont     = document.querySelector('input[name="font_scale"]');
        const fontValLab  = document.getElementById('font-val');

        // -------- apply functions --------
        function applyContrast(mode){
            const on = (mode === 'high');
            document.body.classList.toggle('hc', on);
            setCookie('pref_contrast', on ? 'high' : 'normal');


            if (selContrast) selContrast.value = on ? 'high' : 'normal';
            if (btnContrast) btnContrast.setAttribute('aria-pressed', on ? 'true' : 'false');
        }

        function applyFontScale(scale){
            const s = clamp(parseFloat(scale || 1) || 1, 0.9, 1.25);
            contentEls.forEach(el => el.style.fontSize = (16 * s) + 'px');
            setCookie('pref_font_scale', String(s));


            if (rngFont) rngFont.value = s.toFixed(2);
            if (fontValLab) fontValLab.textContent = '(' + s.toFixed(2) + '×)';
        }

        // -------- init from cookies --------
        applyContrast(getCookie('pref_contrast') === 'high' ? 'high' : 'normal');
        applyFontScale(parseFloat(getCookie('pref_font_scale') || '1') || 1);

        // -------- wire topbar buttons --------
        if (btnContrast) {
            btnContrast.addEventListener('click', () => {
                const turnOn = !document.body.classList.contains('hc');
                applyContrast(turnOn ? 'high' : 'normal');
            });
        }
        if (btnPlus) {
            btnPlus.addEventListener('click', () => {
                const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
                applyFontScale(curr + 0.05);
            });
        }
        if (btnMinus) {
            btnMinus.addEventListener('click', () => {
                const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
                applyFontScale(curr - 0.05);
            });
        }

        if (selContrast) selContrast.addEventListener('change', e => applyContrast(e.target.value));
        if (rngFont)     rngFont.addEventListener('input',  e => applyFontScale(e.target.value));
    })();
</script>


<?= $this->Html->script('accessibility.js') ?>
<?= $this->Html->script('copilot.js') ?>
</body>
</html>

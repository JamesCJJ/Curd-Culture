<?php
/**
 * App default layout (global, safer)
 * Uses DB/Session-based preferences (no cookies).
 */

$session = $this->getRequest()->getSession();
$prefs = $session->read('Prefs') ?: [
    'theme'       => 'auto',   // auto|light|dark
    'contrast'    => 'normal', // normal|high
    'font_scale'  => 1.0,
    'language'    => 'en',
];

$bodyThemeClass = ($prefs['theme'] === 'dark') ? 'theme-dark'
    : (($prefs['theme'] === 'light') ? 'theme-light' : '');
$pageContrastClass = ($prefs['contrast'] === 'high') ? 'hc' : '';
$inlineFontSize = (float)($prefs['font_scale'] ?? 1.0);
$inlineFontStyle = ($inlineFontSize != 1.0) ? 'font-size:' . (16 * $inlineFontSize) . 'px' : '';

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

    <!-- Failsafe: always show content -->
    <script>document.documentElement.classList.add('is-ready');</script>

    <?= $this->fetch('css') ?>
    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('script') ?>
</head>

<body class="<?= h($bodyThemeClass) ?>">

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
            $role = $identity ? strtolower((string)$identity->get('role')) : '';
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

<main id="content" class="page <?= h($pageContrastClass) ?>" style="<?= h($inlineFontStyle) ?>">
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
        --nav-radius:12px;
        --nav-h:40px;
        --nav-h-sm:32px;
        --z-modal:1070;
        --z-modal-backdrop:1060;
        --z-header:1030;
        --z-floating:1020;

        /* body text */
        --text-body:#111827; --text-muted:#6b7280;

        /* chrome (topbar/buttons) */
        --chrome-bg:#ffffff; --chrome-fg:#0f172a; --chrome-border:#e5e7eb;
        --chrome-btn-bg:#ffffff; --chrome-btn-fg:#111; --chrome-btn-border:#d1d5db;
        --chrome-btn-primary-bg:#2563eb; --chrome-btn-primary-fg:#fff; --chrome-btn-primary-border:#2563eb;

        --logo-bg:transparent; --logo-outline:transparent;
    }
    .theme-dark{
        --chrome-bg:#0f172a; --chrome-fg:#e5e7eb; --chrome-border:#334155;
        --chrome-btn-bg:#1f2937; --chrome-btn-fg:#f9fafb; --chrome-btn-border:#475569;
        --chrome-btn-primary-bg:#60a5fa; --chrome-btn-primary-fg:#111827; --chrome-btn-primary-border:#60a5fa;
        --logo-bg:#ffffff; --logo-outline:rgba(255,255,255,.25);
    }
    body, .page{ color:var(--text-body); }

    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}

    /* Topbar */
    .topbar{position:sticky;top:0;z-index:var(--z-header);background:var(--chrome-bg);border-bottom:1px solid var(--chrome-border);font-size:.875rem;line-height:1;color:var(--chrome-fg)}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:8px 16px;display:flex;align-items:center;justify-content:space-between;gap:8px}
    .brand-link{display:flex;align-items:center;gap:8px;text-decoration:none;white-space:nowrap;color:inherit}
    .brand-logo{height:28px;width:auto;border-radius:8px;background:var(--logo-bg);box-shadow:0 0 0 2px var(--logo-outline);padding:2px}
    .brand-name{font-weight:800;color:currentColor;font-size:.875rem}
    .nav-actions{flex:1 1 auto;display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;min-width:0}

    .topbar .btn{
        display:inline-flex;align-items:center;justify-content:center;
        height:var(--nav-h);min-height:var(--nav-h);padding:0 14px;border-radius:var(--nav-radius);
        border:1px solid var(--chrome-btn-border);background:var(--chrome-btn-bg);color:var(--chrome-btn-fg);font-size:.875rem;
        white-space:nowrap;flex:0 0 auto;line-height:1;text-decoration:none;box-shadow:none;transition:filter .15s
    }
    .topbar .btn:hover{filter:brightness(.98)}
    .topbar .btn-subtle{background:transparent}
    .topbar .btn-primary{background:var(--chrome-btn-primary-bg);border-color:var(--chrome-btn-primary-border);color:var(--chrome-btn-primary-fg)}
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

    /* Footer (restored dark style) */
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

    /* Safety */
    .page { opacity: 1 !important; filter: none !important; -webkit-filter:none !important; }

    /* ================= High Contrast ================= */
    .hc {
        --hc-bg:#0b111b; --hc-surface:#0f172a; --hc-border:#3b455a; --hc-text:#f5f7fa; --hc-muted:#cdd6e1;
        --hc-link:#9dd1ff; --hc-primary:#5fb0ff; --hc-primary-t:#08101b; --hc-accent:#ffd166;
        --hc-danger:#ff6b6b; --hc-success:#22d3a6;
        color-scheme: dark;
    }
    .hc, .hc .page, .hc body { background:var(--hc-bg) !important; color:var(--hc-text) !important; -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
    .hc * { text-shadow:none !important; }
    .hc h1, .hc h2, .hc h3, .hc .page-title { color:var(--hc-text); font-weight:750; }
    .hc .text-muted, .hc .hint, .hc .form-text, .hc .small { color:var(--hc-muted) !important; }
    .hc a { color:var(--hc-link) !important; text-decoration:underline; text-underline-offset:2px; }
    .hc a:hover { filter:brightness(1.08); }

    .hc .card, .hc .group, .hc .settings-card, .hc .auth-card, .hc .sec-box, .hc .admin-content .card, .hc .dashboard-content .card {
        background:var(--hc-surface) !important; border:1px solid var(--hc-border) !important; color:var(--hc-text) !important;
    }
    .hc .card-header, .hc .group__title { background:transparent; color:var(--hc-text); border-bottom:1px solid var(--hc-border); }

    .hc .form-control, .hc .form-select, .hc select.input, .hc .auth-input,
    .hc input[type="text"], .hc input[type="email"], .hc input[type="password"], .hc input[type="tel"], .hc textarea {
        background:var(--hc-surface) !important; color:var(--hc-text) !important; border:1px solid var(--hc-border) !important;
    }
    .hc .form-control::placeholder, .hc .auth-input::placeholder { color:#a7b1c0 !important; }

    .hc .form-check-input { background-color:#0b1220; border-color:var(--hc-border); }
    .hc .form-check-input:checked { background-color:var(--hc-primary); border-color:var(--hc-primary); }

    .hc .btn { background:#141c2b; color:var(--hc-text); border:1px solid var(--hc-border); }
    .hc .btn:hover { filter:brightness(1.08); }
    .hc .btn.btn-primary, .hc .btn-primary { background:var(--hc-primary) !important; border-color:var(--hc-primary) !important; color:var(--hc-primary-t) !important; font-weight:700; }
    .hc .btn.btn-outline, .hc .btn-outline-secondary, .hc .btn-ghost { background:transparent !important; color:var(--hc-text) !important; border-color:var(--hc-border) !important; }

    .hc a:focus-visible, .hc button:focus-visible, .hc .btn:focus-visible,
    .hc .form-control:focus, .hc .form-select:focus, .hc select.input:focus, .hc .auth-input:focus {
        outline:3px solid var(--hc-accent) !important; outline-offset:2px !important; box-shadow:none !important;
    }

    .hc .dashboard-sidebar, .hc .admin-sidebar { background:#060a12 !important; border-right:1px solid var(--hc-border); }
    .hc .dashboard-nav .nav-link, .hc .admin-sidebar .nav-link { color:var(--hc-muted) !important; border-left:3px solid transparent; }
    .hc .dashboard-nav .nav-link:hover, .hc .admin-sidebar .nav-link:hover { background:#0f172a !important; color:var(--hc-text) !important; border-left-color:var(--hc-border); }
    .hc .dashboard-nav .nav-link.active, .hc .admin-sidebar .nav-link.active { background:#132033 !important; color:var(--hc-text) !important; border-left-color:var(--hc-primary); font-weight:700; }

    .hc .topbar { background:var(--hc-surface) !important; border-color:var(--hc-border) !important; color:var(--hc-text) !important; }
    .hc .topbar .btn { background:#131c2c; color:var(--hc-text); border-color:var(--hc-border); }
    .hc .topbar .btn.btn-primary { background:var(--hc-primary); color:var(--hc-primary-t); border-color:var(--hc-primary); }

    .hc .site-footer { background:var(--hc-surface) !important; color:var(--hc-text) !important; border-top:1px solid var(--hc-border) !important; }
    .hc .footer-links a { color:var(--hc-link) !important; }

    .hc input[type="range"]::-webkit-slider-thumb { background:var(--hc-primary); }
    .hc input[type="range"]::-moz-range-thumb { background:var(--hc-primary); }
    .hc input[type="range"]::-webkit-slider-runnable-track, .hc input[type="range"]::-moz-range-track { background:#22304a; }

    .hc .alert-info    { background:#0f2236; border-color:#284b72; color:#cfe8ff; }
    .hc .alert-success { background:#072b27; border-color:#116f62; color:#bef5ea; }
    .hc .alert-danger  { background:#3a0b13; border-color:#7a1b2b; color:#ffdfe3; }

    .hc .table { color:var(--hc-text); }
    .hc .table thead { color:var(--hc-text); border-bottom:1px solid var(--hc-border); }
    .hc .table tbody tr { border-color:var(--hc-border); }
    .hc .table tbody tr:hover { background:#132033; }

    .hc hr { border-color:var(--hc-border); }
    .hc .brand-logo{ --logo-bg:#ffffff; --logo-outline: var(--hc-border); }

    /* ===== Homepage visibility & contrast (dark/hc + light) ===== */
    .home .hero,
    .theme-dark .home .hero { position: relative; }
    .home .hero h1, .home .hero .title { color:#0f172a; text-shadow:none; opacity:1 !important; }
    .theme-dark .home .hero h1, .theme-dark .home .hero .title,
    .hc .home .hero h1,        .hc .home .hero .title,
    .home.hc .hero h1,         .home.hc .hero .title { color:#f7fafc !important; text-shadow:0 1px 0 rgba(0,0,0,.35); }
    .home .hero .lead { color:#334155; }
    .theme-dark .home .hero .lead, .hc .home .hero .lead, .home.hc .hero .lead { color:#e6edf5 !important; }
    .home .hero .badge, .home .hero .stat-card {
        background:rgba(255,255,255,.9); border:1px solid rgba(15,23,42,.08); box-shadow:0 6px 24px rgba(2,6,23,.08); color:#0f172a;
    }
    .theme-dark .home .hero .badge, .theme-dark .home .hero .stat-card,
    .hc .home .hero .badge,        .hc .home .hero .stat-card,
    .home.hc .hero .badge,         .home.hc .hero .stat-card {
        background:#131c2c !important; border:1px solid #243047 !important; color:#f5f7fa !important; box-shadow:0 10px 40px rgba(8,15,27,.35);
    }

    .home .section--value, .home .benefits, .home .features-band{
        background:#f8fbff; border:1px solid #e6eef6; border-radius:18px; box-shadow:0 20px 60px rgba(2,6,23,.06);
        padding:clamp(16px, 3vw, 28px);
    }
    .theme-dark .home .section--value, .hc .home .section--value, .home.hc .section--value{
        background:#0f172a !important; border:1px solid #243047 !important; box-shadow:0 18px 60px rgba(8,15,27,.35);
    }
    .home .section--value .card, .home .benefits .card, .home .features-band .card{
        background:#ffffff; border:1px solid #e6eef6; border-radius:16px; box-shadow:0 10px 30px rgba(2,6,23,.06);
    }
    .theme-dark .home .section--value .card, .hc .home .section--value .card, .home.hc .section--value .card,
    .theme-dark .home .benefits .card,     .hc .home .benefits .card,
    .theme-dark .home .features-band .card,.hc .home .features-band .card{
        background:#0b1220 !important; border:1px solid #243047 !important;
    }
    .home .section--value .card h4, .home .benefits .card h4 { color:#0f172a; font-weight:700; }
    .theme-dark .home .section--value .card h4, .hc .home .section--value .card h4, .home.hc .section--value .card h4,
    .theme-dark .home .benefits .card h4,     .hc .home .benefits .card h4 { color:#eef3fb !important; }
    .home .section--value .card p, .home .benefits .card p { color:#334155; }
    .theme-dark .home .section--value .card p, .hc .home .section--value .card p,
    .theme-dark .home .benefits .card p,     .hc .home .benefits .card p { color:#cdd6e1 !important; }

    .home .section--process{
        background:#f8fbff; border:1px solid #e6eef6; border-radius:18px; padding:clamp(16px, 3vw, 28px); box-shadow:0 20px 60px rgba(2,6,23,.06);
    }
    .theme-dark .home .section--process, .hc .home .section--process, .home.hc .section--process{
        background:#0f172a !important; border:1px solid #243047 !important; box-shadow:0 18px 60px rgba(8,15,27,.35);
    }
    .home .section--process h3{ color:#0b1220; font-weight:800; }
    .theme-dark .home .section--process h3, .hc .home .section--process h3, .home.hc .section--process h3{ color:#f5f7fa !important; }
    .home .section--process .card{ background:#ffffff; border:1px solid #e6eef6; border-radius:16px; box-shadow:0 10px 30px rgba(2,6,23,.06); }
    .theme-dark .home .section--process .card, .hc .home .section--process .card, .home.hc .section--process .card{
        background:#0b1220 !important; border:1px solid #243047 !important;
    }
    .home .section--process .step-chip{
        background:#ffd166; color:#0b1220; border-radius:999px; font-weight:800; box-shadow:0 4px 16px rgba(255,209,102,.35);
    }

    .home .section--testimonials h2{ color:#0b1220; font-weight:800; }
    .theme-dark .home .section--testimonials h2, .hc .home .section--testimonials h2, .home.hc .section--testimonials h2{ color:#eef3fb !important; }
    .home .section--testimonials .card{ background:#ffffff; border:1px solid #e6eef6; border-radius:16px; box-shadow:0 10px 30px rgba(2,6,23,.06); }
    .theme-dark .home .section--testimonials .card, .hc .home .section--testimonials .card, .home.hc .section--testimonials .card{
        background:#0b1220 !important; border:1px solid #243047 !important;
    }
    .home .section--testimonials .quote{ color:#334155; }
    .theme-dark .home .section--testimonials .quote, .hc .home .section--testimonials .quote, .home.hc .section--testimonials .quote{ color:#cdd6e1 !important; }

    .theme-dark .home .btn.btn-primary, .hc .home .btn.btn-primary, .home.hc .btn.btn-primary{
        background:#5fb0ff !important; border-color:#5fb0ff !important; color:#08101b !important; font-weight:700;
    }

    .home h1, .home h2, .home h3, .home h4 { opacity:1 !important; }

    /* keep nav size stable when A+/A- scales content */
    .topbar, .brand-name, .topbar .btn { font-size:14px !important; }
    .topbar .btn.small { font-size:13px !important; }
</style>

<script>
    /* Read-aloud */
    (function(){
        const btn = document.getElementById('btn-read');
        if (!btn) return;
        const playIcon  = btn.querySelector('.glyph--play');
        const pauseIcon = btn.querySelector('.glyph--pause-square');
        let speaking = false, utterance = null;

        function updateUI(){
            btn.setAttribute('aria-pressed', speaking ? 'true' : 'false');
            if (playIcon)  playIcon.style.display  = speaking ? 'none'  : 'inline-block';
            if (pauseIcon) pauseIcon.style.display = speaking ? 'inline-block' : 'none';
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

<script>
    /* Live apply + persist to DB (A+/A-/High Contrast buttons) */
    (function(){
        const PREFS = JSON.parse(document.getElementById('PREFS_BOOTSTRAP')?.textContent || '{}');
        const csrf = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
        const contentEls = Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));

        function apply(p){
            const page = document.querySelector('.page') || document.body;
            page.classList.toggle('hc', p.contrast === 'high');
            const s = Math.min(1.25, Math.max(0.9, parseFloat(p.font_scale || 1) || 1));
            contentEls.forEach(el => el.style.fontSize = (16 * s) + 'px');
        }
        apply(PREFS);

        async function savePrefs(patch){
            const res = await fetch('<?= $this->Url->build(['controller'=>'Preferences','action'=>'update']) ?>', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-Token': csrf},
                body: JSON.stringify(patch)
            });
            const data = await res.json().catch(()=>({ok:false}));
            if (data && data.ok && data.prefs) { Object.assign(PREFS, data.prefs); apply(PREFS); }
        }

        document.getElementById('contrast-toggle')?.addEventListener('click', ()=>{
            const next = (PREFS.contrast === 'high') ? 'normal' : 'high';
            savePrefs({contrast: next});
        });
        document.getElementById('font-plus')?.addEventListener('click', ()=>{
            const s = Math.min(1.25, (parseFloat(PREFS.font_scale||1) || 1) + 0.05);
            savePrefs({font_scale: s});
        });
        document.getElementById('font-minus')?.addEventListener('click', ()=>{
            const s = Math.max(0.9, (parseFloat(PREFS.font_scale||1) || 1) - 0.05);
            savePrefs({font_scale: s});
        });

        // If Settings form exists:
        document.querySelector('select[name="contrast"]')?.addEventListener('change', e=> savePrefs({contrast: e.target.value}));
        document.querySelector('input[name="font_scale"]')?.addEventListener('input',  e=> savePrefs({font_scale: e.target.value}));
    })();
</script>

<?= $this->Html->script('accessibility.js') ?>
<?= $this->Html->script('copilot.js') ?>
</body>
</html>

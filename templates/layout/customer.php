<?php
/**
 * Customer layout (standalone)
 * 读取 cookies 中的用户偏好：pref_theme / pref_contrast / pref_font_scale
 */

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
            $row = $CartItems->find()
                ->select(['sum_qty' => $CartItems->find()->func()->sum('qty')])
                ->where(['cart_id' => $cart->id])
                ->first();
            $cartQty = (int)($row->sum_qty ?? 0);
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <script>
        (function applyUserPrefsFromCookies(){
            const cookieMap = document.cookie.split(';').reduce((acc, c) => {
                if (!c.trim()) return acc;
                const i = c.indexOf('=');
                const k = (i >= 0 ? c.slice(0,i) : c).trim();
                const v = (i >= 0 ? c.slice(i+1) : '').trim();
                acc[decodeURIComponent(k)] = decodeURIComponent(v);
                return acc;
            }, {});

            const fontScale = parseFloat(cookieMap['pref_font_scale'] || '1.0');
            if (!isNaN(fontScale) && fontScale !== 1.0) {
                document.documentElement.style.fontSize = (16 * fontScale) + 'px';
            }

            const page = document.querySelector('.page') || document.body;
            if ((cookieMap['pref_contrast'] || 'normal') === 'high') {
                page.classList.add('hc');
            } else {
                page.classList.remove('hc');
            }

            const theme = cookieMap['pref_theme'] || 'auto';
            if (theme === 'dark') {
                document.body.classList.add('theme-dark');
                document.body.classList.remove('theme-light');
            } else if (theme === 'light') {
                document.body.classList.add('theme-light');
                document.body.classList.remove('theme-dark');
            } else {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.body.classList.toggle('theme-dark', !!prefersDark);
                document.body.classList.toggle('theme-light', !prefersDark);
            }
        })();
    </script>
</head>
<body class="<?= h($bodyClass) ?>">

<main id="content" class="page">
    <div class="container-fluid">
        <div class="row g-0">

            <div class="col-auto dashboard-sidebar">
                <div class="d-flex flex-column h-100">
                    <div class="p-3 border-bottom">
                        <h5 class="mb-2">Customer Dashboard</h5>
                        <div class="welcome text-muted small">
                            <span class="me-1">Welcome,</span>
                            <span class="welcome-id">
                                <?= h($identity->get('name') ?: $identity->get('email')) ?>
                            </span>
                        </div>
                    </div>

                    <nav class="dashboard-nav nav nav-pills flex-column flex-grow-1">
                        <?= $this->Html->link(
                            '<i class="bi bi-house"></i>Home',
                            ['controller' => 'Pages', 'action' => 'display', 'home'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="bi bi-shop"></i>Shop',
                            ['controller' => 'Products', 'action' => 'index'],
                            ['class' => 'nav-link', 'escape' => false]
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="bi bi-box-seam"></i>Orders',
                            ['controller' => 'Customer', 'action' => 'orders'],
                            ['class' => 'nav-link' . (in_array($this->request->getParam('action'), ['orders','orderDetails']) ? ' active' : ''), 'escape' => false]
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="bi bi-person"></i>Profile',
                            ['controller' => 'Customer', 'action' => 'profile'],
                            ['class' => 'nav-link' . ($this->request->getParam('action') === 'profile' ? ' active' : ''), 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="bi bi-gear"></i>Settings',
                            ['controller' => 'Customer', 'action' => 'settings'],
                            [
                                'class' => 'nav-link' . ($this->request->getParam('action') === 'settings' ? ' active' : ''),
                                'escape' => false
                            ]
                        ) ?>
                        <div class="mt-auto p-3">
                            <?= $this->Html->link(
                                '<i class="bi bi-box-arrow-right"></i>Logout',
                                ['controller' => 'Customer', 'action' => 'logout'],
                                ['class' => 'nav-link text-danger', 'escape' => false, 'confirm' => 'Are you sure you want to logout?']
                            ) ?>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-content">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .footer{text-align:center;color:#6b7280;padding:1.25rem 1rem}

    .dashboard-sidebar{
        background:#f8f9fa;
        border-right:1px solid #dee2e6;
        flex:0 0 auto;
        width:auto;
        min-width: 220px;
        max-width: 360px;
        padding-right: 0;
    }
    .dashboard-sidebar .welcome{white-space:nowrap;max-width:100%}
    .dashboard-sidebar .welcome-id{display:inline-block;max-width:calc(100% - 60px);overflow:hidden;text-overflow:ellipsis;vertical-align:bottom}

    .dashboard-nav .nav-link{color:#495057;padding:1rem 1.5rem;border-radius:0;margin-bottom:.25rem}
    .dashboard-nav .nav-link:hover,.dashboard-nav .nav-link.active{background:#e9ecef;color:#212529}
    .dashboard-nav .nav-link i{margin-right:.5rem;width:1.25rem}

    .dashboard-content{padding:2rem}

    .theme-dark{background:#0b1220;color:#e5e7eb}
    .theme-dark .dashboard-sidebar{background:#0f172a;border-color:#1f2937}
    .theme-dark .dashboard-nav .nav-link{color:#cbd5e1}
    .theme-dark .dashboard-nav .nav-link:hover,
    .theme-dark .dashboard-nav .nav-link.active{background:#1f2937;color:#fff}

    .page.hc, body.hc{background:#0b1220;color:#e5e7eb}
    .page.hc a, body.hc a{color:#93c5fd}
    .page.hc .dashboard-sidebar, body.hc .dashboard-sidebar{background:#0f172a;border-color:#334155}
    .page.hc .dashboard-nav .nav-link:hover,
    .page.hc .dashboard-nav .nav-link.active,
    body.hc .dashboard-nav .nav-link:hover,
    body.hc .dashboard-nav .nav-link.active{background:#1f2937;color:#fff}

    @media (max-width:680px){ .dashboard-sidebar{max-width:85vw;} }
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
    (function(){
        const root = document.querySelector('.page') || document.body;
        const plus = document.getElementById('font-plus');
        const minus = document.getElementById('font-minus');
        const contrast = document.getElementById('contrast-toggle');

        let scale = parseFloat(localStorage.getItem('fontSize')) || 1;
        if (scale && scale !== 1) {
            document.documentElement.style.fontSize = (16 * scale) + 'px';
        }

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

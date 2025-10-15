<?php
/**
 * Customer layout (dashboard)
 * Uses DB/Session-based preferences only (no cookies).
 */

$session = $this->getRequest()->getSession();
$prefs = $session->read('Prefs') ?: [
    'theme'       => 'auto',
    'contrast'    => 'normal',
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
<?= $this->Html->meta('csrfToken', $this->getRequest()->getAttribute('csrfToken')) ?>
<script>window.CopilotTalkUrl = <?= json_encode($this->Url->build('/copilot/talk')) ?>;</script>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <title><?= h($this->fetch('title') ?: 'Curd & Culture') ?></title>

    <?= $this->Html->meta('csrfToken', $this->getRequest()->getAttribute('csrfToken')) ?>
    <?= $this->fetch('meta') ?>

    <?= $this->Html->css('home') ?>
    <?= $this->Html->css('app') ?>

    <!-- Make sure content is visible immediately -->
    <script>document.documentElement.classList.add('is-ready');</script>

    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <!-- Bootstrap (optional for dashboard UI) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="<?= h($bodyThemeClass) ?>">

<?php
$currentController = $this->request->getParam('controller');
?>
<main id="content" class="page <?= h($pageContrastClass) ?>" style="<?= h($inlineFontStyle) ?>">
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
                            ['class' => 'nav-link' . ($this->request->getParam('action') === 'settings' ? ' active' : ''), 'escape' => false]
                        ) ?>

                        <?= $this->Html->link(
                            '<i class="bi bi-box-arrow-right"></i>Logout',
                            '#',
                            [
                                'class'   => 'nav-link text-danger',  // same look as others
                                'escape'  => false,
                                // click -> submit hidden POST form (keeps it secure)
                                'onclick' => "if(confirm('Are you sure you want to logout?')){document.getElementById('logoutFormSidebar').submit();} return false;"
                            ]
                        ) ?>

                        <!-- hidden POST form (CSRF-safe) -->
                        <form id="logoutFormSidebar"
                              method="post"
                              action="<?= $this->Url->build(['controller' => 'Customer', 'action' => 'logout']) ?>"
                              style="display:none">
                            <input type="hidden" name="_csrfToken" value="<?= h($this->getRequest()->getAttribute('csrfToken')) ?>">
                        </form>

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

<footer class="footer text-center text-muted py-3">
    <?= h(date('Y')) ?> &copy; Curd &amp; Culture
</footer>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        overflow-x: hidden;
    }

    :root{
        --nav-radius:12px;
        --nav-h:40px;
        --nav-h-sm:32px;
        --text-body:#111827; --text-muted:#6b7280;

        --chrome-bg:#ffffff; --chrome-fg:#0f172a; --chrome-border:#e5e7eb; --chrome-bg-2:#f8f9fa;
        --chrome-btn-bg:#ffffff; --chrome-btn-fg:#111111; --chrome-btn-border:#d1d5db;
        --chrome-btn-primary-bg:#2563eb; --chrome-btn-primary-fg:#ffffff; --chrome-btn-primary-border:#2563eb;

        --logo-bg:transparent; --logo-outline:transparent;
        --z-header:1030;
    }
    .theme-dark{
        --chrome-bg:#0f172a; --chrome-fg:#e5e7eb; --chrome-border:#334155; --chrome-bg-2:#111827;
        --chrome-btn-bg:#1f2937; --chrome-btn-fg:#f9fafb; --chrome-btn-border:#475569;
        --chrome-btn-primary-bg:#60a5fa; --chrome-btn-primary-fg:#111827; --chrome-btn-primary-border:#60a5fa;
        --logo-bg:#ffffff; --logo-outline:rgba(255,255,255,.25);
    }
    body{ color:var(--text-body); }

    .container-fluid {
        width: 100%;
        max-width: 100%;
    }

    .row {
        width: 100%;
    }

    .dashboard-sidebar{
        background:var(--chrome-bg-2);
        border-right:1px solid var(--chrome-border);
        color:var(--chrome-fg);
        flex: 0 0 auto;
        width: auto;
        min-width:220px;
        max-width:280px;
        padding-right: 0;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .dashboard-sidebar .welcome{
        white-space: nowrap;
        max-width: 100%;
    }

    .dashboard-sidebar .welcome-id{
        display:inline-block;
        max-width: calc(100% - 60px);
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }

    .dashboard-nav .nav-link{
        color: color-mix(in oklab, var(--chrome-fg) 75%, #9ca3af);
        padding:1rem 1.5rem;
        border-radius:0;
        margin-bottom:.25rem;
        white-space: nowrap;
        min-height: 44px;
        display: flex;
        align-items: center;
    }
    .dashboard-nav .nav-link:hover{
        background: color-mix(in oklab, var(--chrome-bg-2) 70%, #000 8%);
        color: var(--chrome-fg);
    }
    .dashboard-nav .nav-link.active{
        background: color-mix(in oklab, var(--chrome-bg-2) 60%, #000 12%);
        color: var(--chrome-fg);
    }
    .dashboard-nav .nav-link i{margin-right:.5rem;width:1.25rem;flex-shrink: 0;}

    .dashboard-content{
        padding:2rem;
        min-width: 0;
        flex: 1;
        overflow-x: hidden;
    }

    .footer{
        background:var(--chrome-bg);
        color: color-mix(in oklab, var(--chrome-fg) 70%, #9ca3af);
        border-top:1px solid var(--chrome-border);
        text-align:center;
        padding:1.25rem 1rem;
    }

    /* High Contrast overlays dashboard too */
    .page.hc, body.hc { background:#0b1220; color:#e5e7eb }
    .page.hc a { color:#9dd1ff; text-decoration:underline; text-underline-offset:2px }
    .page.hc .dashboard-sidebar{ background:#0f172a; border-color:#334155; color:#f5f7fa }
    .page.hc .dashboard-nav .nav-link:hover,
    .page.hc .dashboard-nav .nav-link.active{ background:#1f2937; color:#fff }
    
    /* High Contrast - Modals */
    .page.hc .modal-content,
    body.hc .modal-content { background:#0f172a; color:#f1f5f9; border:1px solid #475569 }
    .page.hc .modal-header,
    body.hc .modal-header { background:#1f2937; border-bottom-color:#475569 }
    .page.hc .modal-title,
    body.hc .modal-title { color:#f1f5f9 }
    .page.hc .modal-footer,
    body.hc .modal-footer { border-top-color:#475569 }
    .page.hc .btn-close,
    body.hc .btn-close { filter:invert(1) }
    
    /* High Contrast - Forms */
    .page.hc .form-control,
    .page.hc .form-select,
    .page.hc select,
    .page.hc input[type="text"],
    .page.hc input[type="email"],
    .page.hc input[type="tel"],
    .page.hc input[type="number"],
    .page.hc textarea,
    body.hc .form-control,
    body.hc .form-select,
    body.hc select,
    body.hc input[type="text"],
    body.hc input[type="email"],
    body.hc input[type="tel"],
    body.hc input[type="number"],
    body.hc textarea { background:#1f2937; color:#f1f5f9; border:1px solid #475569 }
    
    .page.hc .form-control:focus,
    .page.hc .form-select:focus,
    .page.hc select:focus,
    .page.hc input:focus,
    .page.hc textarea:focus,
    body.hc .form-control:focus,
    body.hc .form-select:focus,
    body.hc select:focus,
    body.hc input:focus,
    body.hc textarea:focus { border-color:#60a5fa; box-shadow:0 0 0 3px rgba(96,165,250,0.25); outline:none }
    
    .page.hc .form-label,
    body.hc .form-label { color:#cbd5e1; font-weight:600 }
    
    .page.hc .form-check-label,
    body.hc .form-check-label { color:#e5e7eb }
    
    .page.hc .form-check-input,
    body.hc .form-check-input { background:#1f2937; border-color:#475569 }
    
    .page.hc .form-check-input:checked,
    body.hc .form-check-input:checked { background:#60a5fa; border-color:#60a5fa }
    
    /* High Contrast - Buttons */
    .page.hc .btn,
    body.hc .btn { background:#1f2937; color:#f1f5f9; border:1px solid #475569 }
    .page.hc .btn:hover,
    body.hc .btn:hover { filter:brightness(1.1) }
    .page.hc .btn-primary,
    .page.hc .btn-dark,
    body.hc .btn-primary,
    body.hc .btn-dark { background:#60a5fa; color:#0f172a; border-color:#60a5fa; font-weight:600 }
    .page.hc .btn-outline-primary,
    body.hc .btn-outline-primary { background:transparent; color:#60a5fa; border-color:#60a5fa }
    .page.hc .btn-outline-primary:hover,
    body.hc .btn-outline-primary:hover { background:#60a5fa; color:#0f172a }
    
    /* High Contrast - Cards & Sections */
    .page.hc .card,
    body.hc .card { background:#0f172a; border-color:#475569; color:#f1f5f9 }
    .page.hc .card-header,
    body.hc .card-header { background:#1f2937; border-bottom-color:#475569; color:#f1f5f9 }
    .page.hc .border,
    body.hc .border { border-color:#475569 !important }
    
    /* High Contrast - Text & Badges */
    .page.hc .text-muted,
    body.hc .text-muted { color:#cbd5e1 !important }
    .page.hc .badge,
    body.hc .badge { background:#475569; color:#f1f5f9 }
    .page.hc .badge.bg-light,
    body.hc .badge.bg-light { background:#374155 !important; color:#f1f5f9 !important }
    
    /* High Contrast - Dropdown */
    .page.hc .dropdown-menu,
    body.hc .dropdown-menu { background:#0f172a; border-color:#475569 }
    .page.hc .dropdown-item,
    body.hc .dropdown-item { color:#f1f5f9 }
    .page.hc .dropdown-item:hover,
    body.hc .dropdown-item:hover { background:#1f2937; color:#fff }
    .page.hc .dropdown-divider,
    body.hc .dropdown-divider { border-top-color:#475569 }
    
    /* High Contrast - Input Groups (Phone country code) */
    .page.hc .input-group-text,
    body.hc .input-group-text { background:#1f2937; color:#f1f5f9; border:1px solid #475569 }
    
    /* High Contrast - Alerts */
    .page.hc .alert,
    body.hc .alert { background:#1f2937; border-color:#475569; color:#f1f5f9 }
    .page.hc .alert-info,
    body.hc .alert-info { background:#0f2236; border-color:#284b72; color:#cfe8ff }
    
    /* High Contrast - Settings Groups */
    .page.hc .settings-group,
    body.hc .settings-group { background:#1f2937; border-color:#475569 }
    .page.hc .settings-group-title,
    body.hc .settings-group-title { color:#f1f5f9; border-bottom-color:#475569 }
    
    /* High Contrast - Form Range Slider */
    .page.hc .form-range,
    body.hc .form-range { background:#374155 }
    .page.hc .form-range::-webkit-slider-thumb,
    body.hc .form-range::-webkit-slider-thumb { background:#60a5fa }
    .page.hc .form-range::-moz-range-thumb,
    body.hc .form-range::-moz-range-thumb { background:#60a5fa }
    .page.hc .form-range::-webkit-slider-runnable-track,
    body.hc .form-range::-webkit-slider-runnable-track { background:#22304a }
    .page.hc .form-range::-moz-range-track,
    body.hc .form-range::-moz-range-track { background:#22304a }
    
    /* High Contrast - Form Switch */
    .page.hc .form-switch .form-check-input,
    body.hc .form-switch .form-check-input { background:#374155; border-color:#475569 }
    .page.hc .form-switch .form-check-input:checked,
    body.hc .form-switch .form-check-input:checked { background:#60a5fa; border-color:#60a5fa }
    
    /* High Contrast - Form Text (helper text) */
    .page.hc .form-text,
    body.hc .form-text { color:#94a3b8 }

    @media (max-width: 1024px) {
        .dashboard-sidebar {
            min-width: 200px;
            max-width: 250px;
        }

        .dashboard-nav .nav-link {
            padding: 0.875rem 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .dashboard-sidebar {
            position: relative;
            height: auto;
            max-width: 100%;
            width: 100%;
        }

        .row {
            flex-direction: column;
        }

        .dashboard-content {
            padding: 1rem;
        }

        .dashboard-nav {
            display: flex;
            overflow-x: auto;
            flex-direction: row;
            -webkit-overflow-scrolling: touch;
        }

        .dashboard-nav .nav-link {
            flex-shrink: 0;
        }
    }

    @media (max-width: 680px) {
        .dashboard-sidebar {
            max-width: 100%;
        }
    }

    /* ===== FORCE LIGHT MODE FOR CUSTOMER PAGES ===== */
    html:not(.hc),
    body:not(.hc) {
        background: #f8fafc !important;
    }

    body:not(.hc) .dashboard-sidebar {
        background: #ffffff !important;
        border-right: 1px solid #e5e7eb !important;
    }

    body:not(.hc) .dashboard-content {
        background: #f8fafc !important;
        color: #0f172a !important;
    }

    body:not(.hc) .card,
    body:not(.hc) .sec-box,
    body:not(.hc) .group {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #e5e7eb !important;
    }

    body:not(.hc) h1,
    body:not(.hc) h2,
    body:not(.hc) h3,
    body:not(.hc) h4,
    body:not(.hc) p,
    body:not(.hc) span,
    body:not(.hc) label {
        color: #0f172a !important;
    }

    body:not(.hc) .form-control,
    body:not(.hc) input[type="text"],
    body:not(.hc) input[type="email"],
    body:not(.hc) input[type="password"],
    body:not(.hc) textarea,
    body:not(.hc) select {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #d1d5db !important;
    }
</style>

<!-- Bootstrap prefs to JS -->
<script id="PREFS_BOOTSTRAP" type="application/json"><?= json_encode($prefs, JSON_UNESCAPED_SLASHES) ?></script>

<script>
    /* Live apply + persist via /preferences/update */
    (function(){
        let PREFS = JSON.parse(document.getElementById('PREFS_BOOTSTRAP')?.textContent || '{}');
        const csrf = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
        const contentEls = Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));

        function apply(p, opts){
            const persist = !!(opts && opts.persist);
            const on = p.contrast === 'high';
            document.documentElement.classList.toggle('hc', on);
            document.body.classList.toggle('hc', on);
            const page = document.querySelector('.page') || document.body;
            page.classList.toggle('hc', on);

            // Persist to cookie + localStorage (only when this call is user-initiated)
            if (persist) {
                try {
                    document.cookie = `pref_contrast=${on ? 'high' : 'normal'}; Max-Age=${180*24*60*60}; Path=/`;
                    localStorage.setItem('highContrast', on ? 'true' : 'false');
                } catch(e) {}
            }

            const s = Math.min(1.25, Math.max(0.9, parseFloat(p.font_scale || 1) || 1));
            contentEls.forEach(el => el.style.fontSize = (16 * s) + 'px');
            if (persist) {
                try { document.cookie = `pref_font_scale=${encodeURIComponent(s.toFixed(2))}; Max-Age=${180*24*60*60}; Path=/`; } catch(e) {}
            }
        }
        // Respect cookie/localStorage if they differ from session
        (function(){
            let cookieContrast = (document.cookie.split(';').map(s=>s.trim().split('=')).reduce((a,[k,v])=>{a[k]=decodeURIComponent(v||'');return a;}, {})['pref_contrast'] || '').toLowerCase();
            let lsHC = '';
            try { lsHC = localStorage.getItem('highContrast') || ''; } catch(e) {}
            const derivedContrast = cookieContrast ? (cookieContrast === 'high' ? 'high' : 'normal') : (lsHC === 'true' ? 'high' : (lsHC === 'false' ? 'normal' : PREFS.contrast));
            const initial = Object.assign({}, PREFS, { contrast: derivedContrast });
            apply(initial); // no persistence on initial paint
        })();

        async function savePrefs(patch) {
            // Apply immediately and persist to cookie/localStorage
            const next = Object.assign({}, PREFS, patch);
            apply(next, {persist:true});

            const res = await fetch('<?= $this->Url->build(['controller'=>'Preferences','action'=>'update']) ?>', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-Token': csrf},
                body: JSON.stringify(patch)
            });
            const data = await res.json().catch(()=>({ok:false}));
            if (data && data.ok && data.prefs) { PREFS = data.prefs; apply(PREFS); }
        }

        // Bind if your Settings page has inputs with these names:
        document.querySelector('select[name="contrast"]')?.addEventListener('change', e=> savePrefs({contrast: e.target.value}));
        document.querySelector('input[name="font_scale"]')?.addEventListener('input', e=> savePrefs({font_scale: e.target.value}));
    })();
</script>

<?= $this->Html->script('accessibility.js') ?>
<?= $this->Html->script('copilot.js') ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

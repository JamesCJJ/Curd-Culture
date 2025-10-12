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
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

<footer class="footer text-center text-muted py-3">
    <?= h(date('Y')) ?> &copy; Curd &amp; Culture
</footer>

<style>
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
    .dashboard-sidebar{
        background:var(--chrome-bg-2);
        border-right:1px solid var(--chrome-border);
        color:var(--chrome-fg);
        min-width:220px; max-width:360px;
    }
    .dashboard-nav .nav-link{color: color-mix(in oklab, var(--chrome-fg) 75%, #9ca3af); padding:1rem 1.5rem; border-radius:0; margin-bottom:.25rem}
    .dashboard-nav .nav-link:hover{background: color-mix(in oklab, var(--chrome-bg-2) 70%, #000 8%); color: var(--chrome-fg)}
    .dashboard-nav .nav-link.active{background: color-mix(in oklab, var(--chrome-bg-2) 60%, #000 12%); color: var(--chrome-fg)}
    .dashboard-content{padding:2rem}
    .footer{ background:var(--chrome-bg); color: color-mix(in oklab, var(--chrome-fg) 70%, #9ca3af); border-top:1px solid var(--chrome-border); }

    /* High Contrast overlays dashboard too */
    .page.hc, body.hc { background:#0b1220; color:#e5e7eb }
    .page.hc a { color:#9dd1ff; text-decoration:underline; text-underline-offset:2px }
    .page.hc .dashboard-sidebar{ background:#0f172a; border-color:#334155; color:#f5f7fa }
    .page.hc .dashboard-nav .nav-link:hover,
    .page.hc .dashboard-nav .nav-link.active{ background:#1f2937; color:#fff }
</style>

<!-- Bootstrap prefs to JS -->
<script id="PREFS_BOOTSTRAP" type="application/json"><?= json_encode($prefs, JSON_UNESCAPED_SLASHES) ?></script>

<script>
    /* Live apply + persist via /preferences/update */
    (function(){
        const PREFS = JSON.parse(document.getElementById('PREFS_BOOTSTRAP')?.textContent || '{}');
        const csrf = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
        const contentEls = Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));

        function apply(p) {
            const page = document.querySelector('.page') || document.body;
            page.classList.toggle('hc', p.contrast === 'high');
            const s = Math.min(1.25, Math.max(0.9, parseFloat(p.font_scale || 1) || 1));
            contentEls.forEach(el => el.style.fontSize = (16 * s) + 'px');
        }
        apply(PREFS);

        async function savePrefs(patch) {
            const res = await fetch('<?= $this->Url->build(['controller'=>'Preferences','action'=>'update']) ?>', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-Token': csrf},
                body: JSON.stringify(patch)
            });
            const data = await res.json().catch(()=>({ok:false}));
            if (data && data.ok && data.prefs) { Object.assign(PREFS, data.prefs); apply(PREFS); }
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

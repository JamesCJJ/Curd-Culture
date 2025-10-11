<?php
/**
 * Customer layout (standalone, unified with default topbar + a11y)
 */

$cookies   = $this->getRequest()->getCookieParams();
$theme     = $cookies['pref_theme']    ?? 'light';   // default: light
$contrast  = $cookies['pref_contrast'] ?? 'normal';  // default: normal
$fontScale = (float)($cookies['pref_font_scale'] ?? '1.0'); // default: 1.0
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

    <!-- Failsafe: make content visible no matter what -->
    <script>
        document.documentElement.classList.add('is-ready');
    </script>

    <!-- Apply user preferences from cookies (guarded) — SAME AS default -->
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

    <script>window.CakeWebroot = <?= json_encode($this->Url->webroot) ?>;</script>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <!-- Bootstrap (you already had this) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="<?= h($bodyClass) ?>">

<!-- === TOPBAR: copied to match default layout === -->
<?php
$currentController = $this->request->getParam('controller');
$currentPrefix     = $this->request->getParam('prefix');
$identity          = $this->getRequest()->getAttribute('identity');
$isAdmin           = ($identity && strtolower((string)$identity->get('role')) === 'admin');
?>

<!-- === /TOPBAR === -->

<main id="content" class="page">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Keep your sidebar -->
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
        --z-header:1030;
    }

    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}

    /* === TOPBAR styles (same as default) === */
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
    .topbar .btn.small{height:var(--nav-h-sm);min-height:var(--nav-h-sm);padding:0 10px;font-size:.8125rem}

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

    /* Theme / high-contrast tweaks for topbar */
    .theme-dark .topbar{background:#111827;border-color:#1f2937}
    .theme-dark .topbar .btn{background:#374151;color:#f9fafb;border-color:#475569}
    .theme-dark .topbar .btn-primary{background:#60a5fa;color:#111;border-color:#60a5fa}
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .page.hc .brand-name{color:#e5e7eb}
    .page.hc .topbar .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .topbar .btn-primary{background:#60a5fa;color:#111}

    /* === Sidebar / content (kept) === */
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
</style>

<script>
    /* Read-aloud button — SAME AS default */
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

<!-- Accessibility tools write cookies → global effect — EXACTLY like default -->
<script>
    (function(){
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

        // Scale only content areas (same selectors as default)
        const contentEls = Array.from(document.querySelectorAll('.page, .dashboard-content, .admin-content'));

        // Topbar buttons
        const btnContrast = document.getElementById('contrast-toggle');
        const btnPlus     = document.getElementById('font-plus');
        const btnMinus    = document.getElementById('font-minus');

        // Optional Settings page controls (kept for parity)
        const selContrast = document.querySelector('select[name="contrast"]');
        const rngFont     = document.querySelector('input[name="font_scale"]');
        const fontValLab  = document.getElementById('font-val');

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

        // Init from cookies
        applyContrast(getCookie('pref_contrast') === 'high' ? 'high' : 'normal');
        applyFontScale(parseFloat(getCookie('pref_font_scale') || '1') || 1);

        // Wire buttons
        btnContrast && btnContrast.addEventListener('click', () => {
            const turnOn = !document.body.classList.contains('hc');
            applyContrast(turnOn ? 'high' : 'normal');
        });
        btnPlus && btnPlus.addEventListener('click', () => {
            const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
            applyFontScale(curr + 0.05);
        });
        btnMinus && btnMinus.addEventListener('click', () => {
            const curr = parseFloat(getCookie('pref_font_scale') || '1') || 1;
            applyFontScale(curr - 0.05);
        });

        // Keep Settings page in sync if present
        selContrast && selContrast.addEventListener('change', e => applyContrast(e.target.value));
        rngFont     && rngFont.addEventListener('input',  e => applyFontScale(e.target.value));
    })();
</script>

<?= $this->Html->script('accessibility.js') ?>
<?= $this->Html->script('copilot.js') ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

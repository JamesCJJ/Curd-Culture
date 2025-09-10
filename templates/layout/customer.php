<?php
/**
 * Customer layout (standalone)
 * - 使用 default 的样式资源
 * - 中间区域：左侧栏(自适应宽度) + 右侧内容（fetch('content')）
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

    <?= $this->fetch('meta') ?>

    <?= $this->Html->css('home') ?>
    <?= $this->Html->css('app') ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="<?= h($bodyClass) ?>">

<main id="content" class="page">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Sidebar: 自适应内容宽度 -->
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

            <!-- Main content: 自动占据剩余空间 -->
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
    /* default 部分 */
    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .footer{text-align:center;color:#6b7280;padding:1.25rem 1rem}

    /* ---- 自适应侧边栏 ----
       col-auto 让列宽按内容自动；以下规则保证观感与安全范围 */
    .dashboard-sidebar{
        background:#f8f9fa;
        border-right:1px solid #dee2e6;
        /* 内容决定宽度 + 安全护栏 */
        flex:0 0 auto;            /* 不拉伸，不压缩，宽度由内容定 */
        width:auto;
        min-width: 220px;         /* 最小宽度 */
        max-width: 360px;         /* 最大宽度，避免极长账号占太多 */
        padding-right: 0;         /* g-0 已去掉列间距，这里避免视觉双边距 */
    }
    .dashboard-sidebar .welcome{
        white-space: nowrap;      /* 不换行，随内容横向扩展 */
        max-width: 100%;
    }
    .dashboard-sidebar .welcome-id{
        display:inline-block;
        max-width: calc(100% - 60px); /* 预留“Welcome,”宽度 */
        overflow: hidden;
        text-overflow: ellipsis;  /* 超长时尾部省略号 */
        vertical-align: bottom;
    }

    .dashboard-nav .nav-link{color:#495057;padding:1rem 1.5rem;border-radius:0;margin-bottom:.25rem}
    .dashboard-nav .nav-link:hover,.dashboard-nav .nav-link.active{background:#e9ecef;color:#212529}
    .dashboard-nav .nav-link i{margin-right:.5rem;width:1.25rem}

    .dashboard-content{padding:2rem}

    /* 主题/辅助（沿用 default） */
    .topbar{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid #e5e7eb}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
    .nav-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
    .brand-link{display:flex;align-items:center;gap:.5rem;text-decoration:none}
    .brand-logo{height:28px;width:auto;border-radius:.25rem}
    .brand-name{font-weight:700;color:#0f172a}
    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none;font-size:.95rem}
    .btn:hover{filter:brightness(.97)}
    .btn:focus-visible{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .small{font-size:.85rem;padding:.3rem .6rem}
    .topbar .btn-primary{background:#2563eb;color:#fff;border-color:transparent;width:auto !important;display:inline-block !important;border-radius:.6rem;padding:.55rem .9rem;box-shadow:none}
    .glyph{display:inline-block;width:12px;height:12px;margin-right:.35rem;vertical-align:-1px}
    .glyph--play{clip-path:polygon(0 0,100% 50%,0 100%);background:currentColor}
    .glyph--pause-square{display:none;position:relative;width:12px;height:12px;border-radius:2px;background:transparent;border:1.5px solid currentColor}
    .glyph--pause-square::before,.glyph--pause-square::after{content:"";position:absolute;top:2px;bottom:2px;width:2px;background:currentColor}
    .glyph--pause-square::before{left:3px}
    .glyph--pause-square::after{right:3px}

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
        .dashboard-sidebar{max-width: 85vw;} /* 小屏时放宽上限，但仍保留余量 */
    }
</style>

<script>
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
                    const ut = new SpeechSynthesisUtterance(buildText());
                    ut.rate = 1; ut.pitch = 1;
                    ut.onend = () => { speaking = false; updateUI(); };
                    ut.onerror = () => { speaking = false; updateUI(); };
                    speaking = true; updateUI();
                    window.speechSynthesis.speak(ut);
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

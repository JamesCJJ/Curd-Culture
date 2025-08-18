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
    <?= $this->Html->css('home') ?>        <!-- 载入全局样式 -->
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>

<header class="topbar" role="navigation" aria-label="Global">
    <div class="topbar__inner">
        <div class="brand">
            <?= $this->Html->link(
                $this->Html->image('logo.png', [   // 如果你实际是 cake-logo.png，这里改成 'cake-logo.png'
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
                'Admin Login',
                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'login'],
                ['class' => 'btn', 'aria-label' => 'Go to admin login']
            ) ?>


            <button id="btn-read" class="btn btn-subtle" type="button" aria-pressed="false" aria-label="Read page aloud">
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
    <?= $this->fetch('content') ?>
</main>

<footer class="footer" role="contentinfo">
    <small>© <?= date('Y') ?> Curd &amp; Culture</small>
</footer>

<style>
    /* ====== Topbar ====== */
    .topbar{position:sticky;top:0;z-index:1000;background:#fff;border-bottom:1px solid #e5e7eb}
    .page.hc .topbar{background:#0f172a;border-color:#334155}
    .topbar__inner{max-width:1100px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
    .nav-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}


    .brand-link{display:flex;align-items:center;gap:.5rem;text-decoration:none}
    .brand-logo{height:28px;width:auto;border-radius:.25rem}
    .brand-name{font-weight:700;color:#0f172a}
    .page.hc .brand-name{color:var(--text)}


    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none}
    .btn:hover{filter:brightness(.98)}
    .btn:focus-visible{outline:3px solid rgba(44,123,229,.25);outline-offset:2px}
    .btn-primary{background:#2c7be5;color:#fff}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .small{font-size:.9rem;padding:.35rem .55rem}


    #content{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .footer{text-align:center;color:#6b7280;padding:1.25rem 1rem}

    .page.hc .btn{background:#1f2937;color:#fff;border-color:#475569}
    .page.hc .btn-primary{background:#60a5fa;color:#111}
    @media (max-width:680px){.topbar__inner{padding:.5rem .75rem}.nav-actions{gap:.4rem}}
    .page.hc{background:#0b1220;color:#e5e7eb}
    .page.hc a{color:#93c5fd}
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
</script>

<?= $this->Html->script('accessibility.js') ?>  <!-- Read 播放/暂停 -->
</body>
</html>

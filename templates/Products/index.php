<?php
$this->assign('title', 'Products');
?>
<div class="page-catalog">
    <header class="catalog-head">
        <h1>Our Cheeses</h1>
        <p>Explore our curated selection of artisan dairy & cheese.</p>
    </header>

    <div class="grid">
        <?php foreach ($products as $p): ?>
            <?php

            $key = $p->slug ?: (string)$p->id;
            $viewUrl = $this->Url->build(['controller' => 'Products', 'action' => 'view', $key]);
            ?>
            <div class="card product-card">

                <a class="product-media js-product-view" href="<?= h($viewUrl) ?>">
                    <?php if (!empty($p->image_url)): ?>
                        <img src="<?= h($this->Url->webroot($p->image_url)) ?>" alt="<?= h($p->name) ?>">
                    <?php else: ?>
                        <div class="ph">No Image</div>
                    <?php endif; ?>
                </a>

                <div class="product-body">
                    <h3 class="product-title">
                        <a href="<?= h($viewUrl) ?>"><?= h($p->name) ?></a>
                    </h3>

                    <div class="product-meta">
                        <span><?= h($p->milk_type ?: 'Dairy') ?></span>
                        <?php if (!empty($p->origin_country)): ?>
                            <span>• <?= h($p->origin_country) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p->age)): ?>
                            <span>• Aged <?= h($p->age) ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="product-summary"><?= h(mb_strimwidth((string)$p->summary, 0, 120, '…')) ?></p>

                    <div class="product-foot">
                        <div class="price">
                            <?= $this->Number->currency((float)($p->price ?? 0), $p->currency ?: 'AUD') ?>
                        </div>
                        <a class="btn small btn-primary" href="<?= h($viewUrl) ?>">View</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($paging) && ($paging['pages'] ?? 1) > 1): ?>
        <?php
        $cur     = (int)($paging['page'] ?? 1);
        $pages   = (int)($paging['pages'] ?? 1);
        $count   = (int)($paging['count'] ?? 0);
        $hasPrev = !empty($paging['hasPrev']);
        $hasNext = !empty($paging['hasNext']);
        $start   = max(1, $cur - 2);
        $end     = min($pages, $cur + 2);
        ?>
        <nav class="cc-paginate" aria-label="Products pagination">
            <ul class="pagination">
                <?php if ($pages > 2): ?>
                    <li class="page-item <?= $cur > 1 ? '' : 'disabled' ?>">
                        <?php if ($cur > 1): ?>
                            <a class="page-link" href="<?= $this->Url->build(['controller' => 'Products','action' => 'index','?' => ['page' => 1]]) ?>" aria-label="First">«</a>
                        <?php else: ?>
                            <span class="page-link" aria-hidden="true">«</span>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= $hasPrev ? '' : 'disabled' ?>">
                    <?php if ($hasPrev): ?>
                        <a class="page-link" href="<?= $this->Url->build(['controller' => 'Products','action' => 'index','?' => ['page' => max(1, $cur - 1)]]) ?>" rel="prev" aria-label="Previous">‹</a>
                    <?php else: ?>
                        <span class="page-link" aria-hidden="true">‹</span>
                    <?php endif; ?>
                </li>

                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index','?' => ['page' => 1]]) ?>">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="page-item dots"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $cur ? 'active' : '' ?>">
                        <?php if ($i === $cur): ?>
                            <span class="page-link"><?= $i ?></span>
                        <?php else: ?>
                            <a class="page-link" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index','?' => ['page' => $i]]) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $pages): ?>
                    <?php if ($end < $pages - 1): ?>
                        <li class="page-item dots"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index','?' => ['page' => $pages]]) ?>"><?= $pages ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $hasNext ? '' : 'disabled' ?>">
                    <?php if ($hasNext): ?>
                        <a class="page-link" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index','?' => ['page' => min($pages, $cur + 1)]]) ?>" rel="next" aria-label="Next">›</a>
                    <?php else: ?>
                        <span class="page-link" aria-hidden="true">›</span>
                    <?php endif; ?>
                </li>

                <?php if ($pages > 2): ?>
                    <li class="page-item <?= $cur < $pages ? '' : 'disabled' ?>">
                        <?php if ($cur < $pages): ?>
                            <a class="page-link" href="<?= $this->Url->build(['controller' => 'Products','action' => 'index','?' => ['page' => $pages]]) ?>" aria-label="Last">»</a>
                        <?php else: ?>
                            <span class="page-link" aria-hidden="true">»</span>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="pagination-meta">
                Page <?= $cur ?> of <?= $pages ?> (<?= $count ?> total products)
            </div>
        </nav>
    <?php endif; ?>
</div>

<div id="modalHost" class="modal-host" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="product-modal-title" tabindex="-1">
        <div id="modalBody"></div>
    </div>
</div>

<style>
    .page-catalog{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .catalog-head h1{margin:.2rem 0 .15rem;font-size:1.6rem}
    .catalog-head p{margin:0 0 1rem;color:#6b7280}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
    @media (max-width:980px){.grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:640px){.grid{grid-template-columns:1fr}}
    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:hidden}
    .theme-dark .card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    .product-card .product-media{display:block;height:180px;background:#f3f4f6}
    .product-card img{width:100%;height:180px;object-fit:cover;display:block}
    .product-card .ph{height:180px;display:grid;place-items:center;color:#9aa3af}
    .product-body{padding:.9rem}
    .product-title{margin:.1rem 0 .25rem;font-size:1.05rem}
    .product-title a{text-decoration:none;color:inherit}
    .product-meta{color:#6b7280;font-size:.9rem;margin-bottom:.5rem}
    .product-summary{color:#374151;font-size:.95rem;min-height:2.5rem}
    .product-foot{display:flex;align-items:center;justify-content:space-between;margin-top:.65rem}
    .price{font-weight:800}
    .btn{display:inline-block;padding:.45rem .75rem;border-radius:.55rem;border:1px solid #e4e7ec;background:#f3f5f7;color:#111;text-decoration:none}
    .small{font-size:.9rem;padding:.35rem .55rem}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .btn.disabled, .btn[aria-disabled="true"]{opacity:.5;pointer-events:none}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}

    .cc-paginate{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap}
    .pagination{list-style:none;display:flex;gap:.35rem;padding:0;margin:0}
    .page-item .page-link{
        display:inline-block;min-width:2.25rem;text-align:center;
        padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:.55rem;
        background:#fff;color:#111;text-decoration:none;line-height:1;transition:all .15s ease;
    }
    .page-item .page-link:hover{filter:brightness(.98)}
    .page-item.active .page-link{background:#2563eb;color:#fff;border-color:#2563eb}
    .page-item.disabled .page-link{opacity:.45;pointer-events:none}
    .page-item.dots .page-link{cursor:default}
    .pagination-meta{color:#6b7280;font-size:.9rem}
    .theme-dark .page-item .page-link{background:#0f172a;color:#e5e7eb;border-color:#334155}
    .theme-dark .page-item.active .page-link{background:#60a5fa;color:#111;border-color:#60a5fa}
    .theme-dark .pagination-meta{color:#94a3b8}

    .modal-dialog{background:#fff;border:1px solid #eef0f3}
    .theme-dark .modal-dialog{background:#111827;border-color:#1f2937}

    .modal-host{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:1050}
    .modal-host.show{display:flex}
    .modal-backdrop{position:absolute;inset:0;background:rgba(2,6,23,.45);backdrop-filter:saturate(120%) blur(2px)}
    .theme-dark .modal-backdrop{background:rgba(0,0,0,.55)}
    .modal-dialog{position:relative;max-width:960px;width:min(960px, calc(100vw - 2rem));max-height:calc(100vh - 2rem);overflow:auto;border-radius:1rem}
</style>

<script>
    (function(){
        const host = document.getElementById('modalHost');
        const body = document.getElementById('modalBody');
        let lastFocused = null;

        function openModal(html, url){
            body.innerHTML = html;
            host.classList.add('show');
            host.setAttribute('aria-hidden','false');
            const closer = body.querySelector('.modal-close');
            if (closer) closer.addEventListener('click', closeModal);
            host.querySelector('.modal-backdrop').addEventListener('click', closeModal, {once:true});
            document.addEventListener('keydown', onEsc);
            const dlg = host.querySelector('.modal-dialog');
            dlg && dlg.focus({preventScroll:true});
            if (url) {
                history.pushState({modal:true}, '', url);
                window.addEventListener('popstate', onPopClose, {once:true});
            }
        }
        function closeModal(){
            host.classList.remove('show');
            host.setAttribute('aria-hidden','true');
            body.innerHTML = '';
            document.removeEventListener('keydown', onEsc);
            if (lastFocused) { try { lastFocused.focus(); } catch(e){} }
        }
        function onEsc(e){ if (e.key === 'Escape') closeModal(); }
        function onPopClose(){ closeModal(); }

        async function handleClick(e){
            const a = e.target.closest('a.js-product-view');
            if (!a) return;
            const url = new URL(a.href, location.href);
            if (url.origin !== location.origin) return;
            e.preventDefault();
            lastFocused = a;
//test
            url.searchParams.set('modal', '1');
            const res = await fetch(url.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}});
            if (!res.ok) { location.href = a.href; return; }
            const html = await res.text();
            openModal(html, a.href);
        }

        document.addEventListener('click', handleClick);
    })();
</script>

<?php
$this->assign('title', 'Your cart');
$csrf = (string)($this->getRequest()->getAttribute('csrfToken') ?? '');
?>
<!-- 提供 CSRF 给前端 fetch 使用 -->
<meta name="csrf-token" content="<?= h($csrf) ?>">

<div class="cart-page">
    <h1>Your cart</h1>

    <?php if (empty($items)): ?>
        <div class="card empty">
            <p>Your cart is empty.</p>
            <a class="btn btn-primary" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index']) ?>">Browse products</a>
        </div>
    <?php else: ?>
        <?= $this->Form->create(null, ['url' => ['action' => 'update'], 'id' => 'cartUpdate']) ?>
        <div class="cart-wrap">
            <div class="card">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th class="qtyc">Qty</th>
                        <th class="pricec">Price</th>
                        <th class="pricec">Subtotal</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $id => $it): ?>
                        <tr>
                            <td>
                                <a class="pname" href="<?= $this->Url->build(['controller'=>'Products','action'=>'view',$it['slug']]) ?>">
                                    <?= h($it['name']) ?>
                                </a>
                            </td>
                            <td class="qtyc">
                                <input class="qty" type="number" min="0" name="qty[<?= (int)$id ?>]" value="<?= (int)$it['qty'] ?>">
                            </td>
                            <td class="pricec"><?= $this->Number->currency((float)$it['price'], $it['currency']) ?></td>
                            <td class="pricec"><?= $this->Number->currency((float)$it['price']*(int)$it['qty'], $it['currency']) ?></td>
                            <td class="actions">
                                <!-- 用 data-remove 携带删除 URL，JS 用 fetch POST 提交 -->
                                <button type="button"
                                        class="btn small danger js-remove"
                                        data-remove="<?= $this->Url->build(['action'=>'remove', (int)$id]) ?>">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <button class="btn" type="submit">Update cart</button>
                    <a class="btn btn-subtle" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index']) ?>">Continue shopping</a>
                </div>
            </div>

            <aside class="card summary">
                <h3>Order summary</h3>
                <dl>
                    <div><dt>Subtotal</dt><dd><?= $this->Number->currency($subtotal, $currency) ?></dd></div>
                    <div><dt>Shipping</dt><dd><?= $this->Number->currency($shipping, $currency) ?></dd></div>
                    <div class="total"><dt>Total</dt><dd><?= $this->Number->currency($total, $currency) ?></dd></div>
                </dl>
                <a class="btn btn-primary" href="<?= $this->Url->build(['action'=>'checkout']) ?>">Checkout</a>
            </aside>
        </div>
        <?= $this->Form->end() ?>
    <?php endif; ?>
</div>

<style>
    .cart-page{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
    .theme-dark .card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    .empty{display:grid;gap:.7rem;place-items:center;padding:2rem 1rem}

    .cart-wrap{display:grid;grid-template-columns:2fr 1fr;gap:1rem}
    @media (max-width:900px){.cart-wrap{grid-template-columns:1fr}}

    .table{width:100%;border-collapse:separate;border-spacing:0}
    .table thead th{font-weight:600;color:#6b7280;text-align:left;border-bottom:1px solid #eef0f3;padding:.55rem}
    .table tbody td{padding:.6rem .55rem;border-bottom:1px solid #f2f4f6;vertical-align:middle}
    .table tbody tr:last-child td{border-bottom:0}
    .qtyc{width:110px}
    .pricec{width:140px;text-align:right}
    .actions{text-align:right}

    .pname{text-decoration:none;color:inherit}
    .qty{width:80px;border-radius:.6rem;border:1px solid #e5e7eb;padding:.35rem .5rem;background:#f9fafb}
    .theme-dark .qty{background:#0f172a;border-color:#334155;color:#e5e7eb}
    
    /* High contrast mode for cart */
    .page.hc .card{background:#0f172a !important;border-color:#334155 !important;color:#f1f5f9 !important}
    .page.hc .table thead th{color:#f1f5f9 !important;border-bottom-color:#334155 !important}
    .page.hc .table tbody td{border-bottom-color:#334155 !important;color:#f1f5f9 !important}
    .page.hc .pname{color:#60a5fa !important}
    .page.hc .qty{background:#0f172a !important;border-color:#334155 !important;color:#f1f5f9 !important}
    .page.hc .qty::placeholder{color:#9aa3af !important}
    .page.hc .btn{background:#1f2937 !important;border-color:#374155 !important;color:#f1f5f9 !important}
    .page.hc .btn-primary{background:#60a5fa !important;color:#0f172a !important;border-color:#60a5fa !important}
    .page.hc .btn.danger{background:#dc2626 !important;color:#fff !important;border-color:#dc2626 !important}
    .page.hc .btn-subtle{background:transparent !important;color:#60a5fa !important;border-color:#334155 !important}
    .page.hc .summary dt{color:#cbd5e1 !important}
    .page.hc .summary dd{color:#f1f5f9 !important}
    .page.hc .summary .total dt,
    .page.hc .summary .total dd{color:#f1f5f9 !important;font-weight:700}

    .cart-actions{display:flex;align-items:center;gap:.5rem;justify-content:space-between;margin-top:.75rem}

    .summary h3{margin:.1rem 0 .6rem}
    .summary dl{margin:0;display:grid;gap:.35rem}
    .summary dt{color:#6b7280}
    .summary dd{margin:0;text-align:right;font-weight:700}
    .summary .total{padding-top:.35rem;border-top:1px solid #eef0f3;margin-top:.35rem}

    .btn{display:inline-block;padding:.45rem .75rem;border-radius:.55rem;border:1px solid #e4e7ec;background:#f3f5f7;color:#111;text-decoration:none;cursor:pointer}
    .btn-subtle{背景:transparent;border-color:#d1d5db;color:#374151}
    .btn.primary{background:#2c7be5;color:#fff;border-color:transparent}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .small{font-size:.9rem;padding:.35rem .55rem}
    .danger{background:#fee2e2;border-color:#fecaca;color:#991b1b}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}
</style>

<script>
    (function(){
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.js-remove');
            if (!btn) return;
            if (!confirm('Remove this item?')) return;

            try{
                const res = await fetch(btn.dataset.remove, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf
                    }
                });
                // 无论返回 200/302，刷新以显示最新数据
                location.reload();
            }catch(err){
                location.reload();
            }
        });
    })();
</script>

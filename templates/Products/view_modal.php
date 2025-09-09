<?php
$this->assign('title', $product->name);
$currency = $product->currency ?: 'AUD';
$yn10 = function($v) {
    if ($v === null || $v === '') return null;     // 留空就不显示
    if ((string)$v === '1') return 'Yes';          // 严格 1 -> Yes
    if ((string)$v === '0') return 'No';           // 严格 0 -> No
    return (string)$v;                             // 其它值原样返回
};

?>
<section class="p-modal">
    <header class="p-modal__head">
        <h2 id="product-modal-title"><?= h($product->name) ?></h2>
        <button type="button" class="modal-close" aria-label="Close">✕</button>
    </header>

    <div class="p-modal__body">
        <div class="p-modal__grid">
            <div class="p-modal__media">
                <?php if (!empty($product->image_url)): ?>
                    <img src="<?= h($product->image_url) ?>" alt="<?= h($product->name) ?>">
                <?php else: ?>
                    <div class="ph">No Image</div>
                <?php endif; ?>
            </div>

            <div class="p-modal__info">
                <?php if (!empty($product->summary)): ?>
                    <p class="lead"><?= h($product->summary) ?></p>
                <?php endif; ?>

                <div class="p-price">
                    <div class="amount"><?= $this->Number->currency((float)($product->price ?? 0), $currency) ?></div>
                    <?php if ($product->stock !== null && $product->stock !== ''): ?>
                        <div class="stock">Stock: <?= (int)$product->stock ?></div>
                    <?php endif; ?>
                </div>

                <form class="buy" method="post" action="<?= $this->Url->build(['controller' => 'Products', 'action' => 'addToCart', $product->id]) ?>">
                    <label for="qty" class="qty-label">Qty</label>
                    <input class="qty" type="number" min="1" name="qty" id="qty" value="1">
                    <button class="btn btn-primary">Add to cart</button>
                </form>

                <?php if (!empty($product->description)): ?>
                    <div class="desc">
                        <h3>About this cheese</h3>
                        <p><?= nl2br(h($product->description)) ?></p>
                    </div>
                <?php endif; ?>

                <div class="specs">
                    <h3>Details</h3>
                    <div class="spec-grid">
                        <?php
                        $specs = [
                            'Origin'        => $product->origin_country ?? null,
                            'Milk'          => $product->milk_type ?? null,
                            'Age'           => $product->age ?? null,
                            'Style'         => $product->style ?? null,
                            'Rennet'        => $product->rennet ?? null,
                            'Pasteurised'   => $product->pasteurised ?? null,
                            'Fat content'   => $product->fat_content ?? null,
                            'Vegetarian'    => $product->vegetarian ?? null,
                            'Gluten free'   => $yn10($product->gluten_free),
                            'Lactose free'  => $yn10($product->lactose_free),
                            'Allergens'     => $product->allergens ?? null,
                            'Pairings'      => $product->pairing_notes ?? null,
                            'Awards'        => $product->awards ?? null,
                            'Rating'        => $product->rating ?? null,
                        ];
                        foreach ($specs as $label => $val):
                            if ($val === null || $val === '') continue; ?>
                            <div class="row">
                                <span class="k"><?= h($label) ?></span>
                                <span class="v"><?= h((string)$val) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .p-modal{background:#fff;border-radius:1rem}
    .theme-dark .p-modal{background:#111827}
    .p-modal__head{position:sticky;top:0;z-index:2;display:flex;align-items:center;justify-content:space-between;
        padding:14px 18px;border-bottom:1px solid #eef0f3;background:inherit;border-top-left-radius:1rem;border-top-right-radius:1rem}
    .theme-dark .p-modal__head{border-color:#1f2937}
    .p-modal__head h2{margin:0;font-size:1.15rem;font-weight:800;letter-spacing:.2px}
    .modal-close{appearance:none;border:0;background:transparent;font-size:20px;line-height:1;padding:6px;cursor:pointer;border-radius:.5rem}
    .modal-close:hover{filter:brightness(.9)}
    .theme-dark .modal-close{color:#e5e7eb}

    .p-modal__body{padding:16px 18px 18px}
    .p-modal__grid{display:grid;grid-template-columns:minmax(260px,340px) minmax(380px, 1fr);gap:16px}
    @media (max-width:860px){.p-modal__grid{grid-template-columns:1fr}}

    .p-modal__media{background:#f3f4f6;border:1px solid #eef0f3;border-radius:.9rem;display:grid;place-items:center;
        aspect-ratio:3/4;min-height:280px;overflow:hidden}
    .p-modal__media img{width:100%;height:100%;object-fit:cover;display:block}
    .p-modal__media .ph{color:#9aa3af}
    .theme-dark .p-modal__media{background:#0f172a;border-color:#1f2937}

    .p-modal__info{min-width:0}
    .lead{margin:.15rem 0 .6rem;color:#374151}
    .theme-dark .lead{color:#cbd5e1}

    .p-price{display:flex;align-items:center;gap:.75rem;margin:.3rem 0 .55rem}
    .p-price .amount{font-weight:800}
    .p-price .stock{color:#6b7280}
    .theme-dark .p-price .stock{color:#94a3b8}

    .buy{display:flex;align-items:center;gap:.5rem;margin:.2rem 0 1rem;flex-wrap:wrap}
    .qty-label{font-weight:600}
    .qty{width:92px;border-radius:.6rem;border:1px solid #e5e7eb;padding:.45rem .55rem;background:#f9fafb}
    .theme-dark .qty{background:#0f172a;border-color:#334155;color:#e5e7eb}

    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none;cursor:pointer}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}

    .desc h3{margin:.8rem 0 .35rem}
    .desc p{margin:0 0 .4rem}

    .specs h3{margin:.6rem 0 .45rem}
    .spec-grid{display:grid;grid-template-columns:repeat(2, minmax(160px, 1fr));gap:.5rem .9rem;max-width:740px}
    .spec-grid .row{display:grid;grid-template-columns:120px 1fr;gap:.75rem;align-items:start}
    .spec-grid .k{color:#6b7280}
    .spec-grid .v{font-weight:600}
    @media (max-width:600px){
        .spec-grid{grid-template-columns:1fr}
        .spec-grid .row{grid-template-columns:120px 1fr}
    }
</style>

<?php
$this->assign('title', $product->name);
$currency = $product->currency ?: 'AUD';
?>
<div class="modal-content">
    <header class="modal-header">
        <h2 id="product-modal-title"><?= h($product->name) ?></h2>
        <button type="button" class="modal-close" aria-label="Close">&times;</button>
    </header>

    <div class="modal-body">
        <div class="modal-media">
            <?php if (!empty($product->image_url)): ?>
                <img src="<?= h($product->image_url) ?>" alt="<?= h($product->name) ?>">
            <?php else: ?>
                <div class="ph">No Image</div>
            <?php endif; ?>
        </div>

        <div class="modal-info">
            <?php if (!empty($product->summary)): ?>
                <p class="summary"><?= h($product->summary) ?></p>
            <?php endif; ?>

            <div class="price-line">
                <div class="price">
                    <?= $this->Number->currency((float)($product->price ?? 0), $currency) ?>
                </div>
                <?php if ($product->stock !== null && $product->stock !== ''): ?>
                    <div class="stock">Stock: <?= (int)$product->stock ?></div>
                <?php endif; ?>
            </div>

            <form method="post"
                  action="<?= $this->Url->build(['controller'=>'Products','action'=>'addToCart', $product->id]) ?>">
                <div class="qty-row">
                    <label for="qty">Qty</label>
                    <input class="qty" type="number" min="1" name="qty" id="qty" value="1">
                </div>
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
                <dl class="spec-grid">
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
                        'Gluten free'   => $product->gluten_free ?? null,
                        'Lactose free'  => $product->lactose_free ?? null,
                        'Allergens'     => $product->allergens ?? null,
                        'Pairings'      => $product->pairing_notes ?? null,
                        'Awards'        => $product->awards ?? null,
                        'Rating'        => $product->rating ?? null,
                    ];
                    foreach ($specs as $k => $v):
                        if ($v === null || $v === '') continue; ?>
                        <div><dt><?= h($k) ?></dt><dd><?= h((string)$v) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-content{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 20px 60px rgba(2,6,23,.18);max-width:920px;width:calc(100vw - 2rem)}
    .theme-dark .modal-content{background:#111827;border-color:#1f2937;box-shadow:0 30px 80px rgba(0,0,0,.6)}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.1rem;border-bottom:1px solid #eef0f3}
    .theme-dark .modal-header{border-color:#1f2937}
    .modal-header h2{margin:0;font-size:1.3rem;font-weight:800}
    .modal-close{font-size:1.8rem;line-height:1;border:0;background:transparent;cursor:pointer;color:#6b7280}
    .modal-close:hover{color:#111}
    .theme-dark .modal-close{color:#cbd5e1}
    .modal-body{display:grid;grid-template-columns:1.3fr 1.7fr;gap:1rem;padding:1rem}
    @media (max-width:900px){.modal-body{grid-template-columns:1fr}}
    .modal-media{background:#f3f4f6;border-radius:.9rem;min-height:280px;display:grid;place-items:center;overflow:hidden}
    .modal-media img{width:100%;height:100%;object-fit:cover}
    .theme-dark .modal-media{background:#0f172a}
    .sum

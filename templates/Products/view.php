<?php
$this->assign('title', $product->name);
$currency = $product->currency ?: 'AUD';
?>
<div class="product-page">
    <div class="product-wrap">
        <div class="media">
            <?php if (!empty($product->image_url)): ?>
                <img src="<?= h($product->image_url) ?>" alt="<?= h($product->name) ?>">
            <?php else: ?>
                <div class="ph">No Image</div>
            <?php endif; ?>
        </div>

        <div class="info card">
            <h1 class="title"><?= h($product->name) ?></h1>

            <?php if (!empty($product->summary)): ?>
                <p class="summary"><?= h($product->summary) ?></p>
            <?php endif; ?>

            <div class="price-line">
                <div class="price"><?= $this->Number->currency((float)($product->price ?? 0), $currency) ?></div>
                <?php if ($product->stock !== null && $product->stock !== ''): ?>
                    <div class="stock">Stock: <?= (int)$product->stock ?></div>
                <?php endif; ?>
            </div>

            <form method="post" action="<?= $this->Url->build(['controller'=>'Products','action'=>'addToCart', $product->id]) ?>">
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
        </div>
    </div>

    <div class="specs card">
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
                'Gluten free'   => $product->gluten_free ?? null,
                'Lactose free'  => $product->lactose_free ?? null,
                'Allergens'     => $product->allergens ?? null,
                'Pairings'      => $product->pairing_notes ?? null,
                'Awards'        => $product->awards ?? null,
                'Rating'        => $product->rating ?? null,
                'Slug'          => $product->slug ?? null,
                'Created'       => $product->created ? $product->created->i18nFormat('yyyy-MM-dd HH:mm') : null,
                'Modified'      => $product->modified ? $product->modified->i18nFormat('yyyy-MM-dd HH:mm') : null,
            ];
            foreach ($specs as $label => $val):
                if ($val === null || $val === '') continue; ?>
                <div><span class="k"><?= h($label) ?></span><span class="v"><?= is_string($val) ? h($val) : $val ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<style>
    .product-page{max-width:1100px;margin:0 auto;padding:1.25rem 1rem;display:grid;gap:1rem}
    .product-wrap{display:grid;grid-template-columns:2fr 3fr;gap:1rem}
    @media (max-width:900px){.product-wrap{grid-template-columns:1fr}}
    .media{background:#f3f4f6;border-radius:1rem;overflow:hidden;border:1px solid #eef0f3;min-height:320px;display:grid;place-items:center}
    .media img{width:100%;height:100%;object-fit:cover;display:block}
    .media .ph{color:#9aa3af}
    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
    .theme-dark .card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    .info .title{margin:.25rem 0 .35rem}
    .info .summary{color:#6b7280}
    .price-line{display:flex;align-items:center;justify-content:space-between;margin:.8rem 0}
    .price{font-weight:800;font-size:1.25rem}
    .qty-row{display:flex;align-items:center;gap:.5rem;margin:.5rem 0 .9rem}
    .qty{width:84px;border-radius:.6rem;border:1px solid #e5e7eb;padding:.45rem .55rem;background:#f9fafb}
    .theme-dark .qty{background:#0f172a;border-color:#334155;color:#e5e7eb}
    .btn{display:inline-block;padding:.55rem .9rem;border-radius:.6rem;border:1px solid transparent;background:#e5e7eb;color:#111;text-decoration:none;cursor:pointer}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}
    .desc h3{margin:1rem 0 .4rem}
    .specs h3{margin:.1rem 0 .6rem}
    .spec-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem .8rem}
    .spec-grid .k{display:block;color:#6b7280;font-size:.9rem}
    .spec-grid .v{font-weight:600}
    @media (max-width:640px){.spec-grid{grid-template-columns:1fr}}
</style>

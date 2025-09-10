<?php
$this->assign('title', 'Checkout');
$items    = $items ?? [];
$currency = $currency ?? 'AUD';
$subtotal = $subtotal ?? 0;
$shipping = $shipping ?? 0;
$total    = $total ?? ($subtotal + $shipping);

// bank transfer variables coming from controller
$bankAccountName = $bankAccountName ?? 'Curd & Culture Pty Ltd';
$bankBsb         = $bankBsb ?? '000-000';
$bankAccountNo   = $bankAccountNo ?? '000000000';
?>
<div class="checkout-page">
    <div class="grid">
        <div class="card">
            <h2>Shipping details</h2>
            <?= $this->Form->create(null) ?>

            <div class="fg">
                <label>Full name</label>
                <input name="full_name" required value="<?= h($prefill['full_name'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>Email</label>
                <input type="email" name="email" required value="<?= h($prefill['email'] ?? '') ?>">
            </div>
            <div class="fg">
                <label>Address</label>
                <input name="address" required>
            </div>
            <div class="row2">
                <div class="fg"><label>City</label><input name="city" required></div>
                <div class="fg"><label>Postcode</label><input name="postcode" required></div>
            </div>
            <div class="fg">
                <label>Country</label>
                <input name="country" required value="Australia">
            </div>

            <div class="actions">
                <a class="btn btn-subtle" href="<?= $this->Url->build(['controller'=>'Cart','action'=>'index']) ?>">Back to cart</a>
                <button class="btn btn-primary">Place order</button>
            </div>

            <?= $this->Form->end() ?>
        </div>

        <aside class="card">
            <h3>Your items</h3>
            <ul class="mini">
                <?php foreach ((array)$items as $it): ?>
                    <li>
                        <div class="n"><?= h($it['name']) ?></div>
                        <div class="r"><?= (int)$it['qty'] ?> × <?= $this->Number->currency((float)$it['price'], $it['currency']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="muted">No payment gateway is connected in this demo.</p>

            <hr class="sep">

            <div class="bank-block">
                <h4 style="margin:0 0 .4rem">Pay by bank transfer</h4>
                <div class="kv"><span>Account name</span><strong><?= h($bankAccountName) ?></strong></div>
                <div class="kv"><span>BSB</span><strong><?= h($bankBsb) ?></strong></div>
                <div class="kv"><span>Account number</span><strong><?= h($bankAccountNo) ?></strong></div>
                <p class="muted" style="margin-top:.5rem">
                    Please include your email as the payment reference so we can match your order.
                    <br>
                    Once we receive your payment we will update the order status as soon as possible.
                </p>
            </div>
        </aside>
    </div>
</div>

<style>
    .checkout-page{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:1rem}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}

    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
    .theme-dark .card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    h2{margin:.1rem 0 .8rem}
    .fg{margin-bottom:.8rem}
    label{display:block;margin-bottom:.25rem;color:#6b7280}
    input{width:100%;border-radius:.6rem;border:1px solid #e5e7eb;padding:.55rem .7rem;background:#f9fafb}
    .theme-dark input{background:#0f172a;border-color:#334155;color:#e5e7eb}
    .row2{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}

    .mini{list-style:none;margin:0;padding:0;display:grid;gap:.45rem}
    .mini li{display:flex;align-items:center;justify-content:space-between}
    .muted{color:#6b7280;margin-top:.6rem}

    .btn{display:inline-block;padding:.45rem .75rem;border-radius:.55rem;border:1px solid #e4e7ec;background:#f3f5f7;color:#111;text-decoration:none;cursor:pointer}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}
    .actions{display:flex;justify-content:space-between;margin-top:.6rem}

    .sep{border:none;border-top:1px solid #eef0f3;margin:1rem 0}
    .bank-block .kv{display:flex;justify-content:space-between;padding:.25rem 0}
    .bank-block .kv span{color:#6b7280}
</style>

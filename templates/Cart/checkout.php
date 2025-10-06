<?php
$this->assign('title', 'Checkout');

$items    = $items    ?? [];
$currency = $currency ?? 'AUD';
$subtotal = $subtotal ?? 0;
$shipping = $shipping ?? 0;
$total    = $total    ?? ($subtotal + $shipping);

$bankAccountName = $bankAccountName ?? 'Curd & Culture Pty Ltd';
$bankBsb         = $bankBsb         ?? '000-000';
$bankAccountNo   = $bankAccountNo   ?? '000000000';

$deliverySlots   = $deliverySlots   ?? [];
$pickupLocations = $pickupLocations ?? [];
$today = date('Y-m-d');

// sticky values when post failed
$sticky = (array)$this->request->getData();
?>
<div class="checkout-page">

    <div class="progress">
        <div class="progress-steps" aria-label="Checkout progress">
            <span class="step done">Cart</span>
            <span class="divider" aria-hidden="true">—</span>
            <span class="step current">Checkout</span>
            <span class="divider" aria-hidden="true">—</span>
            <span class="step">Complete</span>
        </div>
        <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="66">
            <span style="width:66%"></span>
        </div>
    </div>

    <div class="grid">
        <!-- MAIN -->
        <section class="card">
            <header class="card-hd">
                <h2 class="title">Shipping & Fulfillment</h2>
                <p class="sub">We’ll only use this information to fulfill your order.</p>
            </header>

            <?= $this->Form->create(null, ['id' => 'checkout-form', 'aria-describedby' => 'form-help']) ?>

            <fieldset class="fs">
                <div class="fg">
                    <label for="full_name">Full name</label>
                    <input id="full_name"
                           name="full_name"
                           required
                           autocomplete="name"
                           placeholder="e.g. Alex Johnson"
                           value="<?= h($sticky['full_name'] ?? ($prefill['full_name'] ?? '')) ?>">
                </div>

                <div class="fg">
                    <label for="email">Email</label>
                    <input id="email"
                           type="email"
                           name="email"
                           required
                           autocomplete="email"
                           placeholder="you@example.com"
                           value="<?= h($sticky['email'] ?? ($prefill['email'] ?? '')) ?>">
                </div>

                <div class="fg">
                    <label for="address">Address</label>
                    <input id="address"
                           name="address"
                           required
                           autocomplete="address-line1"
                           placeholder="Street, number and unit"
                           value="<?= h($sticky['address'] ?? '') ?>">
                </div>

                <div class="row2">
                    <div class="fg">
                        <label for="city">City</label>
                        <input id="city"
                               name="city"
                               required
                               autocomplete="address-level2"
                               placeholder="Suburb / City"
                               value="<?= h($sticky['city'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label for="postcode">Postcode</label>
                        <input id="postcode"
                               name="postcode"
                               required
                               inputmode="numeric"
                               autocomplete="postal-code"
                               placeholder="Postcode"
                               value="<?= h($sticky['postcode'] ?? '') ?>">
                    </div>
                </div>

                <div class="fg">
                    <label for="country">Country</label>
                    <input id="country"
                           name="country"
                           required
                           autocomplete="country-name"
                           value="<?= h($sticky['country'] ?? 'Australia') ?>">
                </div>
            </fieldset>

            <!-- Fulfillment -->
            <fieldset class="fs">
                <h3 class="title sm">Fulfillment</h3>
                <?php
                $selMethod = $sticky['fulfillment_method'] ?? 'delivery';
                $isPickup  = ($selMethod === 'pickup');
                ?>
                <div class="fg radios">
                    <label class="radio">
                        <input type="radio" name="fulfillment_method" value="delivery" <?= $isPickup ? '' : 'checked' ?>>
                        <span>Home Delivery</span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="fulfillment_method" value="pickup" <?= $isPickup ? 'checked' : '' ?>>
                        <span>Click &amp; Collect (in-store pickup)</span>
                    </label>
                </div>

                <!-- Delivery-only -->
                <div id="delivery-fields" <?= $isPickup ? 'hidden' : '' ?>>
                    <div class="row2">
                        <div class="fg">
                            <label for="delivery_date">Delivery date</label>
                            <input id="delivery_date"
                                   name="delivery_date"
                                   type="date"
                                   min="<?= h($today) ?>"
                                   value="<?= h($sticky['delivery_date'] ?? '') ?>">
                        </div>
                        <div class="fg">
                            <label for="delivery_slot_id">Preferred time slot</label>
                            <select id="delivery_slot_id" name="delivery_slot_id">
                                <option value="">Select a time slot…</option>
                                <?php foreach ((array)$deliverySlots as $slot): ?>
                                    <?php
                                    $label = (string)($slot['name'] ?? 'Slot');
                                    $ws = isset($slot['window_start']) ? substr((string)$slot['window_start'], 0, 5) : '';
                                    $we = isset($slot['window_end'])   ? substr((string)$slot['window_end'], 0, 5)   : '';
                                    if ($ws && $we) { $label .= " ({$ws}–{$we})"; }
                                    $cap = $slot['capacity'] ?? null;
                                    if ($cap) { $label .= " · cap {$cap}"; }
                                    $selected = ((int)($sticky['delivery_slot_id'] ?? 0) === (int)$slot['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= (int)$slot['id'] ?>" <?= $selected ?>><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="fg">
                        <label for="delivery_instructions">Delivery instructions (optional)</label>
                        <textarea id="delivery_instructions" name="delivery_instructions" rows="2"
                                  placeholder="Gate code, safe drop note, contactless delivery…"><?= h($sticky['delivery_instructions'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Pickup-only -->
                <div id="pickup-fields" <?= $isPickup ? '' : 'hidden' ?>>
                    <div class="fg">
                        <label for="pickup_location_id">Pickup location</label>
                        <select id="pickup_location_id" name="pickup_location_id">
                            <option value="">Select a store…</option>
                            <?php foreach ((array)$pickupLocations as $loc): ?>
                                <?php
                                $line = (string)($loc['name'] ?? 'Store');
                                $addr = trim(($loc['address_line_1'] ?? '')
                                    . ', ' . ($loc['suburb'] ?? '')
                                    . ' ' . ($loc['state'] ?? '')
                                    . ' ' . ($loc['postcode'] ?? ''));
                                if ($addr) $line .= ' — ' . $addr;
                                $selected = ((int)($sticky['pickup_location_id'] ?? 0) === (int)$loc['id']) ? 'selected' : '';
                                ?>
                                <option value="<?= (int)$loc['id'] ?>" <?= $selected ?>><?= h($line) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="muted tiny" style="margin-top:.35rem">
                            Picking up? Shipping becomes <strong>free</strong>. Bring your order confirmation at collection.
                        </p>
                    </div>
                </div>
            </fieldset>

            <p id="form-help" class="muted small">
                All payments are encrypted. Your data is protected and used only to fulfil your order.
            </p>

            <div class="actions">
                <a class="btn btn-subtle" href="<?= $this->Url->build(['controller'=>'Cart','action'=>'index']) ?>">Back to cart</a>

                <!-- Native POST to CartController::checkout() (bank transfer) -->
                <button class="btn" title="Place order and pay via bank transfer">
                    Place order (Bank transfer)
                </button>

                <!-- Stripe: will set action to Payments/checkout -->
                <button type="button" id="btn-stripe" class="btn btn-primary">
                    <span class="lock" aria-hidden="true"></span>
                    Pay with card
                    <span class="brands" aria-hidden="true">
                        <svg viewBox="0 0 36 12" class="brand"><rect x="0" y="0" width="36" height="12" rx="2"/></svg>
                        <svg viewBox="0 0 36 12" class="brand"><rect x="0" y="0" width="36" height="12" rx="2"/></svg>
                        <svg viewBox="0 0 36 12" class="brand"><rect x="0" y="0" width="36" height="12" rx="2"/></svg>
                    </span>
                </button>
            </div>

            <?= $this->Form->end() ?>
        </section>

        <!-- SIDEBAR -->
        <aside class="card aside-sticky" aria-label="Order summary">
            <h3 class="title sm">Your order</h3>

            <?php if (!empty($items)): ?>
                <ul class="mini">
                    <?php foreach ((array)$items as $it): ?>
                        <li>
                            <div class="avatar" aria-hidden="true"><?= mb_strtoupper(mb_substr((string)$it['name'], 0, 1)) ?></div>
                            <div class="line">
                                <div class="n"><?= h($it['name']) ?></div>
                                <div class="meta">
                                    <span class="qty"><?= (int)$it['qty'] ?> ×</span>
                                    <span class="price"><?= $this->Number->currency((float)$it['price'], $it['currency']) ?></span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">Your cart is empty.</p>
            <?php endif; ?>

            <hr class="sep">

            <div class="totals" role="table" aria-label="Price breakdown">
                <div class="row" role="row">
                    <div class="k" role="cell">Subtotal</div>
                    <div class="v" role="cell" id="subtotal-val"><?= $this->Number->currency((float)$subtotal, $currency) ?></div>
                </div>
                <div class="row" role="row">
                    <div class="k" role="cell">Shipping</div>
                    <div class="v" role="cell" id="shipping-val"><?= $this->Number->currency((float)$shipping, $currency) ?></div>
                </div>
                <div class="row total" role="row">
                    <div class="k" role="cell">Total</div>
                    <div class="v" role="cell" id="total-val"><?= $this->Number->currency((float)$total, $currency) ?></div>
                </div>
                <p class="muted tiny">Prices include GST where applicable.</p>
            </div>

            <details class="bank">
                <summary>Pay by bank transfer</summary>
                <div class="bank-block">
                    <div class="kv"><span>Account name</span><strong><?= h($bankAccountName) ?></strong></div>
                    <div class="kv"><span>BSB</span><strong><?= h($bankBsb) ?></strong></div>
                    <div class="kv"><span>Account number</span><strong><?= h($bankAccountNo) ?></strong></div>
                    <p class="muted" style="margin-top:.5rem">
                        Please include your <strong>email</strong> as the payment reference so we can match your order.
                        We will update your order status as soon as we receive your payment.
                    </p>
                </div>
            </details>
        </aside>
    </div>
</div>

<script>
    (function(){
        // Toggle delivery vs pickup + live price preview
        const methodRadios = document.querySelectorAll('input[name="fulfillment_method"]');
        const elDelivery   = document.getElementById('delivery-fields');
        const elPickup     = document.getElementById('pickup-fields');

        const shippingPhp  = <?= json_encode((float)$shipping) ?>;
        const subtotalPhp  = <?= json_encode((float)$subtotal) ?>;
        const currencyIso  = <?= json_encode((string)$currency) ?>;

        function fmtMoney(num){
            return new Intl.NumberFormat(undefined, { style: 'currency', currency: currencyIso }).format(num);
        }

        function updatePricePreview(){
            const method = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery';
            const shipping = (method === 'pickup') ? 0 : shippingPhp;
            const total = subtotalPhp + shipping;
            const sv = document.getElementById('shipping-val');
            const tv = document.getElementById('total-val');
            if (sv) sv.textContent = fmtMoney(shipping);
            if (tv) tv.textContent = fmtMoney(total);
        }

        function toggleBlocks(){
            const isPickup = document.querySelector('input[name="fulfillment_method"]:checked')?.value === 'pickup';
            elDelivery.hidden = isPickup;
            elPickup.hidden   = !isPickup;
            updatePricePreview();
        }

        methodRadios.forEach(r => r.addEventListener('change', toggleBlocks));
        toggleBlocks();

        // Stripe button -> post to Payments/checkout
        document.getElementById('btn-stripe')?.addEventListener('click', function () {
            const form = document.getElementById('checkout-form');
            if (!form) return;

            const req = ['full_name','email','address','city','postcode','country'];
            for (const id of req) {
                const el = document.getElementById(id);
                if (el && !el.value.trim()) { el.focus(); return; }
            }

            const method = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery';
            if (method === 'delivery') {
                const dd = document.getElementById('delivery_date');
                const ds = document.getElementById('delivery_slot_id');
                if (!dd?.value) { dd.focus(); return; }
                if (!ds?.value) { ds.focus(); return; }
            } else {
                const pl = document.getElementById('pickup_location_id');
                if (!pl?.value) { pl.focus(); return; }
            }

            form.action = "<?= $this->Url->build(['controller'=>'Payments','action'=>'checkout']) ?>";
            form.method = "post";
            form.submit();
        });
    })();
</script>

<style>
    .checkout-page{max-width:1100px;margin:0 auto;padding:1.25rem 1rem}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:1rem}
    @media (max-width:960px){.grid{grid-template-columns:1fr}}
    .aside-sticky{position:sticky;top:1rem}

    .progress{margin:0 0 1rem}
    .progress-steps{font-size:.9rem;color:#6b7280;display:flex;align-items:center;gap:.4rem}
    .progress-steps .step.done{color:#10b981}
    .progress-steps .step.current{color:#111827}
    .progress-bar{height:6px;background:#eef0f3;border-radius:999px;margin-top:.4rem;overflow:hidden}
    .progress-bar>span{display:block;height:100%;background:linear-gradient(90deg,#60a5fa,#2c7be5)}

    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1rem}
    .card-hd{margin-bottom:.5rem}
    .title{margin:.1rem 0 .2rem}
    .title.sm{font-size:1.1rem;margin-bottom:.35rem}
    .sub{color:#6b7280;margin:0}

    .fs{margin-top:.6rem}
    .fg{margin-bottom:.85rem}
    label{display:block;margin-bottom:.25rem;color:#6b7280}
    input, select, textarea{
        width:100%;border-radius:.65rem;border:1px solid #e5e7eb;
        padding:.62rem .8rem;background:#f9fafb;transition:box-shadow .15s,border-color .15s,background .15s
    }
    input:focus, select:focus, textarea:focus{outline:none;border-color:#93c5fd;box-shadow:0 0 0 3px rgba(147,197,253,.45);background:#fff}
    textarea{resize:vertical}
    .row2{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
    .radios{display:flex;gap:1rem;align-items:center}
    .radio{display:inline-flex;gap:.45rem;align-items:center;cursor:pointer;color:#374151}

    .muted{color:#6b7280}
    .small{font-size:.9rem}
    .tiny{font-size:.8rem}

    .actions{display:flex;flex-wrap:wrap;gap:.6rem;justify-content:flex-end;margin-top:.8rem}
    .btn{
        display:inline-flex;align-items:center;gap:.45rem;
        padding:.54rem .9rem;border-radius:.65rem;border:1px solid #e4e7ec;
        background:#f3f5f7;color:#111;text-decoration:none;cursor:pointer;transition:transform .05s ease,box-shadow .15s
    }
    .btn:active{transform:translateY(1px)}
    .btn-subtle{background:transparent;border-color:#d1d5db;color:#374151}
    .btn-primary{
        background:linear-gradient(180deg,#2c7be5,#1d63c6);color:#fff;border-color:transparent;
        box-shadow:0 6px 18px rgba(37,99,235,.25)
    }
    .btn-primary:hover{filter:brightness(1.03)}
    .btn .brands{display:inline-flex;gap:.25rem;margin-left:.2rem}
    .btn .brand{width:24px;height:12px;opacity:.6}
    .btn .brand rect{fill:#fff}

    .mini{list-style:none;margin:.2rem 0 0;padding:0;display:grid;gap:.55rem}
    .mini li{display:grid;grid-template-columns:32px 1fr;gap:.6rem;align-items:center}
    .avatar{
        width:32px;height:32px;border-radius:50%;background:#eef2ff;color:#3730a3;
        display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem
    }
    .line .n{font-weight:600}
    .line .meta{color:#6b7280;font-size:.92rem}
    .line .meta .qty{margin-right:.25rem}

    .sep{border:none;border-top:1px solid #eef0f3;margin:1rem 0}

    .totals{display:grid;gap:.35rem}
    .totals .row{display:flex;justify-content:space-between;align-items:center}
    .totals .total{font-weight:700}
    .totals .total .v{font-size:1.15rem}

    details.bank summary{cursor:pointer;font-weight:600;margin:.2rem 0 .4rem}
    .bank-block .kv{display:flex;justify-content:space-between;padding:.25rem 0}
    .bank-block .kv span{color:#6b7280}

    .theme-dark .card{background:#0b1020;border-color:#121a2d;box-shadow:0 16px 48px rgba(0,0,0,.45)}
    .theme-dark input, .theme-dark select, .theme-dark textarea{background:#0f172a;border-color:#334155;color:#e5e7eb}
    .theme-dark .btn{background:#18202f;color:#e5e7eb;border-color:#2b3546}
    .theme-dark .btn-primary{background:linear-gradient(180deg,#60a5fa,#2563eb);color:#0b1020}
    .theme-dark .avatar{background:#0a172e;color:#8ab4ff}
    .contrast-high .btn-primary,.contrast-high .progress-bar>span{filter:none}
</style>

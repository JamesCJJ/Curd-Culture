<?php
$this->assign('title', 'Order complete');
?>
<div class="complete-page">
    <div class="card">
        <h1>Thank you!</h1>
        <p>Your order has been placed.</p>
        <a class="btn btn-primary" href="<?= $this->Url->build(['controller'=>'Products','action'=>'index']) ?>">Continue shopping</a>
    </div>
</div>

<style>
    .complete-page{max-width:800px;margin:0 auto;padding:1.5rem 1rem}
    .card{background:#fff;border:1px solid #eef0f3;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:1.25rem;display:grid;gap:.6rem;place-items:center}
    .theme-dark .card{background:#111827;border-color:#1f2937;box-shadow:0 16px 48px rgba(0,0,0,.35)}
    .btn{display:inline-block;padding:.45rem .75rem;border-radius:.55rem;border:1px solid #e4e7ec;background:#f3f5f7;color:#111;text-decoration:none}
    .btn-primary{background:#2c7be5;color:#fff;border-color:transparent}
    .theme-dark .btn{background:#1f2937;color:#fff;border-color:#475569}
    .theme-dark .btn-primary{background:#60a5fa;color:#111}
</style>

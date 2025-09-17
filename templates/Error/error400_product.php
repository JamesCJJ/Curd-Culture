<?php
$this->assign('title', 'Product Not Found');
?>
<div class="error-page">
    <h1>Product Not Found</h1>
    <p>Sorry, the product you are looking for does not exist or is no longer available.</p >
    <?= $this->Html->link(
        'Back to Products',
        ['controller' => 'Products', 'action' => 'index'],
        ['class' => 'btn btn-primary']
    ) ?>
</div>

<style>
    .error-page{max-width:700px;margin:4rem auto;text-align:center;padding:0 1rem}
    .error-page h1{font-size:2rem;margin-bottom:1rem}
    .error-page p{margin-bottom:2rem;color:#6b7280}
    .btn.btn-primary{background:#2563eb;color:#fff;padding:.6rem 1rem;border-radius:.5rem;text-decoration:none;border:0}
    .theme-dark .btn.btn-primary{background:#60a5fa;color:#111}
</style>

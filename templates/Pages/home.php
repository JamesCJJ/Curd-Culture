<div class="hero">
    <h1>Welcome</h1>
    <p class="lead">Landing page with a clear CTA.</p>

    <p>
        <?= $this->Html->link(
            'Contact Us',
            ['controller' => 'ContactMessages', 'action' => 'add'],
            ['class' => 'button']
        ) ?>
    </p>

    <p>
        <?= $this->Html->link(
            'Admin Login',
            ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'login'],
            ['class' => 'button']
        ) ?>
    </p>
</div>

<style>
    .hero { text-align:center; padding: 3rem 1rem; }
    .button { display:inline-block; padding:.75rem 1.25rem; border-radius:.5rem; background:#0366d6; color:#fff; text-decoration:none; margin: 0.5rem; }
    .button:hover { opacity:.9; }
</style>

<div class="stats" style="display:flex; gap:1rem; flex-wrap:wrap;">
  <div class="card" style="flex:1; min-width:220px; border:1px solid #ddd; border-radius:.75rem; padding:1rem;">
    <h3 style="margin:.25rem 0;">Total Submissions</h3>
    <p style="font-size:2rem; font-weight:700;"><?= h($total) ?></p>
  </div>
  <div class="card" style="flex:1; min-width:220px; border:1px solid #ddd; border-radius:.75rem; padding:1rem;">
    <h3 style="margin:.25rem 0;">Today</h3>
    <p style="font-size:2rem; font-weight:700;"><?= h($today) ?></p>
  </div>
</div>
<div style="margin-top:1rem;">
  <a class="button" href="/admin/contact-messages">View Submissions</a>
    <?= $this->Html->link(
        'View Submissions',
        ['prefix' => 'Admin', 'controller' => 'ContactMessages', 'action' => 'index'],
        ['class' => 'button']
    ) ?>

</div>
<style>
.button { display:inline-block; padding:.5rem 1rem; border-radius:.5rem; background:#0366d6; color:#fff; text-decoration:none; }
.button:hover { opacity:.9; }
</style>

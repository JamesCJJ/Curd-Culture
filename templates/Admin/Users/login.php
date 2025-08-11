<div class="row">
  <div class="column-responsive column-50" style="max-width:420px;margin:0 auto;">
    <h2>Admin Login</h2>
    <?= $this->Flash->render() ?>
    <?= $this->Form->create() ?>
    <?= $this->Form->control('email') ?>
    <?= $this->Form->control('password', ['type' => 'password']) ?>
    <div style="margin-top:1rem">
      <?= $this->Form->button(__('Login')) ?>
    </div>
    <?= $this->Form->end() ?>
  </div>
</div>

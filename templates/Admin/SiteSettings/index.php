<?php
/**
 * @var \App\View\AppView $this
 * @var array $fields
 * @var array $settings
 */
$this->assign('title', 'Homepage Content');
?>
<script src="https://cdn.tiny.cloud/1/sizbqq8iu7pdltiy3ifusu49ug6uzv7zbjpke2qdglwkkigc/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (window.tinymce) {
    tinymce.init({
      selector: 'textarea[data-editor="wysiwyg"]',
      height: 300,
      menubar: false,
      plugins: 'link lists code table',
      toolbar: 'undo redo | bold italic underline | bullist numlist | link | table | code',
      setup: (ed) => ed.on('change', () => ed.save())
    });
  }
});
document.addEventListener('submit', function(){ try{ if (window.tinymce) tinymce.triggerSave(); }catch(e){} }, true);
</script>

<div class="admin-section">
  <div class="card">
    <div class="card-header"><h2>Homepage Content</h2></div>
    <div class="card-body">
      <?= $this->Form->create(null, ['type' => 'file']) ?>
        <?php foreach ($fields as $key => $meta): ?>
          <div class="field-group" style="margin-bottom:1rem;">
            <label for="<?= h($key) ?>"><strong><?= h($meta['label']) ?></strong></label>
            <?php if ($meta['type'] === 'image'): ?>
              <?php if (!empty($settings[$key])): ?>
                <div style="margin:.5rem 0;">
                  <?= $this->Html->image($settings[$key], ['style' => 'max-width:220px;height:auto;border:1px solid #eee;border-radius:8px']) ?>
                </div>
              <?php endif; ?>
              <?= $this->Form->control("site_settings_files.$key", [
                    'type' => 'file', 'label' => false, 'accept' => 'image/*'
              ]) ?>
              <div class="form-text">PNG/JPG/WebP/GIF. Uploading replaces the current image.</div>
            <?php elseif ($meta['type'] === 'textarea'): ?>
              <?= $this->Form->textarea("site_settings.$key", [
                    'id' => $key,
                    'value' => $settings[$key] ?? '',
                    'data-editor' => 'wysiwyg'
              ]) ?>
            <?php else: ?>
              <?= $this->Form->control("site_settings.$key", [
                    'label' => false,
                    'id' => $key,
                    'value' => $settings[$key] ?? '',
              ]) ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?= $this->Form->button('Save Changes', ['class' => 'button button-primary']) ?>
      <?= $this->Form->end() ?>
    </div>
  </div>
  <p class="hint">Tip: Use the bold and list tools to format longer paragraphs.</p>
</div>



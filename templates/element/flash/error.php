<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="flash-message flash-error" onclick="this.classList.add('hidden');">
    <div class="flash-content">
        <span class="flash-icon">⚠</span>
        <span class="flash-text"><?= $message ?></span>
        <button class="flash-close" onclick="event.stopPropagation(); this.parentElement.parentElement.classList.add('hidden');" aria-label="Close message">×</button>
    </div>
</div>

<!-- Simple HTML email -->
<p>Hello<?= isset($user->email) ? ' ' . h($user->email) : '' ?>,</p>
<p>Your password reset code is:</p>
<h2 style="font-size:22px;letter-spacing:2px"><?= h($code) ?></h2>
<p>This code will expire in <strong><?= (int)$ttlMinutes ?></strong> minutes.</p>
<p>If you did not request this, you can ignore this email.</p>
<p>— <?= h($appName ?? 'Curd & Culture') ?></p>

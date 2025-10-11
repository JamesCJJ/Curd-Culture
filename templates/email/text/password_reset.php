Hello<?= isset($user->email) ? ' ' . $user->email : '' ?>,

Your password reset code is: <?= $code ?>

This code will expire in <?= (int)$ttlMinutes ?> minutes.

If you did not request this, you can ignore this email.

— <?= $appName ?? 'Curd & Culture' ?>


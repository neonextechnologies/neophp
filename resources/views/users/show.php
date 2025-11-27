<?php $this->layout('layouts.app'); ?>

<h1><?= $this->e($user['name']) ?></h1>

<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <p><strong>Email:</strong> <?= $this->e($user['email']) ?></p>
    <p><strong>Status:</strong> <?= $this->e($user['status']) ?></p>
    <p><strong>Created:</strong> <?= $user['created_at'] ?></p>
</div>

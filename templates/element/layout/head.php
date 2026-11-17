<?php
/**
 * Shared <head>. Pass 'css' and 'scripts' arrays for layout-specific assets.
 *
 * @var \App\View\AppView $this
 * @var list<string> $css
 * @var list<string> $scripts
 */
?>
<meta charset="utf-8">
<title><?= h($this->fetch('title') ?: 'Admin') ?></title>
<meta name="HandheldFriendly" content="True">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrfToken" content="<?= h($this->request->getAttribute('csrfToken')) ?>">
<?= $this->Html->meta('icon') ?>
<?= $this->Html->css($css ?? []) ?>
<?= $this->fetch('css') ?>
<?= $this->Html->script('/vendor/jquery-4.0.0/jquery.min.js') ?>
<?= $this->Html->script($scripts ?? []) ?>
<?= $this->fetch('script') ?>

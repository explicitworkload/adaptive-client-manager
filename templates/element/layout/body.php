<?php
use Cake\Core\Configure;

/**
 * Page body shared by the default, client and settings layouts.
 *
 * @var \App\View\AppView $this
 * @var array|null $currentUser
 * @var string|null $title
 * @var string $defaultTitle
 * @var string $titleClass
 * @var bool $showSettingsLink
 * @var string $nav rendered navigation tiles
 */
$currentUser = $this->get('currentUser');
?>
<div class="wrapper">
    <?= $this->element('layout/header', ['menuButton' => true]) ?>

    <?php if (!empty($showUserBar) && $currentUser) : ?>
        <div class="settings_tile">
            <div class="settings_tile_container">
                Welcome <?= h($currentUser['first_name'] ?? '') ?> |
                <?= $this->Html->link('Sign Out', ['controller' => 'Users', 'action' => 'logout']) ?>
                <?php if ($showSettingsLink && ($currentUser['role'] ?? null) === 'admin') : ?>
                    | <?= $this->Html->link('Settings', ['controller' => 'Settings', 'action' => 'editConfig', 1]) ?>
                <?php endif; ?>
                <?php if ($showSettingsLink) : ?>| V<?= h(Configure::read('App.version')) ?><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="<?= h($titleClass) ?>">
        <div class="page_title"><h1><?= h($this->get('title') ?? $defaultTitle) ?></h1></div>
    </div>

    <div class="nav_tile">
        <div class="nav_tile_container">
            <div class="container">
                <?= $nav ?>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="content_container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </div>

    <div class="info_tile">
        <div class="info_tile_container">
            <?= $this->fetch('info_tile') ?>
        </div>
    </div>

    <div class="push"></div>
</div>
<?= $this->element('layout/footer') ?>

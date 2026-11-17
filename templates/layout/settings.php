<?php
/**
 * Admin settings area layout.
 *
 * @var \App\View\AppView $this
 */
$nav = $this->element('layout/nav_item', ['icon' => 'icon-cog', 'label' => 'CONFIG', 'class' => 'settings', 'url' => ['controller' => 'Settings', 'action' => 'editConfig', 1]])
    . $this->element('layout/nav_item', ['icon' => 'icon-droplet', 'label' => 'THEME', 'class' => 'settings', 'url' => ['controller' => 'Settings', 'action' => 'editTheme', 1]])
    . $this->element('layout/nav_item', ['icon' => 'icon-user', 'label' => 'USERS', 'class' => 'settings', 'url' => ['controller' => 'Users', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-mail', 'label' => 'EMAIL', 'class' => 'settings', 'url' => ['controller' => 'EmailTemplates', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-clipboard', 'label' => 'TAX RATES', 'class' => 'settings', 'url' => ['controller' => 'Taxrates', 'action' => 'index']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->element('layout/head', [
        'css' => ['skeleton', 'dead_pixel', 'confirm'],
        'scripts' => ['jquery.reveal', 'dead_pixel'],
    ]) ?>
</head>
<body>
    <?= $this->element('layout/body', [
        'nav' => $nav,
        'defaultTitle' => 'Settings',
        'titleClass' => 'settings_title_tile',
        'showUserBar' => false,
        'showSettingsLink' => false,
    ]) ?>
</body>
</html>

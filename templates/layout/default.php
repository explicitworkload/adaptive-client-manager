<?php
/**
 * Main staff layout (admins and managers).
 *
 * @var \App\View\AppView $this
 */
$nav = $this->element('layout/nav_item', ['icon' => 'icon-bars', 'label' => 'Dashboard', 'url' => ['controller' => 'Dashboard', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-user', 'label' => 'Clients', 'url' => ['controller' => 'Clients', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-briefcase', 'label' => 'Invoices', 'url' => ['controller' => 'Invoices', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-drawer-2', 'label' => 'Quotes', 'url' => ['controller' => 'Quotes', 'action' => 'index']])
    . $this->element('layout/nav_item', ['icon' => 'icon-folder', 'label' => 'Reports', 'url' => ['controller' => 'Reports', 'action' => 'index']]);
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
        'defaultTitle' => 'Dashboard',
        'titleClass' => 'title_tile',
        'showUserBar' => true,
        'showSettingsLink' => true,
    ]) ?>
</body>
</html>

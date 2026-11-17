<?php
/**
 * Layout for client users viewing their own area.
 *
 * @var \App\View\AppView $this
 */
$nav = $this->element('layout/nav_item', ['icon' => 'icon-home', 'label' => 'Client Area', 'url' => ['controller' => 'Dashboard', 'action' => 'index']])
    . '<div class="three columns" id="main_menu_statement"><div class="menu_label"><span class="icon-folder"></span><h2>'
    . $this->Html->link('Statement', ['controller' => 'Dashboard', 'action' => 'index'])
    . '</h2></div></div>'
    . '<div class="three columns"></div><div class="three columns"></div><div class="three columns"></div>';
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
        'defaultTitle' => 'Client Area',
        'titleClass' => 'title_tile',
        'showUserBar' => true,
        'showSettingsLink' => false,
    ]) ?>
</body>
</html>

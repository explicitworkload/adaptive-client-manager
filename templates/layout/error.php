<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->element('layout/head', ['css' => ['skeleton', 'dead_pixel']]) ?>
</head>
<body>
    <div class="wrapper">
        <?= $this->element('layout/header') ?>
        <div class="title_tile">
            <div class="page_title"><h1>Error</h1></div>
        </div>
        <div class="content">
            <div class="content_container">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
                <p><?= $this->Html->link('Back to the dashboard', '/') ?></p>
            </div>
        </div>
        <div class="push"></div>
    </div>
    <?= $this->element('layout/footer') ?>
</body>
</html>

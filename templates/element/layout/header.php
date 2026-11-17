<?php
/**
 * @var \App\View\AppView $this
 * @var bool $menuButton
 */
?>
<div class="header">
    <div class="header_container">
        <div class="logo">
            <?= $this->Html->image('logo.png', ['alt' => 'Logo', 'width' => '214', 'height' => '36']) ?>
        </div>
        <?php if (!empty($menuButton)) : ?>
            <div class="menu_button icon-grid-view"></div>
        <?php endif; ?>
    </div>
</div>

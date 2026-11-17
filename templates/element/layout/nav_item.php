<?php
/**
 * One tile in the navigation bar.
 *
 * @var \App\View\AppView $this
 * @var string $icon
 * @var string $label
 * @var array|string $url
 * @var string $class extra classes on the column
 */
?>
<div class="three columns <?= h($class ?? '') ?>"><div class="menu_label div_link"><span class="<?= h($icon) ?>"></span>
    <h2><?= $this->Html->link($label, $url) ?></h2></div></div>

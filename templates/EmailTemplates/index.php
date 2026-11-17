<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\EmailTemplate> $emailTemplates
 */
?>
<h3>Edit Email Templates</h3>

<p>Please Select An Email Template To Edit:</p>

<?= $this->Html->div(' div_link info_button_primary', '<span class="icon-mail "></span>' . $this->Html->link('Client Invoice Template', ['controller' => 'EmailTemplates', 'action' => 'editClientInvoice'])) ?>

<?= $this->Html->div(' div_link info_button_primary', '<span class="icon-mail "></span>' . $this->Html->link('Client Quote Template', ['controller' => 'EmailTemplates', 'action' => 'editClientQuote'])) ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

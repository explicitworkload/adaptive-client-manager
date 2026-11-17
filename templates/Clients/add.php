<?php
/**
 * Create A New Client.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 */
?>
<h3>Create A New Client</h3>

<?= $this->Form->create($client, ['id' => 'ClientAddForm', 'url' => ['action' => 'add']]) ?>

<p>Complete the form below to add a new client. Fields marked with a * must be completed.</p>

<table>

    <tr>
        <th>Client Details</th>
    </tr>

    <tr><td><?= $this->Form->control('client_name') ?></td></tr>

    <tr><td><?= $this->Form->control('client_contact') ?></td></tr>

    <tr><td><?= $this->Form->control('client_phone') ?></td></tr>

    <tr><td><?= $this->Form->control('client_email') ?></td></tr>

    <tr><td><?= $this->Form->control('client_address_line_1') ?></td></tr>

    <tr><td><?= $this->Form->control('client_address_line_2') ?></td></tr>

    <tr><td><?= $this->Form->control('client_town') ?></td></tr>

    <tr><td><?= $this->Form->control('client_city') ?></td></tr>

    <tr><td><?= $this->Form->control('client_state') ?></td></tr>

    <tr><td><?= $this->Form->control('client_zip') ?></td></tr>

    <tr><td><?= $this->Form->control('custom_1', ['label' => 'Custom Field (e.g. Tax Code: 1234 576)']) ?></td></tr>

</table>

<?= $this->Form->end() ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?php /* The submit button lives in the info tile, outside the form; the form attribute ties it to the form. */ ?>
<?= $this->Form->submit('Save Client', ['form' => 'ClientAddForm']) ?>
<?= $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $this->Html->link('Cancel', ['controller' => 'Clients', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

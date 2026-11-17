<?php
/**
 * List of all clients.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Client> $clients
 */
?>
<?php $this->Html->css('clientsIndex', ['block' => true]); ?>

<h3>View All Clients</h3>

<table class="responsive_table" id="clients_table">

    <thead>
        <tr>
            <th>Client Name</th>
            <th>Contact Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="table_icon no_sort">View/Edit</th>
        </tr>
    </thead>

    <?php foreach ($clients as $client) : ?>
        <tr>
            <td><?= h($client->client_name) ?></td>
            <td><?= h($client->client_contact) ?></td>
            <td><?= h($client->client_email) ?></td>
            <td><?= h($client->client_phone) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['action' => 'view', $client->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>'
    . $this->Html->link('Add Client', ['controller' => 'Clients', 'action' => 'add'])) ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-user"></span>'
    . $this->Html->link('Contact List', ['controller' => 'Reports', 'action' => 'clientContacts'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<?= $this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup']) ?>

<script type="text/javascript">

    $(document).ready(function() {

        if ( $('#clients_table')[0] ) { $('#clients_table').DataTable( {
        } ); }

    });

</script>

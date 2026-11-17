<?php
/**
 * All user accounts, split into staff and client tables.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
$this->Html->css('usersIndex', ['block' => true]);
$this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup'], ['block' => true]);
?>
<h3>View All Users</h3>

<p>Below is a table of all registered users. You can delete users using the 'Delete' buttons next to each user in the table. However, by design and for security reasons, attempting to delete the account that you are currently signed in as will fail.</p>

<p>If the default user 'Username: admin' 'Name: Demo User' from installation exists in this table it must be deleted for security reasons.</p>


<!-- STAFF ACCOUNTS -->

<div class="report_form">

<h4>Staff User Accounts</h4>

<table class="responsive_table" id="staff_table">

    <thead>
        <tr>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Role</th>
            <th class="table_icon no_sort">Actions</th>
        </tr>
    </thead>

    <?php foreach ($users as $user) : ?>
        <?php if ($user->role !== 'client') : ?>
            <tr>
                <td><?= h($user->username) ?></td>
                <td><?= h($user->first_name) ?></td>
                <td><?= h($user->last_name) ?></td>
                <td><?= h(strtoupper((string)$user->role)) ?></td>
                <td class="center">
                    <?= $this->Html->link('View/Edit', ['controller' => 'Users', 'action' => 'view', $user->id], ['class' => 'view', 'id' => 'user_' . $user->id]) ?>
                    <?= $this->Form->postLink('Delete', ['controller' => 'Users', 'action' => 'delete', $user->id], ['confirm' => 'Are you sure you want to permanently delete this user?', 'class' => 'user_delete']) ?>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>

</table>

</div>

<!-- // END STAFF ACCOUNTS -->


<!-- CLIENT ACCOUNTS -->

<div class="report_form">

<h4>Client User Accounts</h4>

<table class="responsive_table" id="client_table">

    <thead>
        <tr>
            <th>Username</th>
            <th>Client Name</th>
            <th>Role</th>
            <th class="table_icon no_sort">Actions</th>
        </tr>
    </thead>

    <?php foreach ($users as $user) : ?>
        <?php if ($user->role === 'client') : ?>
            <tr>
                <td><?= h($user->username) ?></td>
                <td><?= h($user->first_name) ?></td>
                <td><?= h(strtoupper((string)$user->role)) ?></td>
                <td class="center">
                    <?= $this->Html->link('View/Edit', ['controller' => 'Users', 'action' => 'view', $user->id], ['class' => 'view', 'id' => 'user_' . $user->id]) ?>
                    <?= $this->Form->postLink('Delete', ['controller' => 'Users', 'action' => 'delete', $user->id], ['confirm' => 'Are you sure you want to permanently delete this user?', 'class' => 'user_delete']) ?>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>

</table>

</div>

<!-- // END CLIENT ACCOUNTS -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $this->Html->link('Add User', ['controller' => 'Users', 'action' => 'add'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        if ($('#staff_table')[0]) {
            $('#staff_table').DataTable({
            });
        }

        if ($('#client_table')[0]) {
            $('#client_table').DataTable({
            });
        }

    });

</script>

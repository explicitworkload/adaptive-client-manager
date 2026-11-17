<?php
/**
 * Create a staff (admin/manager) or client user account.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array<string, string> $staffRoles
 * @var array<string, string> $clientNames client_name => client_name
 */
// After a failed POST, repopulate (and show errors in) only the form that was submitted.
$isClientPost = $user->role === 'client';
$staffEntity = $isClientPost ? null : $user;
$clientEntity = $isClientPost ? $user : null;
?>
<h3>Create A New User</h3>

<p>Please select what type of user account (staff or client) you wish to create:</p>

<!-- STAFF ACCOUNT -->

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-user "></span>Create A Staff Account') ?>

<div class="report_form" id="staffUserForm">

    <h4>Create A New Staff Account</h4>

    <p>Complete the form below to add a new staff user account. Fields marked with a * must be completed.</p>

    <p><span class="bold">Admin</span> user accounts have complete access to all of Adaptive Client Manager's features</p>

    <p><span class="bold">Manager</span> user accounts have limited access to Adaptive Client Manager's features. They can add/view/edit/delete Clients, Invoices and Quotes etc. They do not have access to Settings and Configuration and they can't view/add/edit/delete user accounts.</p>

    <?= $this->Form->create($staffEntity, ['id' => 'UserAddForm', 'url' => ['controller' => 'Users', 'action' => 'add']]) ?>

    <table>

        <tr>
            <th>User Details</th>
        </tr>

        <tr><td><?= $this->Form->control('role', ['options' => $staffRoles, 'label' => 'Role']) ?></td></tr>

        <tr><td><?= $this->Form->control('username') ?></td></tr>

        <tr><td><?= $this->Form->control('password', ['value' => '']) ?></td></tr>

        <tr><td><?= $this->Form->control('first_name') ?></td></tr>

        <tr><td><?= $this->Form->control('last_name') ?></td></tr>

        <tr><td><?= $this->Form->control('email') ?></td></tr>

    </table>

    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Save User', ['id' => 'save_staff_button']) ?>

</div>

<!-- // END STAFF ACCOUNT -->


<!-- CLIENT ACCOUNT -->

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-user "></span>Create A Client Account') ?>

<div class="report_form" id="clientUserForm">

    <h4>Create A New Client Account</h4>

    <p>Complete the form below to add a new client user account. Fields marked with a * must be completed.</p>

    <p><span class="bold">Client</span> user accounts are redirected to their own Client Area on login. They can only access their own Client Area and view (not edit or delete) their own Invoices and Quotes etc. All other areas of Adaptive Client Manager are locked down and redirect client back to their own area.</p>

    <?php /* Field ids are prefixed with "client-" so they don't clash with the staff form above. */ ?>
    <?= $this->Form->create($clientEntity, ['id' => 'addClientUser', 'url' => ['controller' => 'Users', 'action' => 'add']]) ?>

    <?= $this->Form->hidden('role', ['value' => 'client', 'id' => 'client-role']) ?>

    <table>

        <tr>
            <th>User Details</th>
        </tr>

        <tr><td><?= $this->Form->control('client_name', ['options' => $clientNames, 'id' => 'client-client-name']) ?></td></tr>

        <tr><td><?= $this->Form->control('username', ['id' => 'client-username', 'required' => true]) ?></td></tr>

        <tr><td><?= $this->Form->control('password', ['id' => 'client-password', 'value' => '', 'required' => true]) ?></td></tr>

        <tr><td><?= $this->Form->control('email', ['id' => 'client-email', 'required' => true]) ?></td></tr>

    </table>

    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Save User', ['id' => 'save_client_button']) ?>

</div>

<!-- // END CLIENT ACCOUNT -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    // Suggest a username from the chosen client name (the server sets the
    // client user's first/last name itself).
    function setUsername() {
        var currClientName = $('#client-client-name').val() || '';
        var newUsername = currClientName.replace(/[^a-z0-9]/gi, '').toLowerCase();
        $('#client-username').val(newUsername);
    }

    $(document).ready(function() {

        // Style Select Boxes

        $('select').wrap('<div class="styled_select" />');


        // Hide Forms By Default (but keep a form with validation errors open)

        $('.report_form').not(':has(.error-message)').hide();


        // Show Forms On Click

        $('.show_hide_button').click(function() {
            $('.report_form').slideUp();
            $(this).next('div').slideToggle();
        });


        // Save Staff User

        $('#save_staff_button').click(function() {
            $('#UserAddForm').submit();
        });


        // Save Client User

        $('#save_client_button').click(function() {
            $('#addClientUser').submit();
        });


        // Set Username

        $('#client-client-name').change(function() {
            setUsername();
        });

        if ($('#client-username').val() === '') {
            setUsername();
        }

    });

</script>

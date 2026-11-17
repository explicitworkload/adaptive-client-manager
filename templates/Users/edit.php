<?php
/**
 * Edit a user account. A blank password keeps the current one.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user (password unset)
 * @var array<string, string> $roles
 */
$userRole = $user->role;
?>
<h3>Edit User</h3>

<?= $this->Form->create($user, ['id' => 'UserEditForm', 'url' => ['controller' => 'Users', 'action' => 'edit', $user->id]]) ?>

<table>

    <tr>
        <th>User Details</th>
    </tr>

    <tr><td><?= $this->Form->control('username') ?></td></tr>

    <?php if ($userRole !== 'client') : ?>

        <tr><td><?= $this->Form->control('first_name') ?></td></tr>

        <tr><td><?= $this->Form->control('last_name') ?></td></tr>

        <tr><td><?= $this->Form->control('role', ['options' => $roles, 'label' => 'Role']) ?></td></tr>

    <?php else : ?>

        <?php /* The server ignores first_name and role for client users. */ ?>
        <tr><td><?= $this->Form->control('first_name', ['label' => 'Client Company (Can Not Be Changed)', 'readonly' => 'readonly']) ?></td></tr>

    <?php endif; ?>

    <tr><td><?= $this->Form->control('email') ?></td></tr>

    <tr><td><?= $this->Form->control('password', [
        'value' => '',
        'required' => false,
        'autocomplete' => 'new-password',
        'label' => 'Password (Leave Blank To Keep The Current Password)',
    ]) ?></td></tr>

</table>

<?php /* Lets the Enter key submit the form; the Save Changes button in the info tile does the same. */ ?>
<button type="submit" style="position:absolute;left:-9999px" tabindex="-1" aria-hidden="true">Save Changes</button>

<?= $this->Form->end() ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Changes', ['id' => 'save_user_button']) ?>
<?= $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $this->Html->link('Discard Changes', ['controller' => 'Users', 'action' => 'view', $user->id])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        // Style Select Boxes

        $('select').wrap('<div class="styled_select" />');


        // Save Changes Button

        $('#save_user_button').click(function() {
            $('#UserEditForm').submit();
        });

    });

</script>

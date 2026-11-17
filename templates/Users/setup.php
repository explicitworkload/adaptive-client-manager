<?php
/**
 * First-run page: create the first admin login (only shown while no users exist).
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('heading', 'SETUP');
?>
<div class="login_form">
    <?= $this->Form->create($user, ['id' => 'UserSetupForm']) ?>
    <table>
        <tr><th>Welcome! Create The First Admin Account</th></tr>
        <tr><td><?= $this->Form->control('username', ['required' => true, 'autofocus' => true]) ?></td></tr>
        <tr><td><?= $this->Form->control('password', ['required' => true, 'label' => 'Password (6-32 characters)']) ?></td></tr>
        <tr><td><?= $this->Form->control('first_name', ['required' => true]) ?></td></tr>
        <tr><td><?= $this->Form->control('last_name', ['required' => true]) ?></td></tr>
        <tr><td><?= $this->Form->control('email', ['required' => true]) ?></td></tr>
    </table>
    <button type="submit" style="position:absolute;left:-9999px" tabindex="-1" aria-hidden="true">Create</button>
    <?= $this->Form->end() ?>
</div>

<?= $this->Html->div('info_button_primary', '<span class="icon-key"></span>Create Admin', ['id' => 'setup_button']) ?>

<script>
    $('#setup_button').on('click', function() {
        $('#UserSetupForm').submit();
    });
</script>

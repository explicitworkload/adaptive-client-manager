<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="login_form">
    <?= $this->Form->create(null, ['id' => 'UserLoginForm']) ?>
    <table>
        <tr><th>Please Enter Your Username &amp; Password</th></tr>
        <tr><td><?= $this->Form->control('username', ['required' => true, 'autofocus' => true]) ?></td></tr>
        <tr><td><?= $this->Form->control('password', ['required' => true]) ?></td></tr>
    </table>
    <?php /* Lets the Enter key submit the form; the styled button below does the same. */ ?>
    <button type="submit" style="position:absolute;left:-9999px" tabindex="-1" aria-hidden="true">Login</button>
    <?= $this->Form->end() ?>
</div>

<?= $this->Html->div('info_button_primary', '<span class="icon-key"></span>Login', ['id' => 'login_button']) ?>

<script>
    $('#login_button').click(function() {
        $('#UserLoginForm').submit();
    });
</script>

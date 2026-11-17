<?php
/**
 * A single user account.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $thisUser
 */
$userRole = $thisUser->role;
?>
<h3>View User: <?= h($thisUser->username) ?></h3>

<div class="container">

    <!-- USER DETAILS -->

    <table>

        <tr><th>User Details</th></tr>

        <tr><td><span class="bold">Username: </span><?= h($thisUser->username) ?></td></tr>

        <?php if ($userRole !== 'client') : ?>

            <tr><td><span class="bold">First Name: </span><?= h($thisUser->first_name) ?></td></tr>

            <tr><td><span class="bold">Last Name: </span> <?= h($thisUser->last_name) ?></td></tr>

        <?php else : ?>

            <tr><td><span class="bold">Company: </span><?= h($thisUser->first_name) ?></td></tr>

        <?php endif; ?>

        <tr><td><span class="bold">Email: </span> <?= h($thisUser->email) ?></td></tr>

        <tr><td><span class="bold">Role: </span> <?= h(strtoupper((string)$userRole)) ?></td></tr>

        <tr><td><span class="bold">Created: </span> <?= $thisUser->created ? h($thisUser->created->format('Y-m-d H:i:s')) : '' ?></td></tr>

    </table>

    <!-- // END USER DETAILS -->

</div>

<!-- // END CONTAINER -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>

<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>

<?= $this->Html->div('div_link info_button_primary', '<span class="icon-pencil"></span>' . $this->Html->link('Edit User', ['action' => 'edit', $thisUser->id])) ?>

<?= $this->Html->div('info_button_negative', '<span class="icon-cancel"></span><a href="">Delete User</a>', ['id' => 'delete_button']) ?>


<!-- MODAL -->

<div class="modal">

    <div id="heading">Are you sure you want to do that?</div>

    <div id="content">

        <p>Are you sure you want to delete this user?<br>This action cannot be reversed...</p>

        <?= $this->Form->postLink('YES, DO IT', ['action' => 'delete', $thisUser->id], ['class' => 'confirm_button_confirm']) ?>

        <a href="#" class="confirm_button_cancel close">NO, CANCEL</a>

    </div>

</div>

<!-- // END MODAL -->

<?php $this->end(); ?>

<!-- // END INFO TILE -->


<script type="text/javascript">

    $(document).ready(function() {

        // Delete Modal

        $('#delete_button').click(function(e) {

            $('.modal').reveal({
                animation: 'fade',
                animationspeed: 400,
                closeonbackgroundclick: true,
                dismissmodalclass: 'close'
            });

            $("html, body").animate({ scrollTop: 0 }, 600);

            return false;

        });

    });
</script>

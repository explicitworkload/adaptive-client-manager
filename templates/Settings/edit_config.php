<?php
/**
 * Company details, plus a separate logo upload form.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Setting $setting
 */
// No ?timestamp cache-buster: some hosting firewalls answer static files with a
// query string with 403 Forbidden.
$logoSrc = 'logo.png';
?>
<h3>Edit Config Settings</h3>

<?php /* The logo upload is its own form (posting to Settings::uploadLogo), kept outside the settings form. */ ?>
<table>

    <tr>
        <th>Settings</th>
    </tr>

    <tr>

        <td>

        <div>
            <?= $this->Html->image($logoSrc, ['alt' => 'Logo', 'width' => '214', 'height' => '36']) ?>
        </div>

        <div id="edit_logo_button" class="button_small"><span class="icon-pencil"></span>Edit Logo</div>

        <div id="dropbox" class="hidden">
            <?= $this->Form->create(null, ['type' => 'file', 'id' => 'SettingUploadLogoForm', 'url' => ['controller' => 'Settings', 'action' => 'uploadLogo']]) ?>
            <?= $this->Form->control('logo', ['type' => 'file', 'accept' => 'image/png', 'required' => true, 'label' => 'Choose your new .png logo']) ?>
            <?= $this->Form->end() ?>
            <div id="upload_logo_button" class="button_small"><span class="icon-download"></span>Upload Logo</div>
        </div>

        </td>

    </tr>

</table>

<?= $this->Form->create($setting, ['id' => 'SettingEditConfigForm', 'url' => ['controller' => 'Settings', 'action' => 'editConfig', $setting->id]]) ?>

<table>

    <tr><td><?= $this->Form->control('company_name') ?></td></tr>

    <tr><td><?= $this->Form->control('company_address_line_1') ?></td></tr>

    <tr><td><?= $this->Form->control('company_address_line_2') ?></td></tr>

    <tr><td><?= $this->Form->control('company_town') ?></td></tr>

    <tr><td><?= $this->Form->control('company_city') ?></td></tr>

    <tr><td><?= $this->Form->control('company_state') ?></td></tr>

    <tr><td><?= $this->Form->control('company_zip') ?></td></tr>

    <tr><td><?= $this->Form->control('company_phone') ?></td></tr>

    <tr><td><?= $this->Form->control('company_email') ?></td></tr>

    <tr><td><?= $this->Form->control('custom_1', ['label' => 'Custom Field (e.g. TAX CODE: 1234 567) (This field will be displayed on PDFs)']) ?></td></tr>

    <tr><td><?= $this->Form->control('currency', ['label' => 'Currency Symbol (e.g. $, £, €, ¥)']) ?></td></tr>

    <tr><td><?= $this->Form->control('payment_info') ?></td></tr>

</table>

<?= $this->Form->end() ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?= $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Config', ['id' => 'save_config_button']) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        // Reveal The Logo Upload Form

        $('#edit_logo_button').click(function() {
            $(this).hide();
            $('#dropbox').removeClass("hidden");
        });


        // Upload Logo Button

        $('#upload_logo_button').click(function() {
            $('#SettingUploadLogoForm').submit();
        });


        // Save Config Button

        $('#save_config_button').click(function() {
            $('#SettingEditConfigForm').submit();
        });

    });

</script>

<!-- // END SCRIPTS -->

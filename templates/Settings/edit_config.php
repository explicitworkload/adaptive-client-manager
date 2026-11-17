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
            <?= $this->Html->image($logoSrc, ['alt' => 'Logo', 'width' => '214', 'height' => '36', 'id' => 'logo_preview']) ?>
        </div>

        <div id="edit_logo_button" class="button_small"><span class="icon-pencil"></span>Edit Logo</div>

        <div id="dropbox" class="hidden">
            <?= $this->Form->create(null, ['type' => 'file', 'id' => 'SettingUploadLogoForm', 'url' => ['controller' => 'Settings', 'action' => 'uploadLogo']]) ?>
            <?= $this->Form->control('logo', ['type' => 'file', 'accept' => 'image/png', 'required' => true, 'label' => 'Choose your new .png logo']) ?>
            <?= $this->Form->end() ?>
            <div class="progressHolder" id="logo_progress" hidden><div class="progress" style="width: 0"></div></div>
            <p id="logo_status" class="logo_status" role="status" aria-live="polite"></p>
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


        // Upload Logo: sent in the background with live progress, then the
        // public logo URL is re-downloaded to confirm the new file is live.

        var logoUrl = <?= json_encode($this->Url->image('logo.png'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var logoForm = document.getElementById('SettingUploadLogoForm');
        var logoInput = logoForm.querySelector('input[type=file]');
        var uploading = false;

        function logoStatus(text, kind) {
            $('#logo_status').text(text).attr('class', 'logo_status' + (kind ? ' ' + kind : ''));
        }

        function logoProgress(percent) {
            $('#logo_progress').prop('hidden', percent === null).find('.progress').css('width', (percent || 0) + '%');
        }

        function finishUpload(text, kind) {
            uploading = false;
            $('#upload_logo_button').removeClass('disabled');
            logoStatus(text, kind);
        }

        function verifyLiveLogo(file) {
            logoStatus('Uploaded. Checking the live logo…');
            // cache: 'reload' bypasses the browser cache (no ?query needed, which
            // some hosting firewalls block on images).
            fetch(logoUrl, { cache: 'reload', credentials: 'same-origin' })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('the server answers HTTP ' + response.status + ' for the logo (its file permissions should be 644)');
                    }
                    return response.blob();
                })
                .then(function(blob) {
                    if (blob.size !== file.size) {
                        throw new Error('the site is still serving the old logo (if Cloudflare caches it, purge its cache)');
                    }
                    var url = URL.createObjectURL(blob);
                    $('#logo_preview, .header .logo img').attr('src', url);
                    logoProgress(null);
                    finishUpload('Logo updated. The new logo is live.', 'is-success');
                })
                .catch(function(error) {
                    logoProgress(null);
                    finishUpload('Uploaded, but ' + error.message + '.', 'is-error');
                });
        }

        function uploadLogo() {
            if (uploading) {
                return;
            }
            var file = logoInput.files[0];
            if (!file) {
                logoStatus('Choose a .png file first.', 'is-error');
                return;
            }
            if (file.type !== 'image/png' && !/\.png$/i.test(file.name)) {
                logoStatus('Only PNG files are allowed.', 'is-error');
                return;
            }

            uploading = true;
            $('#upload_logo_button').addClass('disabled');
            logoProgress(0);
            logoStatus('Uploading… 0%');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', logoForm.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    var percent = Math.round(e.loaded / e.total * 100);
                    logoProgress(percent);
                    logoStatus('Uploading… ' + percent + '%');
                }
            };
            xhr.onload = function() {
                var data = null;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (ignore) {}

                if (data && data.ok) {
                    logoProgress(100);
                    verifyLiveLogo(file);
                    return;
                }
                logoProgress(null);
                if (data) {
                    finishUpload(data.message, 'is-error');
                } else if (xhr.status === 403) {
                    finishUpload('The upload was refused because your session expired or your browser blocked cookies. Reload this page and try again.', 'is-error');
                } else {
                    finishUpload('The server returned an unexpected response (HTTP ' + xhr.status + '). Details are in logs/error.log.', 'is-error');
                }
            };
            xhr.onerror = function() {
                logoProgress(null);
                finishUpload('Network error: the upload did not reach the server.', 'is-error');
            };
            xhr.send(new FormData(logoForm));
        }

        $('#upload_logo_button').on('click', uploadLogo);
        $(logoForm).on('submit', function(e) {
            e.preventDefault();
            uploadLogo();
        });


        // Save Config Button

        $('#save_config_button').click(function() {
            $('#SettingEditConfigForm').submit();
        });

    });

</script>

<!-- // END SCRIPTS -->

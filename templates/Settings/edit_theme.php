<?php
/**
 * Theme colour (regenerates dead_pixel.css server-side on save).
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Setting $setting
 */
?>
<h3>Edit Theme Settings</h3>

<?= $this->Form->create($setting, ['id' => 'SettingEditThemeForm', 'url' => ['controller' => 'Settings', 'action' => 'editTheme', $setting->id]]) ?>

<table>

    <tr>
        <th>Theme</th>
    </tr>

    <tr>
        <td>
            <?= $this->Form->control('primary_color', ['class' => 'color', 'maxlength' => 7]) ?>

            <div class="theme_color_selection emerald_bg"></div>

            <div class="theme_color_selection green_bg"></div>

            <div class="theme_color_selection aqua_bg"></div>

            <div class="theme_color_selection blue_bg"></div>

            <div class="theme_color_selection purple_bg"></div>

            <div class="theme_color_selection orchid_bg"></div>

            <div class="theme_color_selection hot_pink_bg"></div>

            <div class="theme_color_selection pink_bg"></div>

            <div class="theme_color_selection red_bg"></div>

            <div class="theme_color_selection orange_bg"></div>

            <div class="theme_color_selection yellow_bg"></div>

            <div class="theme_color_selection monochrome_bg"></div>


            <div class="theme_preview_container"><div class="color"><h1>Preview</h1></div></div>

            <div id="create_color_button" class="button_small"><span class="icon-droplet"></span>Create Custom Color</div>

            <?php /* The browser's own colour picker (replaces the Farbtastic plugin). */ ?>
            <input type="color" id="custom_color" value="<?= h($setting->primary_color ?: '#339966') ?>" aria-label="Custom colour" style="width:44px;height:32px;padding:0;border:0;background:none;cursor:pointer;vertical-align:middle">

        </td>
    </tr>

</table>

<div id="result"></div>

<?= $this->Form->end() ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?= $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Theme', ['id' => 'save_theme_button']) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<script>

    // Browsers report background-color as "rgb(r, g, b)"; the server wants #RRGGBB.
    function colorToHex(color) {
        if (color.charAt(0) === '#') {
            return color;
        }
        var digits = /rgb\((\d+), (\d+), (\d+)\)/.exec(color);
        var rgb = parseInt(digits[3], 10) | (parseInt(digits[2], 10) << 8) | (parseInt(digits[1], 10) << 16);
        return '#' + ('000000' + rgb.toString(16)).slice(-6);
    }

    // Show a colour in the hex field, the preview and the custom picker.
    function setColor(color) {
        $('#primary-color').val(color);
        $('.color').css('background-color', color);
        $('#custom_color').val(color);
    }

    $(document).ready(function() {

        var initial = $('#primary-color').val();
        if (/^#[0-9a-fA-F]{6}$/.test(initial)) {
            setColor(initial);
        }

        $('.theme_color_selection').on('click', function() {
            setColor(colorToHex($(this).css('background-color')));
        });

        // "Create Custom Color" opens the browser's colour picker.
        $('#create_color_button').on('click', function() {
            var picker = document.getElementById('custom_color');
            if (picker.showPicker) {
                picker.showPicker();
            } else {
                picker.click();
            }
        });
        $('#custom_color').on('input change', function() {
            setColor(this.value);
        });

        // Typing a hex value updates the preview.
        $('#primary-color').on('input', function() {
            if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                $('.color').css('background-color', this.value);
                $('#custom_color').val(this.value);
            }
        });

        $('#save_theme_button').on('click', function() {
            $('#SettingEditThemeForm').submit();
        });

    });

</script>

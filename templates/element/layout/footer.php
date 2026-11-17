<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="footer">
    <?php /* Credit to Dead Pixel Studio, the original author. Not linked: their site is no longer active. */ ?>
    <?= $this->Html->image('logo_powered.png', ['alt' => 'Adaptive Client Manager - Powered By Dead Pixel Studio', 'width' => '200', 'height' => '26']) ?>
</div>
<script>
    $(document).ready(function() {
        if ($('.menu_button').is(':visible')) {
            $('.nav_tile_container').hide();
            $('.menu_button').click(function() {
                $('.nav_tile_container').slideToggle(500);
            });
        }
        $('.div_link').click(function() {
            var href = $(this).find('a').attr('href');
            if (href) {
                window.location = href;
            }
            return false;
        });
    });
</script>

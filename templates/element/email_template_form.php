<?php
use App\Model\Table\EmailTemplatesTable;

/**
 * Shared rich-text editor (Jodit) for the email templates.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $template
 * @var string|null $kind 'invoice', 'quote', or null (unknown: list both sets of document shortcodes)
 * @var string $formId legacy form id (the save button submits it)
 * @var array $url form action
 * @var string $heading e.g. "Client Invoice Email Template"
 */
$kind = $kind ?? null;
$prefix = $kind ?? 'template';
$editorId = $prefix . 'Email';

$this->Html->css('/vendor/jodit-4.20.3/jodit.min.css', ['block' => true]);
$this->Html->script(['/vendor/jodit-4.20.3/jodit.min.js', 'editor'], ['block' => true]);

$shortcodes = [
    'Your Company:' => ['COMPANY_NAME', 'COMPANY_EMAIL', 'COMPANY_ADDRESS_1', 'COMPANY_ADDRESS_2', 'COMPANY_TOWN', 'COMPANY_CITY', 'COMPANY_STATE', 'COMPANY_ZIP'],
    'The Client:' => ['CLIENT_NAME', 'CLIENT_CONTACT', 'CLIENT_ADDRESS_1', 'CLIENT_ADDRESS_2', 'CLIENT_TOWN', 'CLIENT_CITY', 'CLIENT_STATE', 'CLIENT_ZIP'],
];
if ($kind !== 'quote') {
    $shortcodes['The Invoice:'] = ['INVOICE_NUMBER', 'INVOICE_DATE', 'INVOICE_TOTAL'];
}
if ($kind !== 'invoice') {
    $shortcodes['The Quote:'] = ['QUOTE_NUMBER', 'QUOTE_DATE', 'QUOTE_TOTAL'];
}
$shortcodes['General:'] = ['USER', 'PAYMENT_INFO'];

$defaults = EmailTemplatesTable::DEFAULTS;
?>
<div class="email_form" id="<?= h($prefix) ?>_email_form">

    <?= $this->Form->create($template, ['id' => $formId, 'url' => $url]) ?>
    <?= $this->Form->hidden('content', ['id' => 'EmailTemplateContent']) ?>
    <?= $this->Form->end() ?>

    <h4><?= h($heading) ?></h4>

    <?php /* The editor sits outside the form; Save copies its HTML into the hidden content field. Textarea content is escaped (the editor reads it as HTML text). */ ?>
    <div class="input textarea">
        <textarea id="<?= h($editorId) ?>" rows="6" cols="30"><?= h((string)$template->content) ?></textarea>
    </div>

    <div class="shortcodes">
        <h5>Shortcodes</h5>
        <?php foreach ($shortcodes as $group => $codes) : ?>
            <p><?= h($group) ?></p>
            <?php foreach ($codes as $code) : ?>
                <a href="javascript:;" class="shortcode" data-code="[<?= h($code) ?>]">[<?= h($code) ?>]</a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <?= $this->Html->div('button_small', '<span class="icon-floppy"></span>Save Template', ['id' => 'save_' . $prefix . '_button']) ?>

    <?php if (isset($defaults[$kind])) : ?>
        <?= $this->Html->div('button_small negative', '<span class="icon-refresh"></span>Load Default', ['id' => 'default_' . $prefix . '_button']) ?>
    <?php endif; ?>

</div>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        var editor = acmEditor('#<?= $editorId ?>');

        // Insert Shortcode At The Cursor

        // mousedown + preventDefault keeps the cursor position in the editor.
        $('.shortcodes a.shortcode').on('mousedown', function(e) {
            e.preventDefault();
            editor.s.insertHTML($(this).data('code'));
        });


        // Save Template Button

        $('#save_<?= $prefix ?>_button').click(function() {
            $('#EmailTemplateContent').val(editor.value);
            $('#<?= $formId ?>').submit();
        });


        // Reset To Default

        $('#default_<?= $prefix ?>_button').click(function() {
            var defaultContent = <?= json_encode($defaults[$kind] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            editor.value = defaultContent;
        });

    });

</script>

<?php
/**
 * Preview / edit the quote email before sending.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Quote $thisQuote
 * @var \App\Model\Entity\Setting $settings
 * @var string $message email body (admin-authored HTML template, shortcodes already filled in)
 * @var string|null $to client email
 * @var string|null $from company email
 */
$this->Html->css('/vendor/jodit-4.20.3/jodit.min.css', ['block' => true]);
$this->Html->script(['/vendor/jodit-4.20.3/jodit.min.js', 'editor'], ['block' => true]);

$pdfName = 'Quote_' . $thisQuote->quote_number . '.pdf';
?>
<h3>Email Quote: <?= h($thisQuote->quote_number) ?></h3>

<!-- EMAIL FORM -->

<?php
// Only the message is posted; recipient and sender are taken from the client
// and company records by QuotesController::send().
echo $this->Form->create(null, ['id' => 'QuoteEmailForm', 'url' => ['action' => 'send', $thisQuote->id]]);
echo $this->Form->hidden('message', ['value' => '']);
echo $this->Form->end();
?>

<!-- // END EMAIL FORM -->


<!-- EMAIL VIEW -->

<div id="emailView">

    <p><span class="bold">To: </span><?= h($to) ?></p>
    <p><span class="bold">From: </span><?= h($from) ?></p>
    <p><span class="bold">Message: </span></p>

    <?php
    // $message is admin-authored HTML that is intentionally rendered as HTML by
    // TinyMCE. It is h()-encoded here only because it sits inside a <textarea>;
    // the browser decodes it back to the original HTML for the editor.
    // No name attribute: the edited content is copied into the hidden "message" field on send.
    ?>
    <textarea id="email" rows="15" style="width:100%"><?= h($message) ?></textarea>

</div>

<!-- END EMAIL VIEW -->


<!-- PDF VIEW -->

<p><span class="bold">Attachments: </span><?= $this->Html->link($pdfName, ['action' => 'pdf', $thisQuote->id]) ?></p>

<!-- // END PDF VIEW -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?= $this->Html->div('info_button_primary', '<span class="icon-mail"></span>Send Email', ['id' => 'send_email_button']) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

<script type="text/javascript">

    var editor = acmEditor('#email');

    $(document).ready(function() {

        // Send Email
        $('#send_email_button').click(function() {

            $('#QuoteEmailForm input[name="message"]').val(editor.value);

            $('#QuoteEmailForm').submit();
        });

    });

</script>

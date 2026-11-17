<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice $thisInvoice with client, items, payments
 * @var \App\Model\Entity\Setting $settings
 * @var string $message email template HTML with shortcodes already filled in (admin-authored)
 * @var string|null $to client email
 * @var string|null $from company email
 */
$this->Html->css('/vendor/jodit-4.20.3/jodit.min.css', ['block' => true]);
$this->Html->script(['/vendor/jodit-4.20.3/jodit.min.js', 'editor'], ['block' => true]);
$attachment = 'Invoice_' . $thisInvoice->invoice_number . '.pdf';
?>
<h3>Email Invoice: <?= h($thisInvoice->invoice_number) ?></h3>

<!-- EMAIL VIEW -->
<?= $this->Form->create(null, ['id' => 'InvoiceEmailForm', 'url' => ['action' => 'send', $thisInvoice->id]]) ?>

<div id="emailView">

    <?php /* Recipient and sender are fixed server-side (client and company records). */ ?>
    <p><span class="bold">To: </span><?= h($to) ?></p>
    <p><span class="bold">From: </span><?= h($from) ?></p>
    <p><span class="bold">Message: </span></p>

    <?php
    // $message is admin-authored HTML; the textarea value is escaped by the helper and
    // TinyMCE then renders it as HTML for editing.
    echo $this->Form->control('message', ['type' => 'textarea', 'label' => false, 'id' => 'email', 'value' => $message]);
    ?>

</div>

<?= $this->Form->end() ?>
<!-- END EMAIL VIEW -->

<!-- PDF VIEW -->
<p><span class="bold">Attachments: </span><?= $this->Html->link($attachment, ['action' => 'pdf', $thisInvoice->id]) ?></p>

<div id="pdfView"></div>
<!-- // END PDF VIEW -->

<!-- INFO TILE -->
<?php
$this->start('info_tile');
echo $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>');
echo $this->Html->div('info_button_primary', '<span class="icon-mail"></span>Send Email', ['id' => 'send_email_button']);
$this->end();
?>
<!-- // END INFO TILE -->

<script type="text/javascript">

    var editor = acmEditor('#email');

    $(document).ready(function() {

        // Send Email
        $('#send_email_button').click(function() {
            $('#email').val(editor.value);
            $('#InvoiceEmailForm').submit();
        });

    });

</script>

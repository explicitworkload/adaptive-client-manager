<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice $invoice
 * @var array<int, string> $clients id => client name
 * @var string $nextSequence 4-digit sequence appended to YYMMDD
 */
?>
<h3>Create A New Invoice: Step 1</h3>

<p>Please complete the basic setup for the new invoice and save using the 'Save Invoice' button. All items, prices and quantities can be added on the next screen.</p>

<?php
echo $this->Form->create($invoice, ['id' => 'InvoiceAddForm', 'url' => ['action' => 'add']]);
echo $this->Form->control('client_id', [
    'options' => $clients,
    'empty' => 'Please Select A Client',
    'label' => false,
]);
echo $this->Form->control('invoice_number');
echo $this->Form->control('invoice_date', ['type' => 'date']);
echo $this->Form->end();
?>

<!-- INFO TILE -->
<?php
$this->start('info_tile');
echo $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Invoice', ['id' => 'save_invoice_button']);
$cancelLink = $this->Html->link('Cancel', ['controller' => 'Invoices', 'action' => 'index']);
echo $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $cancelLink);
$this->end();
?>
<!-- // END INFO TILE -->

<script type="text/javascript">

    var nextSequence = <?= json_encode((string)$nextSequence, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Invoice number = YYMMDD (from the Y-m-d date input) + sequence.
    function setInvoiceNumber() {
        var parts = ($('#invoice-date').val() || '').split('-');
        if (parts.length !== 3) {
            return;
        }
        $('#invoice-number').val(parts[0].substring(2, 4) + parts[1] + parts[2] + nextSequence);
    }

    $(document).ready(function() {

        $('select').wrap('<div class="styled_select" />');

        // SET INVOICE NUMBER
        $('#invoice-date').change(function() {
            setInvoiceNumber();
        });

        // SAVE INVOICE BUTTON
        $('#save_invoice_button').click(function() {
            $('#InvoiceAddForm').submit();
        });

    });

</script>

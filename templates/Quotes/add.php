<?php
/**
 * Create a new quote (step 1: client, number, date, days to expire).
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Quote $quote
 * @var array<int, string> $clients
 * @var string $nextSequence 4-digit sequence; the JS prefixes YYMMDD from the quote date
 */
?>
<h3>Create A New Quote: Step 1</h3>

<p>Please complete the basic setup for the new quote and save using the 'Save Quote' button. All items, prices and quantities can be added on the next screen.</p>

<?php
// quote_total, status, created/modified and expires are set by the server.
echo $this->Form->create($quote, ['id' => 'QuoteAddForm', 'url' => ['action' => 'add']]);

echo $this->Form->control('client_id', [
    'options' => $clients,
    'empty' => 'Please Select A Client',
    'label' => false,
]);

echo $this->Form->control('quote_number');

echo $this->Form->control('expire_days', [
    'type' => 'number', 'label' => 'Days To Expire', 'value' => $this->request->getData('expire_days', 30), 'min' => 0,
]);

echo $this->Form->control('quote_date', ['type' => 'date']);

echo $this->Form->end();
?>

<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Quote', ['id' => 'save_quote_button']) ?>
<?= $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $this->Html->link('Cancel', ['controller' => 'Quotes', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

<script type="text/javascript">

    var nextSequence = <?= json_encode((string)$nextSequence) ?>;

    // Quote number = YYMMDD (from the Y-m-d date input) + next sequence.
    function setQuoteNumber() {

        var parts = ($('#quote-date').val() || '').split('-');

        if (parts.length !== 3) {
            return;
        }

        $('#quote-number').val(parts[0].substring(2, 4) + parts[1] + parts[2] + nextSequence);
    }

    $(document).ready(function() {

        $('select').wrap('<div class="styled_select" />');

        // SET QUOTE NUMBER

        $('#expire-days').change(function() {

            var days = $('#expire-days').val();

            if (!(Number.isFinite(Number(days)) && Number(days) > 0)) {
                $('#expire-days').val(30);
            }
        });

        $('#quote-date').change(function() {
            setQuoteNumber();
        });

        // SAVE QUOTE BUTTON

        $('#save_quote_button').click(function() {
            $('#QuoteAddForm').submit();
        });

    });

</script>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice $invoice with client, items, payments
 * @var array<string, string> $taxRates rate => rate
 * @var \App\Model\Entity\Setting $settings
 * @var \App\Model\Entity\Item $newItem
 * @var \App\Model\Entity\Payment $newPayment
 */
$this->Html->css('invoicesEdit', ['block' => true]);

$currency = (string)$settings->currency;
$client = $invoice->client;

$subTotal = 0;
$tax = 0;
$total = 0;
$paymentsTotal = 0;
?>
<h3>Edit Invoice: <?= h($invoice->getOriginal('invoice_number')) ?></h3>

<?= $this->Form->create($invoice, ['id' => 'InvoiceEditForm', 'url' => ['action' => 'edit', $invoice->id]]) ?>

<div class="container">

    <div class="two columns">

        <!-- INVOICE DETAILS -->
        <table>

            <tr><th>Invoice Details</th></tr>

            <tr><td><?= $this->Form->control('invoice_number') ?></td></tr>

            <tr><td><?= $this->Form->control('invoice_date', ['type' => 'date']) ?></td></tr>

            <?php /* Total and status are recalculated server-side when items/payments change. */ ?>
            <tr><td><span class="bold">Total: </span><?= h($currency . number_format((float)$invoice->invoice_total, 2, '.', ',')) ?></td></tr>

            <tr><td><span class="bold">Status: </span><span class="status"><?= h($invoice->status) ?></span></td></tr>

            <tr><td><?= $this->Form->control('public_notes') ?></td></tr>
            <tr><td><?= $this->Form->control('private_notes') ?></td></tr>

        </table>
        <!-- // END INVOICE DETAILS -->

    </div>

    <div class="two columns">

        <!-- INVOICE TO -->
        <table>

            <tr><th>Invoice To</th></tr>

            <tr><td><span class="bold"><?= h($invoice->client_name ?: $client?->client_name) ?></span><br />
                <?php if ($client) : ?>
                    <?php foreach (['client_address_line_1', 'client_address_line_2', 'client_town', 'client_city', 'client_state'] as $field) : ?>
                        <?php if ($client->get($field)) : ?><?= h($client->get($field)) ?><br /><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($client->client_zip) : ?><?= h($client->client_zip) ?><br /><br /><?php endif; ?>
                    <?php if ($client->client_phone) : ?><span class="bold">Phone: </span><?= h($client->client_phone) ?><br /><?php endif; ?>
                    <?php if ($client->client_email) : ?><span class="bold">Email: </span><?= h($client->client_email) ?><br /><?php endif; ?>
                <?php endif; ?>
            </td></tr>

        </table>
        <!-- // END INVOICE TO -->

    </div>

</div>

<?= $this->Form->end() ?>

<!-- ITEMS -->
<table class="responsive_table" id="items_table">

    <thead>
        <tr>
            <th>Items</th>
            <th class="right tiny_cell">QTY</th>
            <th class="right small_cell">Price</th>
            <th class="right small_cell">Sub Tot</th>
            <th class="right small_cell">Tax</th>
            <th class="right small_cell">Total</th>
            <th class="center small_cell">Actions</th>
        </tr>
    </thead>

    <?php if (empty($invoice->items)) : ?>
        <tr><td> -- No Items Are Associated With This Invoice -- </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td></tr>
    <?php endif; ?>

    <?php foreach ($invoice->items as $itemCount => $item) : ?>
        <?php
        $itemCount++;
        $itemSubTotal = (float)$item->quantity * (float)$item->unit_price;
        $itemTax = $itemSubTotal * ((float)$item->tax_rate / 100);
        $itemTotal = $itemSubTotal + $itemTax;
        $subTotal += $itemSubTotal;
        $tax += $itemTax;
        $total += $itemTotal;
        ?>
        <tr id="item_row_<?= (int)$item->id ?>">
            <td><span class="bold">Item <?= $itemCount ?>: </span><?= h($item->title) ?></td>
            <td class="right responsive_cell"><?= h($item->quantity) ?></td>
            <td class="right responsive_cell"><?= number_format((float)$item->unit_price, 2, '.', ',') ?></td>
            <td class="right responsive_cell"><?= number_format($itemSubTotal, 2, '.', ',') ?></td>
            <td class="right responsive_cell"><?= number_format($itemTax, 2, '.', ',') ?></td>
            <td class="right responsive_cell"><?= number_format($itemTotal, 2, '.', ',') ?></td>
            <td class="center responsive_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'Items', 'action' => 'edit', $item->id], ['class' => 'view', 'id' => 'item_' . $item->id]) ?>
                <?= $this->Form->postLink('Delete', ['controller' => 'Items', 'action' => 'delete', $item->id], [
                    'confirm' => 'Are you sure you want to permanently delete this item?',
                    'class' => 'item_delete',
                ]) ?>
            </td>
        </tr>

        <tr><td colspan="7"><span class="bold">Item <?= $itemCount ?> Details:<br></span><?= nl2br(h($item->details)) ?></td></tr>
    <?php endforeach; ?>

</table>

<table class="responsive_table" id="item_total_table">

    <thead>
        <tr><td colspan="7"></td></tr>
    </thead>

    <tr class="bold total_row">
        <td colspan="3">Item Totals (<?= h($currency) ?>)</td>
        <td class="right small_cell"><?= number_format($subTotal, 2, '.', ',') ?></td>
        <td class="right small_cell"><?= number_format($tax, 2, '.', ',') ?></td>
        <td id="invoiceTotal" class="right small_cell"><?= number_format($total, 2, '.', ',') ?></td>
        <td class="small_cell"></td>
    </tr>

</table>

<div id="add_item_form">

    <h4>Add Item</h4>

    <?php
    echo $this->Form->create($newItem, ['id' => 'ItemAddForm', 'url' => ['controller' => 'Items', 'action' => 'add']]);
    echo $this->Form->hidden('invoice_id', ['value' => $invoice->id]);
    echo $this->Form->control('title');
    echo $this->Form->control('details');
    echo $this->Form->control('quantity', ['label' => 'Quantity (e.g. 5)']);
    echo $this->Form->control('unit_price', ['type' => 'number', 'step' => '0.01', 'label' => 'Unit Price (e.g. 49.99)']);
    echo $this->Form->control('tax_rate', ['options' => $taxRates, 'empty' => false, 'label' => 'Tax Rate (%)']);
    echo $this->Form->end();

    echo $this->Html->div('button_small', '<span class="icon-plus"></span>Save Item', ['id' => 'save_item_button']);
    echo $this->Html->div('button_small negative', '<span class="icon-x"></span>Cancel', ['id' => 'cancel_item_button']);
    ?>

</div>

<?= $this->Html->div('button_small', '<span class="icon-plus"></span>Add Item', ['id' => 'add_item_button']) ?>
<!-- // END ITEMS -->

<!-- PAYMENTS -->
<table class="responsive_table" id="payments_table">

    <thead>
        <tr>
            <th>Payments Received</th>
            <th class="center small_cell">Date</th>
            <th class="right small_cell">Amount</th>
            <th class="center small_cell">Actions</th>
        </tr>
    </thead>

    <?php if (empty($invoice->payments)) : ?>
        <tr><td> -- No Payments Are Associated With This Invoice -- </td>
        <td></td>
        <td></td>
        <td></td></tr>
    <?php endif; ?>

    <?php foreach ($invoice->payments as $payment) : ?>
        <?php $paymentsTotal += (float)$payment->amount; ?>
        <tr id="payment_row_<?= (int)$payment->id ?>">
            <td class="responsive_cell"><?= h($payment->method) ?></td>
            <td class="date_cell responsive_cell"><?= h($payment->date?->format('Y-m-d')) ?></td>
            <td class="right responsive_cell"><?= number_format((float)$payment->amount, 2, '.', ',') ?></td>
            <td class="center responsive_cell">
                <?= $this->Form->postLink('Delete', ['controller' => 'Payments', 'action' => 'delete', $payment->id], [
                    'confirm' => 'Are you sure you want to permanently delete this payment?',
                    'class' => 'payment_delete',
                ]) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<table class="responsive_table" id="payments_total_table">

    <thead>
        <tr><td colspan="4"></td></tr>
    </thead>

    <tr class="bold total_row">
        <td colspan="2">Payments Totals (<?= h($currency) ?>)</td>
        <td id="paymentsTotal" class="right"><?= number_format($paymentsTotal, 2, '.', ',') ?></td>
        <td class="small_cell"></td>
    </tr>

</table>

<div id="add_payment_form">

    <h4>Add Payment</h4>

    <?php
    echo $this->Form->create($newPayment, ['id' => 'PaymentAddForm', 'url' => ['controller' => 'Payments', 'action' => 'add']]);
    echo $this->Form->hidden('invoice_id', ['value' => $invoice->id]);
    echo $this->Form->control('date', ['type' => 'date']);
    echo $this->Form->control('method', ['label' => 'Method (e.g. PayPal, Cheque)']);
    echo $this->Form->control('amount', ['type' => 'number', 'step' => '0.01', 'label' => 'Payment Amount (e.g. 49.99)']);
    echo $this->Form->end();

    echo $this->Html->div('button_small', '<span class="icon-plus"></span>Save Payment', ['id' => 'save_payment_button']);
    echo $this->Html->div('button_small negative', '<span class="icon-x"></span>Cancel', ['id' => 'cancel_payment_button']);
    ?>

</div>

<?= $this->Html->div('button_small', '<span class="icon-plus"></span>Add Payment', ['id' => 'add_payment_button']) ?>
<!-- // END PAYMENTS -->

<!-- INFO TILE -->
<?php
$this->start('info_tile');
echo $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>');
echo $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Invoice', ['id' => 'save_invoice_button']);
$cancelLink = $this->Html->link('Cancel', ['controller' => 'Invoices', 'action' => 'view', $invoice->id]);
echo $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $cancelLink);
$this->end();
?>
<!-- // END INFO TILE -->

<script type="text/javascript">

    $(document).ready(function() {

        // Style Dropdowns
        $('select').wrap('<div class="styled_select" />');

        // Hide Additional Forms By Default
        $('#add_item_form, #add_payment_form').hide();

        // ADD ITEM FORM

        // Show/Hide Add Item Form
        $('#add_item_button').click(function(e) {
            $('#add_item_form').slideDown();
            $('#add_item_button').hide();
        });

        // Cancel Add Item
        $('#cancel_item_button').click(function(e) {
            $('#add_item_form').slideUp();
            $('#add_item_button').show();
        });

        // Save Item
        $('#save_item_button').click(function() {
            $('#ItemAddForm').submit();
        });

        // ADD PAYMENT FORM

        // Show/Hide Add Payment Form
        $('#add_payment_button').click(function(e) {
            $('#add_payment_form').slideDown();
            $('#add_payment_button').hide();
        });

        // Cancel Add Payment
        $('#cancel_payment_button').click(function(e) {
            $('#add_payment_form').slideUp();
            $('#add_payment_button').show();
        });

        // Save Payment
        $('#save_payment_button').click(function() {
            $('#PaymentAddForm').submit();
        });

        // SAVE INVOICE BUTTON
        $('#save_invoice_button').click(function() {
            $('#InvoiceEditForm').submit();
        });

    });

</script>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice $thisInvoice with client, items, payments
 * @var \App\Model\Entity\Setting $settings
 * @var array|null $currentUser
 */
$this->Html->css('invoicesView', ['block' => true]);

$isClient = ($currentUser['role'] ?? null) === 'client';
$currency = (string)$settings->currency;
$id = $thisInvoice->id;
$client = $thisInvoice->client;

$subTotal = 0;
$tax = 0;
$total = 0;
$paymentsTotal = 0;
foreach ($thisInvoice->payments as $payment) {
    $paymentsTotal += (float)$payment->amount;
}
?>
<h3>View Invoice: <?= h($thisInvoice->invoice_number) ?></h3>

<div class="container">

    <div class="two columns">

        <!-- INVOICE DETAILS -->
        <table>

            <tr><th>Invoice Details</th></tr>

            <tr><td><span class="bold">Invoice Number: </span><?= h($thisInvoice->invoice_number) ?></td></tr>

            <tr><td><span class="bold">Date of Invoice: </span><?= h($thisInvoice->invoice_date?->format('Y-m-d')) ?></td></tr>

            <tr><td><span class="bold">Total: </span><?= h($currency . number_format((float)$thisInvoice->invoice_total, 2, '.', ',')) ?></td></tr>

            <tr><td><span class="bold">Payments Received:  </span><?= h($currency) ?><span id="payments_received"><?= number_format($paymentsTotal, 2, '.', '') ?></span></td></tr>

            <tr><td><span class="bold">Status: </span><span class="status"><?= h($thisInvoice->status) ?></span></td></tr>

            <tr><td><span class="bold">Created: </span><?= h($thisInvoice->created?->format('Y-m-d H:i:s')) ?> by <?= h($thisInvoice->created_by) ?></td></tr>

            <tr><td><span class="bold">Last Modified: </span><?= h($thisInvoice->last_modified?->format('Y-m-d H:i:s')) ?> by <?= h($thisInvoice->last_mod_by) ?></td></tr>

            <!-- Public Notes - Available To Everyone -->
            <tr><td><span class="bold">Public Notes: </span><?= nl2br(h($thisInvoice->public_notes)) ?></td></tr>

            <!-- Private Notes - Hide From Client -->
            <?php if (!$isClient) : ?>
                <tr><td><span class="bold">Private Notes: </span><?= nl2br(h($thisInvoice->private_notes)) ?></td></tr>
            <?php endif; ?>

        </table>
        <!-- // END INVOICE DETAILS -->

    </div>

    <div class="two columns">

        <!-- INVOICE TO -->
        <table>

            <tr><th>Invoice To</th></tr>

            <tr><td><span class="bold"><?= h($thisInvoice->client_name ?: $client?->client_name) ?></span><br />
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

<h5>Items</h5>

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
        </tr>
    </thead>

    <?php if (empty($thisInvoice->items)) : ?>
        <tr><td> -- No Items Are Associated With This Invoice -- </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td></tr>
    <?php endif; ?>

    <?php foreach ($thisInvoice->items as $itemCount => $item) : ?>
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
        </tr>

        <tr><td colspan="6"><span class="bold">Item <?= $itemCount ?> Details:<br></span><?= nl2br(h($item->details)) ?></td></tr>
    <?php endforeach; ?>

</table>

<table class="responsive_table" id="item_total_table">

    <thead>
        <tr><td colspan="6"></td></tr>
    </thead>

    <tr class="bold total_row">
        <td colspan="3">Item Totals (<?= h($currency) ?>)</td>
        <td class="right small_cell"><?= number_format($subTotal, 2, '.', ',') ?></td>
        <td class="right small_cell"><?= number_format($tax, 2, '.', ',') ?></td>
        <td id="invoiceTotal" class="right small_cell"><?= number_format($total, 2, '.', ',') ?></td>
    </tr>

</table>
<!-- // END ITEMS -->

<!-- PAYMENTS -->
<h5>Payments Received</h5>

<table class="responsive_table" id="payments_table">

    <thead>
        <tr>
            <th>Payments Received</th>
            <th class="center small_cell">Date</th>
            <th class="right small_cell">Amount</th>
        </tr>
    </thead>

    <?php if (empty($thisInvoice->payments)) : ?>
        <tr><td> -- No Payments Are Associated With This Invoice -- </td>
        <td></td>
        <td></td></tr>
    <?php endif; ?>

    <?php foreach ($thisInvoice->payments as $payment) : ?>
        <tr id="payment_row_<?= (int)$payment->id ?>">
            <td class="responsive_cell"><?= h($payment->method) ?></td>
            <td class="date_cell responsive_cell"><?= h($payment->date?->format('Y-m-d')) ?></td>
            <td class="right responsive_cell"><?= number_format((float)$payment->amount, 2, '.', ',') ?></td>
        </tr>
    <?php endforeach; ?>

</table>

<table class="responsive_table" id="payments_total_table">

    <thead>
        <tr><td colspan="3"></td></tr>
    </thead>

    <tr class="bold total_row">
        <td colspan="2">Payments Totals (<?= h($currency) ?>)</td>
        <td id="paymentsTotal" class="right"><?= number_format($paymentsTotal, 2, '.', ',') ?></td>
    </tr>

</table>
<!-- // END PAYMENTS -->

<!-- INFO TILE -->
<?php
$this->start('info_tile');

echo $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>');

// Hide Edit Button From Clients
if (!$isClient) {
    $editLink = $this->Html->link('Edit Invoice', ['action' => 'edit', $id]);
    echo $this->Html->div('div_link info_button_primary', '<span class="icon-pencil-3"></span>' . $editLink);
}

$pdfLink = $this->Html->link('Download PDF', ['action' => 'pdf', $id]);
echo $this->Html->div('div_link info_button_primary', '<span class="icon-search"></span>' . $pdfLink);

// Hide Email & Delete Buttons From Clients
if (!$isClient) {
    $emailLink = $this->Html->link('Email To Client', ['action' => 'email', $id]);
    echo $this->Html->div('div_link info_button_primary', '<span class="icon-attachment"></span>' . $emailLink);
    echo $this->Html->div('info_button_negative', '<span class="icon-remove"></span><a href="">Delete Invoice</a>', ['id' => 'delete_button']);
}
?>

<?php if (!$isClient) : ?>
<!-- MODALS -->
<div id="modal_inv_delete" class="modal">

    <div id="heading">Are you sure you want to do that?</div>

    <div id="content">

        <p>Are you sure you want to delete this invoice and all associated data?<br>This action cannot be reversed...</p>

        <?= $this->Form->postLink('YES, DO IT', ['action' => 'delete', $id], ['class' => 'confirm_button_confirm']) ?>

        <a href="#" class="confirm_button_cancel close">NO, CANCEL</a>

    </div>

</div>
<!-- // END MODALS -->
<?php endif; ?>

<?php $this->end(); ?>
<!-- // END INFO TILE -->

<script type="text/javascript">

    $(document).ready(function() {

        // Delete Modal
        $('#delete_button').click(function(e) {
            $('#modal_inv_delete').reveal({
                animation: 'fade',
                animationspeed: 400,
                closeonbackgroundclick: true,
                dismissmodalclass: 'close'
            });

            $("html, body").animate({ scrollTop: 0 }, 600);

            return false;
        });

    });

</script>

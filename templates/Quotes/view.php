<?php
/**
 * View a single quote (staff and the quote's own client).
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Quote $thisQuote
 * @var \App\Model\Entity\Setting $settings
 * @var array|null $currentUser
 */
$this->Html->css('quotesView', ['block' => true]);

$isClient = ($currentUser['role'] ?? null) === 'client';
$currency = (string)$settings->currency;
$id = $thisQuote->id;
$client = $thisQuote->client;
$items = $thisQuote->quote_items ?? [];

$subTotal = 0;
$tax = 0;
$total = 0;

$fmtDateTime = fn($d) => $d ? h($d->format('Y-m-d H:i:s')) : '';
?>
<h3>View Quote: <?= h($thisQuote->quote_number) ?></h3>

<div class="container">

    <div class="two columns">

        <!-- QUOTE DETAILS -->
        <table>
            <tr><th>Quote Details</th></tr>
            <tr><td><span class="bold">Quote Number: </span><?= h($thisQuote->quote_number) ?></td></tr>
            <tr><td><span class="bold">Date of Quote: </span><?= $thisQuote->quote_date ? h($thisQuote->quote_date->format('Y-m-d')) : '' ?></td></tr>
            <tr><td><span class="bold">Total: </span><?= h($currency . number_format((float)$thisQuote->quote_total, 2, '.', ',')) ?></td></tr>
            <tr><td><span class="bold">Status: </span><span class="quote_status"><?= h($thisQuote->status) ?></span></td></tr>
            <tr><td><span class="bold">Created: </span><?= $fmtDateTime($thisQuote->created) ?> by <?= h($thisQuote->created_by) ?></td></tr>
            <tr><td><span class="bold">Last Modified: </span><?= $fmtDateTime($thisQuote->modified) ?> by <?= h($thisQuote->last_mod_by) ?></td></tr>

            <!-- Public Notes - Available To Everyone -->
            <tr><td><span class="bold">Public Notes: </span><?= nl2br(h($thisQuote->public_notes)) ?></td></tr>

            <!-- Private Notes - Hide From Client -->
            <?php if (!$isClient) : ?>
                <tr><td><span class="bold">Private Notes: </span><?= nl2br(h($thisQuote->private_notes)) ?></td></tr>
            <?php endif; ?>
        </table>
        <!-- // END QUOTE DETAILS -->

    </div>

    <div class="two columns">

        <!-- QUOTE TO -->
        <table>
            <tr><th>Quote To</th></tr>
            <tr><td><span class="bold"><?= h($client?->client_name) ?></span><br />
                <?php foreach (['client_address_line_1', 'client_address_line_2', 'client_town', 'client_city', 'client_state'] as $field) : ?>
                    <?php if ($client?->get($field)) : ?><?= h($client->get($field)) ?><br /><?php endif; ?>
                <?php endforeach; ?>
                <?php if ($client?->client_zip) : ?><?= h($client->client_zip) ?><br /><br /><?php endif; ?>
                <?php if ($client?->client_phone) : ?><span class="bold">Phone: </span><?= h($client->client_phone) ?><br /><?php endif; ?>
                <?php if ($client?->client_email) : ?><span class="bold">Email: </span><?= h($client->client_email) ?><br /><?php endif; ?>
            </td></tr>
        </table>
        <!-- // END QUOTE TO -->

    </div>

</div>

<!-- ITEMS -->

<h5>Items</h5>

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

    <?php if (empty($items)) : ?>
        <tr><td> -- No Items Are Associated With This Invoice -- </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td></tr>
    <?php endif; ?>

    <?php $itemCount = 0; ?>
    <?php foreach ($items as $item) : ?>
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


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>

<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>

<?php if (!$isClient) : ?>
    <?= $this->Html->div('div_link info_button_primary', '<span class="icon-pencil-3"></span>' . $this->Html->link('Edit Quote', ['action' => 'edit', $id])) ?>
<?php endif; ?>

<?= $this->Html->div('div_link info_button_primary', '<span class="icon-search"></span>' . $this->Html->link('Download PDF', ['action' => 'pdf', $id])) ?>

<?php if (!$isClient) : ?>
    <?= $this->Html->div('div_link info_button_primary', '<span class="icon-attachment"></span>' . $this->Html->link('Email To Client', ['action' => 'email', $id])) ?>

    <?= $this->Html->div('info_button_negative', '<span class="icon-remove"></span><a href="">Delete Quote</a>', ['id' => 'delete_button']) ?>

    <!-- MODALS -->
    <div id="modal_inv_delete" class="modal">
        <div id="heading">Are you sure you want to do that?</div>
        <div id="content">
            <p>Are you sure you want to delete this quote and all associated data?<br>This action cannot be reversed...</p>
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

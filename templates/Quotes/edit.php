<?php
/**
 * Edit a quote and its line items.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Quote $quote
 * @var array<string, string> $taxRates
 * @var \App\Model\Entity\Setting $settings
 * @var \App\Model\Entity\QuoteItem $newItem
 * @var array<string, string> $statuses
 */
$this->Html->css('quotesEdit', ['block' => true]);

$currency = (string)$settings->currency;
$client = $quote->client;
$items = $quote->quote_items ?? [];

$subTotal = 0;
$tax = 0;
$total = 0;
?>
<h3>Edit Quote: <?= h($quote->getOriginal('quote_number')) ?></h3>

<?php // quote_total, modified and last_mod_by are maintained by the server. ?>
<?= $this->Form->create($quote, ['id' => 'QuoteEditForm', 'url' => ['action' => 'edit', $quote->id]]) ?>

<div class="container">

    <div class="two columns">

        <!-- QUOTE DETAILS -->
        <table>
            <tr><th>Quote Details</th></tr>
            <tr><td><?= $this->Form->control('quote_number') ?></td></tr>
            <tr><td><?= $this->Form->control('quote_date', ['type' => 'date']) ?></td></tr>
            <tr><td><?= $this->Form->control('expires', ['type' => 'date']) ?></td></tr>
            <tr><td><span class="bold">Total: </span>
                <?= h($currency . number_format((float)$quote->quote_total, 2, '.', ',')) ?>
            </td></tr>
            <tr><td><?= $this->Form->control('status', ['options' => $statuses, 'empty' => false]) ?></td></tr>
            <tr><td><?= $this->Form->control('public_notes') ?></td></tr>
            <tr><td><?= $this->Form->control('private_notes') ?></td></tr>
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

<?= $this->Form->end() ?>


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
            <th class="center small_cell">Actions</th>
        </tr>
    </thead>

    <?php if (empty($items)) : ?>
        <tr><td> -- No Items Are Associated With This Invoice -- </td>
        <td></td>
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
            <td class="center responsive_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'QuoteItems', 'action' => 'edit', $item->id], ['class' => 'view', 'id' => 'item_' . $item->id]) ?>
                <?= $this->Form->postLink('Delete', ['controller' => 'QuoteItems', 'action' => 'delete', $item->id], [
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
    echo $this->Form->create($newItem, ['id' => 'QuoteItemAddForm', 'url' => ['controller' => 'QuoteItems', 'action' => 'add']]);
    echo $this->Form->control('quote_id', ['type' => 'hidden', 'value' => $quote->id]);
    echo $this->Form->control('title');
    echo $this->Form->control('details');
    echo $this->Form->control('quantity', ['label' => 'Quantity (e.g. 5)']);
    echo $this->Form->control('unit_price', ['type' => 'number', 'step' => '0.01', 'label' => 'Unit Price (e.g. 49.99)']);
    echo $this->Form->control('tax_rate', ['options' => $taxRates, 'empty' => false, 'label' => 'Tax Rate (%)']);
    echo $this->Form->end();
    ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Save Item', ['id' => 'save_item_button']) ?>
    <?= $this->Html->div('button_small negative', '<span class="icon-x"></span>Cancel', ['id' => 'cancel_item_button']) ?>

</div>

<?= $this->Html->div('button_small', '<span class="icon-plus"></span>Add Item', ['id' => 'add_item_button']) ?>

<!-- // END ITEMS -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?= $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Quote', ['id' => 'save_quote_button']) ?>
<?= $this->Html->div('div_link info_button_negative', '<span class="icon-cancel"></span>' . $this->Html->link('Cancel', ['controller' => 'Quotes', 'action' => 'view', $quote->id])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

<script type="text/javascript">

    $(document).ready(function() {

        // Style Dropdowns
        $('select').wrap('<div class="styled_select" />');

        // Hide Additional Forms By Default
        $('#add_item_form').hide();

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
            $('#QuoteItemAddForm').submit();
        });

        // SAVE QUOTE BUTTON
        $('#save_quote_button').click(function() {
            $('#QuoteEditForm').submit();
        });

    });

</script>

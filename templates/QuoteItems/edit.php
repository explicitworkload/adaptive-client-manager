<?php
/**
 * Edit a quote line item.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QuoteItem $item
 * @var array<string, string> $taxRates
 */
?>
<h3>Edit Quote Item</h3>

<?= $this->Form->create($item, ['id' => 'QuoteItemEditForm', 'url' => ['action' => 'edit', $item->id]]) ?>

<table>

    <tr>
        <th>Quote Item Details</th>
    </tr>

    <tr><td><?= $this->Form->control('title') ?></td></tr>
    <tr><td><?= $this->Form->control('details') ?></td></tr>
    <tr><td><?= $this->Form->control('quantity') ?></td></tr>
    <tr><td><?= $this->Form->control('unit_price', ['step' => '0.01']) ?></td></tr>
    <tr><td><?= $this->Form->control('tax_rate', ['options' => $taxRates, 'empty' => false, 'label' => 'Tax Rate (%)']) ?></td></tr>

</table>

<?= $this->Form->end() ?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left"></span>' . $this->Html->link('Back', ['controller' => 'Quotes', 'action' => 'edit', $item->quote_id])) ?>
<?php // The button sits outside the <form> (the info tile is rendered elsewhere), so it targets it via the form attribute. ?>
<div class="submit"><?= $this->Form->button('Save Changes', ['type' => 'submit', 'form' => 'QuoteItemEditForm']) ?></div>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

<script type="text/javascript">

    $(document).ready(function() {

        // Style Dropdowns
        $('select').wrap('<div class="styled_select" />');

    });

</script>

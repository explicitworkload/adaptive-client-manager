<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item $item
 * @var array<string, string> $taxRates rate => rate
 */
?>
<h3>Edit Item</h3>

<?= $this->Form->create($item, ['id' => 'ItemEditForm', 'url' => ['action' => 'edit', $item->id]]) ?>

<table>

    <tr>
        <th>Item Details</th>
    </tr>

    <tr><td><?= $this->Form->control('title') ?></td></tr>

    <tr><td><?= $this->Form->control('details') ?></td></tr>

    <tr><td><?= $this->Form->control('quantity') ?></td></tr>

    <tr><td><?= $this->Form->control('unit_price') ?></td></tr>

    <tr><td><?= $this->Form->control('tax_rate', ['options' => $taxRates, 'empty' => false, 'label' => 'Tax Rate (%)']) ?></td></tr>

</table>

<?= $this->Form->end() ?>

<!-- INFO TILE -->
<?php
$this->start('info_tile');
$backLink = $this->Html->link('Back', ['controller' => 'Invoices', 'action' => 'edit', $item->invoice_id]);
echo $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left"></span>' . $backLink);
// The info tile is rendered outside the form, so this button submits it via JS.
echo $this->Html->div('info_button_positive', '<span class="icon-floppy"></span>Save Changes', ['id' => 'save_item_button']);
$this->end();
?>
<!-- // END INFO TILE -->

<script type="text/javascript">

    $(document).ready(function() {

        // Style Dropdowns
        $('select').wrap('<div class="styled_select" />');

        $('#save_item_button').click(function() {
            $('#ItemEditForm').submit();
        });

    });

</script>

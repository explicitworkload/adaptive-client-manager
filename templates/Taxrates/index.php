<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Taxrate> $taxRates
 * @var \App\Model\Entity\Taxrate $taxRate new entity for the add form
 */
?>
<h3>Add/Delete Tax Rates</h3>

<p>These tax rates form the tax drop-down list when you add items to invoices later.</p>

<p>By default, there is a tax rate of 0.00% added to the table for non-taxable items and/or services. You can feel free to delete this, or any other custom created tax rates if you need using the 'Delete' buttons next to each item in the table. You can also add new tax rates using the form below.</p>

<table id="tax_table">

    <thead>
        <tr>
            <th>Tax Name</th>
            <th>Rate (%)</th>
            <th class="table_icon">Actions</th>
        </tr>
    </thead>

    <?php foreach ($taxRates as $rate) : ?>
        <tr>
            <td><?= h($rate->name) ?></td>
            <td><?= h($rate->rate) ?> %</td>
            <td class="center">
                <?= $this->Form->postLink('Delete', ['controller' => 'Taxrates', 'action' => 'delete', $rate->id], ['confirm' => 'Are you sure you want to permanently delete this tax rate?', 'class' => 'tax_delete', 'id' => 'tax_rate_' . $rate->id]) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>


<div id="add_tax_form">

    <h4>Add New Tax Rate</h4>

    <?= $this->Form->create($taxRate, ['id' => 'TaxrateAddForm', 'url' => ['controller' => 'Taxrates', 'action' => 'add']]) ?>
    <?= $this->Form->control('name') ?>
    <?= $this->Form->control('rate') ?>
    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Save Tax Rate', ['id' => 'tax_add_button']) ?>

</div>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-cancel"></span>' . $this->Html->link('Exit Settings', ['controller' => 'Dashboard', 'action' => 'index'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        // Save New Tax Rate
        $('#tax_add_button').click(function() {
            $('#TaxrateAddForm').submit();
        });

    });

</script>

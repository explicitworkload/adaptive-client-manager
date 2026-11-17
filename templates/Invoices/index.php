<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Invoice> $invoices
 * @var \App\Model\Entity\Setting $settings
 */
$this->Html->css('invoicesIndex', ['block' => true]);
$this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup'], ['block' => true]);
$currency = (string)$settings->currency;
?>
<h3>View All Invoices</h3>

<table class="responsive_table" id="invoices_table">

    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Invoice Date</th>
            <th>Client</th>
            <th class="center">Total</th>
            <th class="center">Status</th>
            <th class="center no_sort">View/Edit</th>
        </tr>
    </thead>

    <?php foreach ($invoices as $invoice) : ?>
        <tr>
            <td><?= h($invoice->invoice_number) ?></td>
            <td><?= h($invoice->invoice_date?->format('Y-m-d')) ?></td>
            <td><?= h($invoice->client_name) ?></td>
            <td class="right"><?= h($currency . number_format((float)$invoice->invoice_total, 2, '.', ',')) ?></td>
            <td class="status"><?= h($invoice->status) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['action' => 'view', $invoice->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<!-- INFO TILE -->
<?php
$this->start('info_tile');
$addLink = $this->Html->link('Add Invoice', ['controller' => 'Invoices', 'action' => 'add']);
echo $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $addLink);
$this->end();
?>
<!-- // END INFO TILE -->

<script type="text/javascript">
    if ($('#invoices_table')[0]) {
        $('#invoices_table').DataTable({
            order: [[ 1, "desc" ]],
            drawCallback: function () { updateClasses(); }
        });
    }
</script>

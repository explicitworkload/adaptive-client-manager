<?php
/**
 * Invoices report.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Invoice> $invoices
 * @var \App\Model\Entity\Setting $settings
 * @var string $clientName
 * @var string $invoiceType
 * @var \Cake\I18n\Date $from
 * @var \Cake\I18n\Date $to
 * @var array|null $currentUser
 */
$currency = (string)$settings->currency;
?>
<h3>Invoices Report</h3>

<p><span class="bold">Report Generated On: </span><?= date('Y-m-d') ?> </p>

<p><span class="bold">By: </span><?= h(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?> </p>

<p><span class="bold">Clients: </span><?= h($clientName) ?> </p>

<p><span class="bold">Invoice Type: </span><?= h($invoiceType) ?> </p>

<p><span class="bold">From: </span><?= $from->format('Y-m-d') ?> </p>

<p><span class="bold">To: </span><?= $to->format('Y-m-d') ?> </p>

<table id="invoices_table">

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
            <td><?= $invoice->invoice_date?->format('Y-m-d') ?></td>
            <td><?= h($invoice->client_name) ?></td>
            <td class="right"><?= h($currency) . number_format((float)$invoice->invoice_total, 2, '.', ',') ?></td>
            <td class="status"><?= h($invoice->status) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'Invoices', 'action' => 'view', $invoice->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<?= $this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup']) ?>

<script type="text/javascript">

    $(document).ready(function() {


        // Set Data Table

        if ( $('#invoices_table')[0] ) {

            $('#invoices_table').DataTable( {
            order: [[ 1, "desc" ]],
            dom: '<"top">irt<"bottom"lp><"clear">',
             drawCallback: function () { updateClasses(); }
            } );

        }

    });

</script>

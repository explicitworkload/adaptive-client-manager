<?php
/**
 * Staff view of a single client: details, balance, invoice graph and invoices.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $thisClient
 * @var \App\Model\Entity\Setting $settings
 */
$this->Html->css('clientsView', ['block' => true]);

$currency = (string)$settings->currency;
$graphColor = (string)$settings->primary_color;
$currentYear = (int)date('Y');
$previousYear = $currentYear - 1;
$months = array_fill(1, 12, 0);
$yearData_Invoices = [$currentYear => $months, $previousYear => $months];
$invoicesTotal = 0.0;
$paymentsTotal = 0.0;
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<h3>View Client: <?= h($thisClient->client_name) ?></h3>

<div class="container">

    <div class="two columns">

        <!-- CLIENT DETAILS -->

        <table>

            <tr><th>Client Details</th></tr>

            <tr><td><span class="bold">Client Name: </span><?= h($thisClient->client_name) ?></td></tr>

            <tr><td><span class="bold">Contact Name: </span><?= h($thisClient->client_contact) ?></td></tr>

            <tr><td><span class="bold">Phone: </span> <?= h($thisClient->client_phone) ?></td></tr>

            <tr><td><span class="bold">Email: </span> <?= h($thisClient->client_email) ?></td></tr>

            <?php if ($thisClient->custom_1) : ?>
                <tr><td><span class="bold">Custom Field: </span> <?= h($thisClient->custom_1) ?></td></tr>
            <?php endif; ?>

        </table>

        <!-- // END CLIENT DETAILS -->

    </div>

    <div class="two columns">

        <!-- CLIENT ADDRESS -->

        <table>

            <tr><th>Client Address</th></tr>

            <tr><td><span class="bold"><?= h($thisClient->client_name) ?></span><br />
            <?php
            foreach (['client_address_line_1', 'client_address_line_2', 'client_town', 'client_city', 'client_state'] as $field) {
                if ($thisClient->get($field)) {
                    echo h($thisClient->get($field)) . '<br />';
                }
            }
            if ($thisClient->client_zip) {
                echo h($thisClient->client_zip) . '<br /><br />';
            }
            ?>
            </td></tr>

        </table>

        <!-- // END CLIENT ADDRESS -->

    </div>

</div>

<!-- // END CONTAINER -->


<div class="container">

    <div class="two columns">

        <!-- CLIENT CURRENT BALANCE -->

        <table>

            <tr><th>Client's Current Balance</th></tr>

            <tr><td id="client_balance" class="stat">Client Name:</td></tr>

        </table>

        <!-- // END CLIENT CURRENT BALANCE -->

    </div>

    <div class="two columns">

        <!-- CLIENT HISTORICAL STATS -->

        <table>

            <tr><th>Historical Stats (All Time)</th></tr>

            <tr><td><span class="bold">Total Invoiced: </span><span id="total_invoiced"></span></td></tr>

            <tr><td><span class="bold">Total Received: </span><span id="total_received"></span></td></tr>

        </table>

        <!-- // END CLIENT HISTORICAL STATS -->

    </div>

</div>

<!-- // END CONTAINER -->


<!-- INVOICES GRAPH -->

<h5>Activity This Year</h5>

<table>

    <thead>
        <tr>
            <th>New Invoices</th>
        </tr>
    </thead>

    <tr><td><span class="bold">Invoice Values Per Month In <?= $currentYear ?></span></td></tr>

    <tr><td><div class="widget_graph" id="placeholder"></div></td></tr>

</table>

<!-- // END INVOICE GRAPH -->


<!-- INVOICES -->

<h5>Invoices To <?= h($thisClient->client_name) ?></h5>

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

    <!-- INVOICES LOOP -->

    <?php foreach ($thisClient->invoices as $invoice) : ?>
        <?php
        $total = (float)$invoice->invoice_total;
        $invoicesTotal += $total;
        $year = $invoice->invoice_date?->year;
        if (isset($yearData_Invoices[$year])) {
            $yearData_Invoices[$year][$invoice->invoice_date->month] += $total;
        }
        ?>
        <tr>
            <td><?= h($invoice->invoice_number) ?></td>
            <td><?= $invoice->invoice_date?->format('Y-m-d') ?></td>
            <td><?= h($invoice->client_name) ?></td>
            <td class="right"><?= h($currency) . number_format($total, 2, '.', ',') ?></td>
            <td class="status"><?= h($invoice->status) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'Invoices', 'action' => 'view', $invoice->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <!-- // END INVOICES LOOP -->

</table>

<!-- // END INVOICES -->

<?php
foreach ($thisClient->payments as $payment) {
    $paymentsTotal += (float)$payment->amount;
}
$balance = $invoicesTotal - $paymentsTotal;
?>


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-pencil"></span>'
    . $this->Html->link('Edit Client', ['action' => 'edit', $thisClient->id])) ?>
<?= $this->Html->div('info_button_negative', '<span class="icon-cancel"></span>'
    . $this->Form->postLink('Delete Client', ['action' => 'delete', $thisClient->id], [
        'confirm' => 'Are you sure you want to delete this client and all associated data - including all of their invoices, quotes and payments? This action cannot be reversed...',
    ]), ['id' => 'delete_button']) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<?= $this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup']) ?>

<?= $this->Html->script(['/vendor/chartjs-4.6.1/chart.umd.min.js', 'charts']) ?>

<script type="text/javascript">

    $(document).ready(function() {

        var graphColor = <?= json_encode($graphColor, $jsonFlags) ?>;


        // Set Data Table

        if ( $('#invoices_table')[0] ) {

            $('#invoices_table').DataTable( {
            order: [[ 1, "desc" ]],
             drawCallback: function () { updateClasses(); }
            } );
        }


        // Set Invoice Graph

        var currentInvoiceDataJSON = <?= json_encode($yearData_Invoices[$currentYear], $jsonFlags) ?>;

        var currentInvoiceData = []
        var i=0;
        for (var o in currentInvoiceDataJSON) currentInvoiceData[i++]=[o,currentInvoiceDataJSON[o]]


        var previousInvoiceDataJSON = <?= json_encode($yearData_Invoices[$previousYear], $jsonFlags) ?>;

        var previousInvoiceData = []
        i=0;
        for (var o in previousInvoiceDataJSON) previousInvoiceData[i++]=[o,previousInvoiceDataJSON[o]]


        acmInvoiceChart('placeholder', previousInvoiceData, currentInvoiceData, graphColor);


        // Set Stat Totals

        $('#total_invoiced').text(<?= json_encode($currency . number_format($invoicesTotal, 2, '.', ','), $jsonFlags) ?>);

        $('#total_received').text(<?= json_encode($currency . number_format($paymentsTotal, 2, '.', ','), $jsonFlags) ?>);

        $('#client_balance').text(<?= json_encode($currency . number_format($balance, 2, '.', ','), $jsonFlags) ?>);

    });
</script>

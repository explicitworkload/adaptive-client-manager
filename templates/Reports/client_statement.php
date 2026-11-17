<?php
/**
 * Client statement: balance from ALL invoices and payments, optionally with the activity table.
 * Rendered in the client layout when a client user requests it.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 * @var \App\Model\Entity\Setting $settings
 * @var bool $printTables
 * @var array|null $currentUser
 */
$currency = (string)$settings->currency;
$invoicesTotal = 0.0;
foreach ($client->invoices as $invoice) {
    $invoicesTotal += (float)$invoice->invoice_total;
}
$paymentsTotal = 0.0;
foreach ($client->payments as $payment) {
    $paymentsTotal += (float)$payment->amount;
}
$balance = $invoicesTotal - $paymentsTotal;
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<h3>Client Statement</h3>

<p><span class="bold">Report Generated On: </span><?= date('Y-m-d') ?> </p>

<?php /* Not required by client users */ ?>
<?php if (($currentUser['role'] ?? null) !== 'client') : ?>
    <p><span class="bold">By: </span><?= h(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?> </p>
<?php endif; ?>


<h5><?= h($client->client_name) ?></h5>

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


<!-- RECORDS -->

<?php if ($printTables) : ?>

    <h5>Activity By <?= h($client->client_name) ?></h5>

    <table class="datatable" id="invoices_table">

        <thead>
            <tr>
                <th>Date</th>
                <th>Activity</th>
                <th class="right">Amount</th>
            </tr>
        </thead>

        <!-- INVOICES LOOP -->

        <?php foreach ($client->invoices as $invoice) : ?>
            <tr>
                <td><?= $invoice->invoice_date?->format('Y-m-d') ?></td>
                <td>New Invoice (<?= h($invoice->invoice_number) . ' - ' . h($invoice->status) ?>)</td>
                <td class="right"><?= h($currency) . ' ' . number_format((float)$invoice->invoice_total, 2, '.', ',') ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- // END INVOICES LOOP -->


        <!-- PAYMENTS LOOP -->

        <?php foreach ($client->payments as $payment) : ?>
            <tr class="primary">
                <td class="date_cell"><?= $payment->date?->format('Y-m-d') ?></td>
                <td>Payment Received (<?= h($payment->method) ?>)</td>
                <td class="right"><?= '- ' . h($currency) . ' ' . number_format((float)$payment->amount, 2, '.', ',') ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- // END PAYMENTS LOOP -->

    </table>

<?php endif; ?>

<!-- // END RECORDS -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<?= $this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup']) ?>

<script type="text/javascript">

    $(document).ready(function() {


        // Set Data Tables

        if ( $('#invoices_table')[0] ) {

            $('#invoices_table').DataTable( {

                order: [[ 0, "desc" ]],
                dom: '<"top">irt<"bottom"lp><"clear">',
                 drawCallback: function () { updateClasses(); },
                 columns: [{ orderable: false }, { orderable: false }, { orderable: false }]

            } );

        }


        // Set Stat Totals

        $('#total_invoiced').text(<?= json_encode($currency . number_format($invoicesTotal, 2, '.', ','), $jsonFlags) ?>);

        $('#total_received').text(<?= json_encode($currency . number_format($paymentsTotal, 2, '.', ','), $jsonFlags) ?>);

        $('#client_balance').text(<?= json_encode($currency . number_format($balance, 2, '.', ','), $jsonFlags) ?>);

    });

</script>

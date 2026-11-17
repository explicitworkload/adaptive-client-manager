<?php
/**
 * Staff dashboard: monthly invoice chart, recent invoices, quotes expiring soon.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Invoice> $invoices invoices since 1 Jan last year, newest first
 * @var iterable<\App\Model\Entity\Quote> $quotes open quotes expiring in the next 7 days (with ->client)
 * @var \App\Model\Entity\Setting $settings
 */
$this->Html->css('dashboard', ['block' => true]);
$this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup', '/vendor/chartjs-4.6.1/chart.umd.min.js', 'charts'], ['block' => true]);

$currency = (string)$settings->currency;
$graphColor = (string)$settings->primary_color;

$currentYear = (int)date('Y');
$previousYear = $currentYear - 1;

// Invoice totals per month (1-12) for this year and last year.
$yearData = [
    $currentYear => array_fill(1, 12, 0.0),
    $previousYear => array_fill(1, 12, 0.0),
];
$recentInvoices = [];
foreach ($invoices as $invoice) {
    if (count($recentInvoices) < 5) {
        $recentInvoices[] = $invoice;
    }
    if ($invoice->invoice_date === null) {
        continue;
    }
    $year = (int)$invoice->invoice_date->format('Y');
    $month = (int)$invoice->invoice_date->format('n');
    if (isset($yearData[$year])) {
        $yearData[$year][$month] += (float)$invoice->invoice_total;
    }
}

// chart series: [[month, total], ...]
$toSeries = function (array $months): array {
    $series = [];
    foreach ($months as $month => $value) {
        $series[] = [$month, round($value, 2)];
    }

    return $series;
};
$chartData = [
    'graphColor' => $graphColor,
    'current' => $toSeries($yearData[$currentYear]),
    'previous' => $toSeries($yearData[$previousYear]),
];
?>
<h3>Dashboard</h3>

<!-- INVOICES GRAPH -->

<h5>Activity This Year</h5>

<table>

    <thead>
        <tr>
            <th>New Invoices This Year</th>
        </tr>
    </thead>

    <tr><td><span class="bold">Invoice Values Per Month In <?= $currentYear ?></span></td></tr>

    <tr><td><div class="widget_graph" id="placeholder"></div></td></tr>

</table>

<!-- // END INVOICE GRAPH -->


<!-- INVOICES -->

<h5>5 Most Recent Invoices</h5>

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

    <?php foreach ($recentInvoices as $invoice) : ?>
        <tr>
            <td><?= h($invoice->invoice_number) ?></td>
            <td><?= $invoice->invoice_date ? h($invoice->invoice_date->format('Y-m-d')) : '' ?></td>
            <td><?= h($invoice->client_name) ?></td>
            <td class="right"><?= h($currency . number_format((float)$invoice->invoice_total, 2, '.', ',')) ?></td>
            <td class="status"><?= h($invoice->status) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'Invoices', 'action' => 'view', $invoice->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<!-- // END INVOICES -->


<!-- QUOTES -->

<h5>Quotes Expiring In Next 7 Days</h5>

<table class="responsive_table" id="quotes_table">

    <thead>
        <tr>
            <th>Date</th>
            <th>Client</th>
            <th>Expires</th>
            <th class="center">Total</th>
            <th class="center">Status</th>
            <th class="center no_sort">View/Edit</th>
        </tr>
    </thead>

    <?php foreach ($quotes as $quote) : ?>
        <tr>
            <td><?= $quote->quote_date ? h($quote->quote_date->format('Y-m-d')) : '' ?></td>
            <td><?= h($quote->client?->client_name) ?></td>
            <td><?= $quote->expires ? h($quote->expires->format('Y-m-d')) : '' ?></td>
            <td class="right"><?= h($currency . number_format((float)$quote->quote_total, 2, '.', ',')) ?></td>
            <td class="quote_status"><?= h($quote->status) ?></td>
            <td class="center small_cell">
                <?= $this->Html->link('View/Edit', ['controller' => 'Quotes', 'action' => 'view', $quote->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<!-- // END QUOTES -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $this->Html->link('New Client', ['controller' => 'Clients', 'action' => 'add'])) ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $this->Html->link('New Invoice', ['controller' => 'Invoices', 'action' => 'add'])) ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $this->Html->link('New Quote', ['controller' => 'Quotes', 'action' => 'add'])) ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {

        var chartData = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;

        acmInvoiceChart('placeholder', chartData.previous, chartData.current, chartData.graphColor);

    });

</script>

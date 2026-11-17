<?php
/**
 * All quotes.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Quote> $quotes
 * @var \App\Model\Entity\Setting $settings
 */
$this->Html->css('quotesIndex', ['block' => true]);
$this->Html->script(['/vendor/datatables-3.1.5/dataTables.min.js', 'datatables-setup'], ['block' => true]);

$currency = (string)$settings->currency;
?>
<h3>View All Quotes</h3>

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
                <?= $this->Html->link('View/Edit', ['action' => 'view', $quote->id], ['class' => 'view']) ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_primary', '<span class="icon-plus-2"></span>' . $this->Html->link('Add Quote', ['controller' => 'Quotes', 'action' => 'add'])) ?>
<?php $this->end(); ?>

<script type="text/javascript">

    if ( $('#quotes_table')[0] ) {

        $('#quotes_table').DataTable( {
            order: [[ 0, "desc" ]],
            drawCallback: function () { updateClasses(); }
        } );
    }

</script>

<?php
/**
 * Payments report.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Payment> $payments
 * @var \App\Model\Entity\Setting $settings
 * @var string $clientName
 * @var \Cake\I18n\Date $from
 * @var \Cake\I18n\Date $to
 * @var array|null $currentUser
 */
$currency = (string)$settings->currency;
$paymentsTotal = 0.0;
?>
<h3>Payments Report</h3>

<p><span class="bold">Report Generated On: </span><?= date('Y-m-d') ?> </p>

<p><span class="bold">By: </span><?= h(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?> </p>

<p><span class="bold">Clients: </span><?= h($clientName) ?> </p>

<p><span class="bold">From: </span><?= $from->format('Y-m-d') ?> </p>

<p><span class="bold">To: </span><?= $to->format('Y-m-d') ?> </p>

<!-- PAYMENTS -->

<table>

    <tr>
        <th class="center small_cell">Date</th>
        <th>Payment Method</th>
        <th>Client</th>
        <th class="right small_cell">Amount</th>
    </tr>

    <?php if ($payments->isEmpty()) : ?>
        <tr><td colspan="4"> -- No Payments Were Found -- </td></tr>
    <?php endif; ?>

    <?php foreach ($payments as $payment) : ?>
        <tr id="payment_row_<?= (int)$payment->id ?>">
            <td class="date_cell"><?= $payment->date?->format('Y-m-d') ?></td>
            <td><?= h($payment->method) ?></td>
            <td><?= h($payment->client?->client_name) ?></td>
            <td class="right"><?= h($currency) . ' ' . number_format((float)$payment->amount, 2, '.', ',') ?></td>
        </tr>
        <?php $paymentsTotal += (float)$payment->amount; ?>
    <?php endforeach; ?>

    <tr class="bold total_row">
        <td>Total (<?= h($currency) ?>)</td>
        <td></td>
        <td></td>
        <td id="paymentsTotal" class="right"><?= number_format($paymentsTotal, 2, '.', ',') ?></td>
    </tr>

</table>

<!-- // END PAYMENTS -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?= $this->Html->div('div_link info_button_normal', '<span class="icon-arrow-left "></span><a href="javascript:history.back()">Back</a>') ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->

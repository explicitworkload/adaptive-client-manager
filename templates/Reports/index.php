<?php
/**
 * Reports generator: forms posting to the invoices, clientStatement and payments reports.
 *
 * @var \App\View\AppView $this
 * @var array<int, string> $clients client id => client name
 * @var array<string, string> $statuses 'All' + invoice statuses
 */
$yesNoOptions = ['Yes' => 'Yes', 'No' => 'No'];
$defaultFrom = date('Y') . '-01-01';
$defaultTo = date('Y-m-d');
?>
<h3>Reports Generator</h3>


<!-- INVOICES -->

<h5>Invoice Reports</h5><br>

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-briefcase "></span>Build An Invoice Report') ?>

<div class="report_form">

    <h4>Build Your Invoices Report</h4>

    <?= $this->Form->create(null, ['id' => 'ReportsInvoicesForm', 'url' => ['action' => 'invoices']]) ?>
    <?= $this->Form->control('client_name_list', [
        'id' => 'invoices-client-name-list',
        'options' => $clients,
        'empty' => 'All',
        'label' => 'Client',
    ]) ?>
    <?= $this->Form->control('invoice_status', ['options' => $statuses, 'label' => 'Invoice Type']) ?>
    <?= $this->Form->control('invoice_from_date', [
        'id' => 'invoices-invoice-from-date',
        'type' => 'date',
        'value' => $defaultFrom,
        'label' => 'From',
    ]) ?>
    <?= $this->Form->control('invoice_to_date', [
        'id' => 'invoices-invoice-to-date',
        'type' => 'date',
        'value' => $defaultTo,
        'label' => 'To',
    ]) ?>
    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Run Report', ['id' => 'run_invoice_button']) ?>

</div>

<!-- // END INVOICES -->


<!-- CLIENT CONTACT LIST -->

<h5>Client Reports</h5><br>

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-user "></span>Full Client Contact List') ?>

<div class="report_form">

    <h4>Generate Your Client Report</h4>

    <?= $this->Html->div('div_link button_small', '<span class="icon-plus"></span>'
        . $this->Html->link('Run Report', ['controller' => 'Reports', 'action' => 'clientContacts'])) ?>

</div>

<!-- // END CLIENT CONTACT LIST -->


<!-- CLIENT STATEMENT -->

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-user "></span>Client Statement') ?>

<div class="report_form">

    <h4>Build Your Client Statement</h4>

    <p>This will produce a financial statement regarding a client's current account standing calculated on the value of ALL historical invoices and ALL historical payments entered. You can choose to either generate and display all these records in the report, or whether to just simply print the balances without the data table.</p>

    <?= $this->Form->create(null, ['id' => 'ReportsClientStatementForm', 'url' => ['action' => 'clientStatement']]) ?>
    <?= $this->Form->control('client_name_list', [
        'id' => 'statement-client-name-list',
        'options' => $clients,
        'label' => 'Client',
        'required' => true,
    ]) ?>
    <?= $this->Form->control('print_table', ['options' => $yesNoOptions, 'label' => 'Display All Records?']) ?>
    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Run Report', ['id' => 'run_statement_button']) ?>

</div>

<!-- // END CLIENT STATEMENT -->


<!-- PAYMENTS -->

<h5>Payment Reports</h5><br>

<?= $this->Html->div('info_button_primary show_hide_button', '<span class="icon-credit-card "></span>Build A Payment Report') ?>

<div class="report_form">

    <h4>Build Your Payments Report</h4>

    <?= $this->Form->create(null, ['id' => 'ReportsPaymentsForm', 'url' => ['action' => 'payments']]) ?>
    <?= $this->Form->control('client_name_list', [
        'id' => 'payments-client-name-list',
        'options' => $clients,
        'empty' => 'All',
        'label' => 'Client',
    ]) ?>
    <?= $this->Form->control('invoice_from_date', [
        'id' => 'payments-invoice-from-date',
        'type' => 'date',
        'value' => $defaultFrom,
        'label' => 'From',
    ]) ?>
    <?= $this->Form->control('invoice_to_date', [
        'id' => 'payments-invoice-to-date',
        'type' => 'date',
        'value' => $defaultTo,
        'label' => 'To',
    ]) ?>
    <?= $this->Form->end() ?>

    <?= $this->Html->div('button_small', '<span class="icon-plus"></span>Run Report', ['id' => 'run_payments_button']) ?>

</div>

<!-- // END PAYMENTS -->


<!-- INFO TILE -->

<?php $this->start('info_tile'); ?>
<?php $this->end(); ?>

<!-- // END INFO TILE -->


<!-- SCRIPTS -->

<script type="text/javascript">

    $(document).ready(function() {


        // Style Select Boxes

        $('select').wrap('<div class="styled_select" />');


        // Run Invoice Report

        $('#run_invoice_button').click(function() {

            $('#ReportsInvoicesForm').submit();

        });


        // Run Statement Report

        $('#run_statement_button').click(function() {

            $('#ReportsClientStatementForm').submit();

        });


        // Run Payments Report

        $('#run_payments_button').click(function() {

            $('#ReportsPaymentsForm').submit();

        });


        // Hide Forms By Default

        $('.report_form').hide();


        // Show Forms On Click

        $('.show_hide_button').click(function() {

            $('.report_form').slideUp();

            $(this).next('div').slideToggle();

        });


    });

</script>

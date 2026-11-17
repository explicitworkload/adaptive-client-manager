<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Chronos\ChronosDate;
use Cake\Validation\Validator;
use DateTimeInterface;

class InvoicesTable extends AppTable
{
    public const STATUSES = ['Paid', 'Unpaid', 'Part Paid', 'Overpaid'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('invoices');
        $this->setDisplayField('invoice_number');

        $this->belongsTo('Clients');
        $this->hasMany('Items', ['dependent' => true, 'sort' => ['Items.id' => 'ASC']]);
        $this->hasMany('Payments', ['dependent' => true, 'sort' => ['Payments.date' => 'ASC']]);
    }

    public function validationDefault(Validator $v): Validator
    {
        $v->requirePresence('invoice_date', 'create')
            ->notEmptyDate('invoice_date', 'Enter a valid date in YY-MM-DD format.')
            ->date('invoice_date', ['ymd'], 'Enter a valid date in YY-MM-DD format.');
        $this->requiredString($v, 'invoice_number', 20, 'Invoice Number must be between 1 and 20 characters long');
        $v->requirePresence('client_id', 'create')->integer('client_id', 'Please select a client');

        return $v;
    }

    /**
     * Next invoice number: YYMMDD followed by a 4-digit sequence (last id + 1),
     * matching the format the legacy add form generated in JavaScript.
     */
    public function nextNumber(ChronosDate|DateTimeInterface $date): string
    {
        $last = $this->find()->select(['id'])->orderByDesc('id')->first();
        $seq = (($last?->id ?? 0) + 1) % 10000;

        return $date->format('ymd') . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recompute invoice_total and status from items and payments.
     *
     * The legacy app did this in browser JavaScript on the edit page and
     * auto-submitted the form; doing it here keeps the stored values correct
     * no matter how items or payments change.
     */
    public function recalculate(int $invoiceId): void
    {
        $invoice = $this->get($invoiceId, contain: ['Items', 'Payments']);

        $total = 0.0;
        foreach ($invoice->items as $item) {
            $sub = (float)$item->quantity * (float)$item->unit_price;
            $total += $sub + $sub * (float)$item->tax_rate / 100;
        }
        $total = round($total, 2);

        $paid = 0.0;
        foreach ($invoice->payments as $payment) {
            $paid += (float)$payment->amount;
        }
        $balance = round($total - $paid, 2);

        $status = $invoice->status;
        if ($total > 0) {
            if ($balance < 0) {
                $status = 'Overpaid';
            } elseif ($balance == 0) {
                $status = 'Paid';
            } elseif ($paid == 0) {
                $status = 'Unpaid';
            } else {
                $status = 'Part Paid';
            }
        }

        $this->updateAll(['invoice_total' => $total, 'status' => $status], ['id' => $invoiceId]);
    }
}

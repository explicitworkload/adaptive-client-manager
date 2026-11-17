<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Chronos\ChronosDate;
use Cake\I18n\Date;
use Cake\Validation\Validator;
use DateTimeInterface;

class QuotesTable extends AppTable
{
    public const STATUSES = ['Pending', 'Accepted', 'Rejected', 'Expired'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('quotes');
        $this->setDisplayField('quote_number');

        $this->belongsTo('Clients');
        $this->hasMany('QuoteItems', ['dependent' => true, 'sort' => ['QuoteItems.id' => 'ASC']]);
    }

    public function validationDefault(Validator $v): Validator
    {
        $v->requirePresence('quote_date', 'create')
            ->notEmptyDate('quote_date', 'Enter a valid date in YY-MM-DD format')
            ->date('quote_date', ['ymd'], 'Enter a valid date in YY-MM-DD format');
        $this->requiredString($v, 'quote_number', 20, 'Quote Number must be between 1 and 20 characters long');
        $v->requirePresence('client_id', 'create')->integer('client_id', 'Please select a client');
        $v->allowEmptyDate('expires')->date('expires', ['ymd']);
        $v->inList('status', self::STATUSES);

        return $v;
    }

    /** Same YYMMDD + 4-digit sequence format as invoices. */
    public function nextNumber(ChronosDate|DateTimeInterface $date): string
    {
        $last = $this->find()->select(['id'])->orderByDesc('id')->first();
        $seq = (($last?->id ?? 0) + 1) % 10000;

        return $date->format('ymd') . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    /** Recompute quote_total from its line items. */
    public function recalculate(int $quoteId): void
    {
        $total = 0.0;
        foreach ($this->QuoteItems->find()->where(['quote_id' => $quoteId]) as $item) {
            $sub = (float)$item->quantity * (float)$item->unit_price;
            $total += $sub + $sub * (float)$item->tax_rate / 100;
        }
        $this->updateAll(['quote_total' => round($total, 2)], ['id' => $quoteId]);
    }

    /** Mark pending quotes whose expiry date has passed as Expired. */
    public function expireOverdue(): int
    {
        return $this->updateAll(
            ['status' => 'Expired'],
            ['status' => 'Pending', 'expires <' => Date::today()],
        );
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;

class PaymentsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('payments');
        $this->belongsTo('Clients');
        $this->belongsTo('Invoices');
    }

    public function validationDefault(Validator $v): Validator
    {
        $v->requirePresence('date', 'create')
            ->notEmptyDate('date', 'Enter a valid date in YY-MM-DD format.')
            ->date('date', ['ymd'], 'Enter a valid date in YY-MM-DD format.');
        $this->requiredString($v, 'method', 20, 'Payment Method must be between 1 and 20 characters long');
        $v->requirePresence('amount', 'create')
            ->notEmptyString('amount', 'Please supply the payment amount')
            ->numeric('amount', 'Please supply the payment amount');
        $v->requirePresence('invoice_id', 'create')->integer('invoice_id');
        $v->requirePresence('client_id', 'create')->integer('client_id');

        return $v;
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->Invoices->recalculate((int)$entity->get('invoice_id'));
    }

    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->Invoices->recalculate((int)$entity->get('invoice_id'));
    }
}

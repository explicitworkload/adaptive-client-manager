<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Shared behaviour for invoice items and quote items: identical validation,
 * and the parent document's total is recalculated whenever a line changes.
 */
abstract class LineItemsTable extends AppTable
{
    /** Name of the parent association, e.g. 'Invoices'. */
    abstract protected function parentAlias(): string;

    abstract protected function parentKey(): string;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('title');
        $this->belongsTo($this->parentAlias(), ['foreignKey' => $this->parentKey()]);
    }

    public function validationDefault(Validator $v): Validator
    {
        $this->requiredString($v, 'title', 100, 'Title must be between 1 and 100 characters long');
        $v->requirePresence('quantity', 'create')
            ->notEmptyString('quantity', 'Please supply the quantity of this item')
            ->numeric('quantity', 'Please supply the quantity of this item');
        $v->requirePresence('unit_price', 'create')
            ->notEmptyString('unit_price', 'Please supply the price per unit of this item - no currency symbols required')
            ->numeric('unit_price', 'Please supply the price per unit of this item - no currency symbols required');
        $v->allowEmptyString('tax_rate')->numeric('tax_rate');
        $v->requirePresence($this->parentKey(), 'create')->integer($this->parentKey());

        return $v;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn($this->parentKey(), $this->parentAlias()));

        return $rules;
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->{$this->parentAlias()}->recalculate((int)$entity->get($this->parentKey()));
    }

    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->{$this->parentAlias()}->recalculate((int)$entity->get($this->parentKey()));
    }
}

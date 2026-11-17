<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;

class TaxratesTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('taxrates');
        $this->setDisplayField('name');
    }

    public function validationDefault(Validator $v): Validator
    {
        $this->requiredString($v, 'name', 64, 'Name must be between 1 and 64 characters long');
        $v->requirePresence('rate', 'create')->numeric('rate', 'Please enter the rate as a number, e.g. 20');

        return $v;
    }

    /**
     * Options for the tax rate dropdown: rate => rate, lowest first.
     *
     * @return array<string, string>
     */
    public function rateOptions(): array
    {
        return $this->find('list', keyField: 'rate', valueField: 'rate')
            ->orderBy(['rate' => 'ASC'])
            ->toArray();
    }
}

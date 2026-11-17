<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Shared helpers for translating the legacy CakePHP 2 validation rules.
 */
abstract class AppTable extends Table
{
    /**
     * Legacy rule: `between 1..$max`, required, not empty.
     */
    protected function requiredString(Validator $v, string $field, int $max, string $message, int $min = 1): Validator
    {
        return $v->requirePresence($field, 'create')
            ->notEmptyString($field, $message)
            ->lengthBetween($field, [$min, $max], $message);
    }

    /**
     * Legacy rule: `between 1..$max`, optional, may be empty.
     */
    protected function optionalString(Validator $v, string $field, int $max, string $message): Validator
    {
        return $v->allowEmptyString($field)->maxLength($field, $max, $message);
    }
}

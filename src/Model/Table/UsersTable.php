<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Schema\Collection;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

class UsersTable extends AppTable
{
    public const ROLES = ['admin' => 'Admin', 'manager' => 'Manager', 'client' => 'Client'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('users');
        $this->setDisplayField('username');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $v): Validator
    {
        $this->requiredString($v, 'username', 50, 'Username must be between 1 and 50 characters long');
        // Required when creating; on edit an empty password means "keep current".
        $v->requirePresence('password', 'create')
            ->notEmptyString('password', 'Password must be between 6 and 32 characters long', 'create')
            ->allowEmptyString('password', null, 'update')
            ->lengthBetween('password', [6, 32], 'Password must be between 6 and 32 characters long');
        $this->requiredString($v, 'first_name', 64, 'Name must be between 1 and 64 characters long');
        $this->requiredString($v, 'last_name', 64, 'Name must be between 1 and 64 characters long');
        $this->requiredString($v, 'email', 64, 'Email must be between 1 and 64 characters long');
        $v->email('email', false, 'Please enter a valid email address');
        $v->requirePresence('role', 'create')->inList('role', array_keys(self::ROLES));

        return $v;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username'], 'This username has already been taken'));

        return $rules;
    }

    /**
     * Whether users.password can hold a bcrypt hash (60 chars). Legacy
     * databases may have a 40-char column sized for sha1.
     */
    public function canStoreModernHashes(): bool
    {
        // Read the live column, not the cached schema: in production the schema
        // cache can predate config/schema/upgrade_existing_database.sql.
        $live = (new Collection($this->getConnection()))->describe($this->getTable());
        $length = $live->getColumn('password')['length'] ?? null;

        return $length === null || $length >= 60;
    }
}

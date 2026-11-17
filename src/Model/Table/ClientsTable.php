<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

class ClientsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('clients');
        $this->setDisplayField('client_name');

        $this->hasMany('Invoices', ['dependent' => true, 'cascadeCallbacks' => true]);
        $this->hasMany('Payments', ['dependent' => true]);
        // Not in the legacy model, which left orphaned quotes behind on delete.
        $this->hasMany('Quotes', ['dependent' => true, 'cascadeCallbacks' => true]);
    }

    public function validationDefault(Validator $v): Validator
    {
        $this->requiredString($v, 'client_name', 64, 'Name must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_contact', 64, 'Contact must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_phone', 32, 'Phone number must be between 1 and 32 characters long');
        $this->optionalString($v, 'client_email', 64, 'Email must be between 1 and 64 characters long');
        $v->email('client_email', false, 'Please enter a valid email address');
        $this->requiredString($v, 'client_address_line_1', 64, 'Address line must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_address_line_2', 64, 'Address line must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_town', 64, 'Town must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_city', 64, 'City must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_state', 64, 'State must be between 1 and 64 characters long');
        $this->optionalString($v, 'client_zip', 32, 'Zip/Postal Code must be between 1 and 32 characters long');

        return $v;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['client_name'], 'A client with this name has already been set up'));

        return $rules;
    }

    /**
     * Keep a rename consistent: client logins are matched to their client by
     * name, and invoices store a copy of the name. In the legacy app a
     * rename silently locked the client out of their area.
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() || !$entity->isDirty('client_name')) {
            return;
        }
        $old = $entity->getOriginal('client_name');
        $new = $entity->get('client_name');
        TableRegistry::getTableLocator()->get('Users')->updateAll(['first_name' => $new], ['role' => 'client', 'first_name' => $old]);
        $this->Invoices->updateAll(['client_name' => $new], ['client_id' => $entity->get('id')]);
    }

    /**
     * Client users are linked to their client record by name: the legacy app
     * stores the client company name in users.first_name.
     */
    public function findIdForClientUser(?string $firstName): ?int
    {
        if ($firstName === null || $firstName === '') {
            return null;
        }
        $client = $this->find()->select(['id'])->where(['client_name' => $firstName])->first();

        return $client?->id;
    }
}

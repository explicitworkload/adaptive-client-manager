<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Setting;
use Cake\Validation\Validator;

class SettingsTable extends AppTable
{
    /** The app keeps a single settings row. */
    public const ID = 1;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('settings');
    }

    public function validationDefault(Validator $v): Validator
    {
        $this->requiredString($v, 'company_name', 64, 'Name must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_phone', 32, 'Phone number must be between 1 and 32 characters long');
        $this->optionalString($v, 'company_email', 64, 'Email must be between 1 and 64 characters long');
        $v->email('company_email', false, 'Please enter a valid email address');
        $this->requiredString($v, 'company_address_line_1', 64, 'Address line must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_address_line_2', 64, 'Address line must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_town', 64, 'Town must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_city', 64, 'City must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_state', 64, 'State must be between 1 and 64 characters long');
        $this->optionalString($v, 'company_zip', 32, 'Zip/Postal Code must be between 1 and 32 characters long');
        $this->optionalString($v, 'currency', 8, 'Currency symbol must be at most 8 characters');
        // Written into the theme CSS file, so only accept a hex colour.
        $v->allowEmptyString('primary_color')
            ->regex('primary_color', '/^#[0-9a-fA-F]{6}$/', 'Please choose a colour in #RRGGBB format');

        return $v;
    }

    public function current(): Setting
    {
        /** @var \App\Model\Entity\Setting */
        return $this->get(self::ID);
    }
}

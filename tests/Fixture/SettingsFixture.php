<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SettingsFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'company_name' => 'Acme', 'company_email' => 'billing@acme.test', 'company_address_line_1' => '1 High St', 'currency' => '£', 'primary_color' => '#339966'],
    ];
}

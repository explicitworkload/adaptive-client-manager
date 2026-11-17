<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ClientsFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'client_name' => 'Globex', 'client_email' => 'hank@globex.test', 'client_address_line_1' => '5 Volcano Rd'],
        ['id' => 2, 'client_name' => 'Initech', 'client_email' => 'bill@initech.test', 'client_address_line_1' => '9 Office Park'],
    ];
}

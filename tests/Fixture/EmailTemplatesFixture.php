<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EmailTemplatesFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'name' => 'Client Invoice', 'content' => '<p>Invoice [INVOICE_NUMBER] for [CLIENT_NAME]</p>'],
        ['id' => 2, 'name' => 'Client Quote', 'content' => '<p>Quote [QUOTE_NUMBER]</p>'],
    ];
}

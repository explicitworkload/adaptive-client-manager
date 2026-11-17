<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ItemsFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'invoice_id' => 1, 'title' => 'Design', 'details' => '', 'quantity' => 1, 'unit_price' => 500, 'tax_rate' => 20],
    ];
}

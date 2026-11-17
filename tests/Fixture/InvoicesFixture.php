<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InvoicesFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'client_id' => 1, 'client_name' => 'Globex', 'invoice_number' => '2611170001', 'invoice_date' => '2026-11-17', 'invoice_total' => 0, 'status' => 'Unpaid'],
        ['id' => 2, 'client_id' => 2, 'client_name' => 'Initech', 'invoice_number' => '2611170002', 'invoice_date' => '2026-11-17', 'invoice_total' => 0, 'status' => 'Unpaid'],
    ];
}

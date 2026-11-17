<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class QuotesFixture extends TestFixture
{
    public array $records = [
        ['id' => 1, 'client_id' => 1, 'quote_number' => '2611170001', 'quote_date' => '2026-11-17', 'expires' => '2026-12-17', 'quote_total' => 0, 'status' => 'Pending'],
    ];
}

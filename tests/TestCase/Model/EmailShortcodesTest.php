<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model;

use App\Model\Entity\Client;
use App\Model\Entity\Setting;
use App\Service\EmailShortcodes;
use Cake\TestSuite\TestCase;

class EmailShortcodesTest extends TestCase
{
    public function testFillsShortcodesAndEscapesValues(): void
    {
        $html = EmailShortcodes::render(
            '<p>[INVOICE_NUMBER] for [CLIENT_NAME]: [INVOICE_TOTAL] from [USER]</p>',
            'INVOICE',
            '2611170001',
            '2026-11-17',
            '700.00',
            new Client(['client_name' => 'Globex <Ltd>']),
            new Setting(['currency' => '£']),
            'Ada Min',
        );
        $this->assertSame('<p>2611170001 for Globex &lt;Ltd&gt;: £700.00 from Ada Min</p>', $html);
    }
}

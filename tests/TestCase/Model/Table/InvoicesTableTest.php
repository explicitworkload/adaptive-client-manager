<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Invoice;
use App\Model\Table\InvoicesTable;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;

/**
 * Invoice totals and status are now computed server-side (the legacy app did
 * it in browser JavaScript on the edit page).
 */
class InvoicesTableTest extends TestCase
{
    protected array $fixtures = ['app.Clients', 'app.Invoices', 'app.Items', 'app.Payments'];

    private InvoicesTable $Invoices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Invoices = $this->fetchTable('Invoices');
    }

    private function invoice(): Invoice
    {
        return $this->Invoices->get(1);
    }

    private function addPayment(float $amount): void
    {
        $payments = $this->fetchTable('Payments');
        $payments->saveOrFail($payments->newEntity([
            'invoice_id' => 1, 'client_id' => 1, 'date' => '2026-11-17', 'method' => 'Cash', 'amount' => $amount,
        ]));
    }

    public function testItemChangesRecalculateTotalIncludingTax(): void
    {
        $items = $this->fetchTable('Items');
        $items->saveOrFail($items->newEntity([
            'invoice_id' => 1, 'title' => 'Hosting', 'quantity' => 2, 'unit_price' => 50, 'tax_rate' => 0,
        ]));
        // 1 x 500 + 20% tax, plus 2 x 50 untaxed.
        $this->assertEquals(700.0, (float)$this->invoice()->invoice_total);
        $this->assertSame('Unpaid', $this->invoice()->status);

        $items->delete($items->get(1));
        $this->assertEquals(100.0, (float)$this->invoice()->invoice_total);
    }

    public function testPaymentsDriveStatus(): void
    {
        $this->Invoices->recalculate(1); // total 600
        $this->assertSame('Unpaid', $this->invoice()->status);

        $this->addPayment(200);
        $this->assertSame('Part Paid', $this->invoice()->status);

        $this->addPayment(400);
        $this->assertSame('Paid', $this->invoice()->status);

        $this->addPayment(0.01);
        $this->assertSame('Overpaid', $this->invoice()->status);

        $payments = $this->fetchTable('Payments');
        $payments->deleteAll(['invoice_id' => 1]);
        $this->Invoices->recalculate(1);
        $this->assertSame('Unpaid', $this->invoice()->status);
    }

    public function testFloatRoundingStillCountsAsPaid(): void
    {
        $items = $this->fetchTable('Items');
        $items->deleteAll([]);
        $items->saveOrFail($items->newEntity([
            'invoice_id' => 1, 'title' => 'Odd', 'quantity' => 3, 'unit_price' => 0.1, 'tax_rate' => 0,
        ]));
        $this->addPayment(0.3);
        $this->assertSame('Paid', $this->invoice()->status);
    }

    public function testNextNumberIsDatePlusSequence(): void
    {
        $this->assertSame('2611180003', $this->Invoices->nextNumber(new Date('2026-11-18')));
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

/** Invoice line items. */
class ItemsTable extends LineItemsTable
{
    protected function parentAlias(): string
    {
        return 'Invoices';
    }

    protected function parentKey(): string
    {
        return 'invoice_id';
    }
}

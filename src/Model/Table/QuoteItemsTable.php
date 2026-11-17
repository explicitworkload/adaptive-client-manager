<?php
declare(strict_types=1);

namespace App\Model\Table;

/** Quote line items. */
class QuoteItemsTable extends LineItemsTable
{
    protected function parentAlias(): string
    {
        return 'Quotes';
    }

    protected function parentKey(): string
    {
        return 'quote_id';
    }
}

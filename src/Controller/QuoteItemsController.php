<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Quote line items. Same behaviour as invoice items, attached to a quote.
 */
class QuoteItemsController extends ItemsController
{
    protected string $parentController = 'Quotes';

    protected string $parentKey = 'quote_id';

    protected string $tableName = 'QuoteItems';
}

<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Recompute every invoice's total/status and every quote's total.
 *
 * Run once after upgrading: the legacy app only stored these when an edit
 * page happened to be opened in a browser, so old rows can be stale.
 */
class RecalculateTotalsCommand extends Command
{
    public static function defaultName(): string
    {
        return 'recalculate_totals';
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $invoices = $this->fetchTable('Invoices');
        $quotes = $this->fetchTable('Quotes');
        $n = 0;
        foreach ($invoices->find()->select(['id'])->all() as $row) {
            $invoices->recalculate($row->id);
            $n++;
        }
        $m = 0;
        foreach ($quotes->find()->select(['id'])->all() as $row) {
            $quotes->recalculate($row->id);
            $m++;
        }
        $io->success("Recalculated {$n} invoices and {$m} quotes.");

        return static::CODE_SUCCESS;
    }
}

<?php
declare(strict_types=1);

use App\Model\Table\EmailTemplatesTable;
use Migrations\BaseMigration;
use Migrations\Db\Table;

/**
 * Schema for Adaptive Client Manager.
 *
 * The original setup script (adaptivecm_setup.sql) was not in the legacy
 * codebase, so this schema was reconstructed from every field the CakePHP 2
 * models, controllers and views read or wrote.
 *
 * Idempotent: on an existing legacy database it only creates missing tables
 * and widens users.password so it can hold bcrypt hashes.
 */
class InitialSchema extends BaseMigration
{
    public bool $autoId = false;

    public function up(): void
    {
        $this->createClients();
        $this->createInvoices();
        $this->createItems('items', 'invoice_id');
        $this->createQuotes();
        $this->createItems('quote_items', 'quote_id');
        $this->createPayments();
        $this->createSettings();
        $this->createUsers();
        $this->createEmailTemplates();
        $this->createTaxrates();
    }

    public function down(): void
    {
        foreach (
            ['taxrates', 'email_templates', 'users', 'settings', 'payments',
            'quote_items', 'quotes', 'items', 'invoices', 'clients'] as $table
        ) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }

    private function newTable(string $name): ?Table
    {
        if ($this->hasTable($name)) {
            return null;
        }

        return $this->table($name)
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'signed' => false])
            ->addPrimaryKey('id');
    }

    private function str(int $limit = 64): array
    {
        return ['limit' => $limit, 'null' => true, 'default' => null];
    }

    private function money(): array
    {
        return ['precision' => 12, 'scale' => 2, 'default' => 0, 'null' => false];
    }

    private function createClients(): void
    {
        $t = $this->newTable('clients');
        if (!$t) {
            return;
        }
        $t->addColumn('client_name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('client_contact', 'string', $this->str())
            ->addColumn('client_phone', 'string', $this->str(32))
            ->addColumn('client_email', 'string', $this->str())
            ->addColumn('client_address_line_1', 'string', $this->str())
            ->addColumn('client_address_line_2', 'string', $this->str())
            ->addColumn('client_town', 'string', $this->str())
            ->addColumn('client_city', 'string', $this->str())
            ->addColumn('client_state', 'string', $this->str())
            ->addColumn('client_zip', 'string', $this->str(32))
            ->addColumn('custom_1', 'string', $this->str(255))
            ->addIndex(['client_name'], ['unique' => true])
            ->create();
    }

    private function createInvoices(): void
    {
        $t = $this->newTable('invoices');
        if (!$t) {
            return;
        }
        $t->addColumn('client_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('client_name', 'string', $this->str())
            ->addColumn('invoice_number', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('invoice_date', 'date', ['null' => false])
            ->addColumn('invoice_total', 'decimal', $this->money())
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'Unpaid'])
            ->addColumn('public_notes', 'text', ['null' => true])
            ->addColumn('private_notes', 'text', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => true])
            ->addColumn('created_by', 'string', $this->str(130))
            ->addColumn('last_modified', 'datetime', ['null' => true])
            ->addColumn('last_mod_by', 'string', $this->str(130))
            ->addIndex(['client_id'])
            ->addIndex(['invoice_number'])
            ->create();
    }

    private function createQuotes(): void
    {
        $t = $this->newTable('quotes');
        if (!$t) {
            return;
        }
        $t->addColumn('client_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('quote_number', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('quote_date', 'date', ['null' => false])
            ->addColumn('expires', 'date', ['null' => true])
            ->addColumn('quote_total', 'decimal', $this->money())
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'Pending'])
            ->addColumn('public_notes', 'text', ['null' => true])
            ->addColumn('private_notes', 'text', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => true])
            ->addColumn('created_by', 'string', $this->str(130))
            ->addColumn('modified', 'datetime', ['null' => true])
            ->addColumn('last_mod_by', 'string', $this->str(130))
            ->addIndex(['client_id'])
            ->addIndex(['quote_number'])
            ->create();
    }

    /** Invoice line items and quote line items share one shape. */
    private function createItems(string $table, string $parentKey): void
    {
        $t = $this->newTable($table);
        if (!$t) {
            return;
        }
        $t->addColumn($parentKey, 'integer', ['signed' => false, 'null' => false])
            ->addColumn('title', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('quantity', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 1])
            ->addColumn('unit_price', 'decimal', $this->money())
            ->addColumn('tax_rate', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0])
            ->addIndex([$parentKey])
            ->create();
    }

    private function createPayments(): void
    {
        $t = $this->newTable('payments');
        if (!$t) {
            return;
        }
        $t->addColumn('invoice_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('client_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('date', 'date', ['null' => false])
            ->addColumn('method', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('amount', 'decimal', $this->money())
            ->addIndex(['invoice_id'])
            ->addIndex(['client_id'])
            ->create();
    }

    private function createSettings(): void
    {
        $t = $this->newTable('settings');
        if (!$t) {
            return;
        }
        $t->addColumn('company_name', 'string', ['limit' => 64, 'default' => 'My Company'])
            ->addColumn('company_phone', 'string', $this->str(32))
            ->addColumn('company_email', 'string', $this->str())
            ->addColumn('company_address_line_1', 'string', ['limit' => 64, 'default' => ''])
            ->addColumn('company_address_line_2', 'string', $this->str())
            ->addColumn('company_town', 'string', $this->str())
            ->addColumn('company_city', 'string', $this->str())
            ->addColumn('company_state', 'string', $this->str())
            ->addColumn('company_zip', 'string', $this->str(32))
            ->addColumn('custom_1', 'string', $this->str(255))
            ->addColumn('payment_info', 'text', ['null' => true])
            ->addColumn('currency', 'string', ['limit' => 8, 'default' => '$'])
            ->addColumn('primary_color', 'string', ['limit' => 7, 'default' => '#339966'])
            ->create();

        // The app always reads/writes settings row 1.
        $this->table('settings')->insert([['id' => 1]])->save();
    }

    private function createUsers(): void
    {
        $t = $this->newTable('users');
        if (!$t) {
            // Legacy table: sha1 hashes were 40 chars; bcrypt needs 60+.
            $this->table('users')->changeColumn('password', 'string', ['limit' => 255, 'null' => false])->update();

            return;
        }
        $t->addColumn('username', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('first_name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('last_name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('role', 'string', ['limit' => 20, 'default' => 'manager'])
            ->addColumn('created', 'datetime', ['null' => true])
            ->addColumn('modified', 'datetime', ['null' => true])
            ->addIndex(['username'], ['unique' => true])
            ->create();
    }

    private function createEmailTemplates(): void
    {
        $t = $this->newTable('email_templates');
        if (!$t) {
            return;
        }
        $t->addColumn('name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('content', 'text', ['null' => true])
            ->create();

        // Ids 1 and 2 are hard-wired in the app (EmailTemplatesController).
        $this->table('email_templates')->insert([
            ['id' => 1, 'name' => 'Client Invoice', 'content' => EmailTemplatesTable::DEFAULTS['invoice']],
            ['id' => 2, 'name' => 'Client Quote', 'content' => EmailTemplatesTable::DEFAULTS['quote']],
        ])->save();
    }

    private function createTaxrates(): void
    {
        $t = $this->newTable('taxrates');
        if (!$t) {
            return;
        }
        $t->addColumn('name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('rate', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0])
            ->create();

        $this->table('taxrates')->insert([['name' => 'No Tax', 'rate' => 0]])->save();
    }
}

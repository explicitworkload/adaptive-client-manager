<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\InvoicesTable;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\Date;

/**
 * Report forms on the index page submit to the other actions. Each report
 * accepts the form fields as POST data or as query-string parameters.
 */
class ReportsController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'Reports');
        $this->confineClients($event, ['clientStatement']);
    }

    public function index(): void
    {
        $clients = $this->fetchTable('Clients')->find('list')->orderBy(['client_name' => 'ASC'])->toArray();
        $statuses = ['All' => 'All'] + array_combine(InvoicesTable::STATUSES, InvoicesTable::STATUSES);
        $this->set(compact('clients', 'statuses'));
    }

    public function invoices(): void
    {
        [$clientId, $clientName] = $this->clientFilter();
        [$from, $to] = $this->dateRange();
        $status = (string)$this->input('invoice_status', 'All');
        $statuses = $status === 'All' ? InvoicesTable::STATUSES : [$status];

        $query = $this->fetchTable('Invoices')->find()
            ->where([
                'status IN' => $statuses,
                'invoice_date >=' => $from,
                'invoice_date <=' => $to,
            ])
            ->orderBy(['invoice_date' => 'ASC']);
        if ($clientId !== null) {
            $query->where(['client_id' => $clientId]);
        }

        $this->set([
            'invoices' => $query->all(),
            'settings' => $this->fetchTable('Settings')->current(),
            'clientName' => $clientName,
            'invoiceType' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function clientContacts(): void
    {
        $this->set('clients', $this->fetchTable('Clients')->find()->orderBy(['client_name' => 'ASC'])->all());
    }

    public function clientStatement(): ?Response
    {
        // Client users can only ever see their own statement.
        $id = $this->isClient() ? $this->currentClientId() : (int)$this->input('client_name_list');
        if (!$id) {
            $this->Flash->error('Please select a client');

            return $this->redirect(['action' => 'index']);
        }
        $client = $this->fetchTable('Clients')->get($id, contain: [
            'Invoices' => ['sort' => ['Invoices.invoice_date' => 'ASC']],
            'Payments' => ['sort' => ['Payments.date' => 'ASC']],
        ]);
        if ($this->isClient()) {
            $this->viewBuilder()->setLayout('client');
        }
        $this->set([
            'client' => $client,
            'settings' => $this->fetchTable('Settings')->current(),
            'printTables' => $this->input('print_table') === 'Yes',
        ]);

        return null;
    }

    public function payments(): void
    {
        [$clientId, $clientName] = $this->clientFilter();
        [$from, $to] = $this->dateRange();

        $query = $this->fetchTable('Payments')->find()
            ->contain(['Clients'])
            ->where(['Payments.date >=' => $from, 'Payments.date <=' => $to])
            ->orderBy(['Payments.date' => 'ASC']);
        if ($clientId !== null) {
            $query->where(['Payments.client_id' => $clientId]);
        }

        $this->set([
            'payments' => $query->all(),
            'settings' => $this->fetchTable('Settings')->current(),
            'clientName' => $clientName,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function input(string $key, mixed $default = null): mixed
    {
        return $this->request->getData($key) ?? $this->request->getQuery($key) ?? $default;
    }

    /**
     * Empty client selection means "All".
     *
     * @return array{0: int|null, 1: string}
     */
    private function clientFilter(): array
    {
        $id = (int)$this->input('client_name_list');
        if ($id === 0) {
            return [null, 'All'];
        }
        $client = $this->fetchTable('Clients')->get($id);

        return [$id, (string)$client->client_name];
    }

    /**
     * Report date range from the from/to date inputs (YYYY-MM-DD).
     * Defaults: 1 January of this year to today.
     *
     * @return array{0: \Cake\I18n\Date, 1: \Cake\I18n\Date}
     */
    private function dateRange(): array
    {
        $parse = function (string $key, Date $default): Date {
            $value = $this->input($key);
            if (is_array($value)) { // legacy year/month/day select format
                $value = sprintf('%04d-%02d-%02d', $value['year'] ?? 0, $value['month'] ?? 0, $value['day'] ?? 0);
            }
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return new Date($value);
            }

            return $default;
        };
        $today = Date::today();

        return [
            $parse('invoice_from_date', new Date($today->year . '-01-01')),
            $parse('invoice_to_date', $today),
        ];
    }
}

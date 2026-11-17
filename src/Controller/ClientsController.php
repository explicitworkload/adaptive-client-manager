<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Client;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * @property \App\Model\Table\ClientsTable $Clients
 */
class ClientsController extends AppController
{
    private const FIELDS = [
        'client_name', 'client_contact', 'client_phone', 'client_email',
        'client_address_line_1', 'client_address_line_2', 'client_town',
        'client_city', 'client_state', 'client_zip', 'custom_1',
    ];

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'clients');
        $this->confineClients($event, ['clientArea']);
    }

    public function index(): void
    {
        $this->set('clients', $this->Clients->find()->orderBy(['client_name' => 'ASC'])->all());
    }

    public function view(?string $id = null): void
    {
        $this->set('thisClient', $this->loadWithDocuments($id));
        $this->set('settings', $this->fetchTable('Settings')->current());
    }

    public function add(): ?Response
    {
        $client = $this->Clients->newEmptyEntity();
        if ($this->request->is('post')) {
            $client = $this->Clients->patchEntity($client, $this->request->getData(), ['fields' => self::FIELDS]);
            if ($this->Clients->save($client)) {
                $this->Flash->success('Client Successfully Added');

                return $this->redirect(['action' => 'view', $client->id]);
            }
            $this->Flash->error('Unable To Add Client');
        }
        $this->set(compact('client'));

        return null;
    }

    public function edit(?string $id = null): ?Response
    {
        $client = $this->Clients->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $client = $this->Clients->patchEntity($client, $this->request->getData(), ['fields' => self::FIELDS]);
            if ($this->Clients->save($client)) {
                $this->Flash->success('Your Client Has Been Updated');

                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error('Unable To Update Your Client');
        }
        $this->set(compact('client'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Clients->delete($this->Clients->get($id))) {
            $this->Flash->success('The Client Has Been Deleted.');
        } else {
            $this->Flash->error('Unable To Delete Client');
        }

        return $this->redirect(['action' => 'index']);
    }

    /** The only page client users can browse: their own invoices, quotes and payments. */
    public function clientArea(?string $id = null): ?Response
    {
        $client = $this->loadWithDocuments($id);
        if ($this->clientDeniedFor($client->id)) {
            return $this->denyOtherClient();
        }
        $this->viewBuilder()->setLayout('client');
        $this->set('thisClient', $client);
        $this->set('settings', $this->fetchTable('Settings')->current());

        return null;
    }

    private function loadWithDocuments(?string $id): Client
    {
        /** @var \App\Model\Entity\Client */
        return $this->Clients->get($id, contain: [
            'Invoices' => ['sort' => ['Invoices.invoice_date' => 'DESC']],
            'Payments' => ['sort' => ['Payments.date' => 'DESC']],
            'Quotes' => ['sort' => ['Quotes.quote_date' => 'DESC']],
        ]);
    }
}

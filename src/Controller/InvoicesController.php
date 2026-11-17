<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\DocumentMailer;
use App\Model\Entity\Invoice;
use App\Model\Entity\Setting;
use App\Model\Table\EmailTemplatesTable;
use App\Service\DocumentPdf;
use App\Service\EmailShortcodes;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Throwable;

/**
 * @property \App\Model\Table\InvoicesTable $Invoices
 */
class InvoicesController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'invoices');
        $this->confineClients($event, ['view', 'pdf']);
    }

    public function index(): void
    {
        $invoices = $this->Invoices->find()->orderByDesc('invoice_date')->all();
        $settings = $this->fetchTable('Settings')->current();
        $this->set(compact('invoices', 'settings'));
    }

    public function view(?string $id = null): ?Response
    {
        $invoice = $this->loadFull($id);
        if ($this->clientDeniedFor($invoice->client_id)) {
            return $this->denyOtherClient();
        }
        $this->set('thisInvoice', $invoice);
        $this->set('settings', $this->fetchTable('Settings')->current());

        return null;
    }

    public function add(): ?Response
    {
        $invoice = $this->Invoices->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $invoice = $this->Invoices->patchEntity($invoice, $data, [
                'fields' => ['client_id', 'invoice_number', 'invoice_date'],
            ]);
            $client = $this->fetchTable('Clients')->find()->where(['id' => (int)($data['client_id'] ?? 0)])->first();
            $now = DateTime::now();
            $invoice->client_name = $client?->client_name;
            $invoice->invoice_total = 0;
            $invoice->status = 'Unpaid';
            $invoice->created = $invoice->last_modified = $now;
            $invoice->created_by = $invoice->last_mod_by = $this->currentUserName();

            if ($client === null && !$invoice->getError('client_id')) {
                $invoice->setError('client_id', ['Please select a client']);
            }
            if ($client !== null && $this->Invoices->save($invoice)) {
                $this->Flash->success('Invoice Successfully Added');

                return $this->redirect(['action' => 'edit', $invoice->id]);
            }
            $this->Flash->error('Unable To Add Invoice');
        } else {
            $invoice->invoice_date = Date::today();
            $invoice->invoice_number = $this->Invoices->nextNumber(Date::today());
        }

        $clients = $this->fetchTable('Clients')->find('list')->orderBy(['client_name' => 'ASC'])->toArray();
        // Last 4 digits; the form's JavaScript prefixes YYMMDD when the date changes.
        $nextSequence = substr($this->Invoices->nextNumber(Date::today()), -4);
        $this->set(compact('invoice', 'clients', 'nextSequence'));

        return null;
    }

    public function edit(?string $id = null): ?Response
    {
        $invoice = $this->loadFull($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $invoice = $this->Invoices->patchEntity($invoice, $this->request->getData(), [
                'fields' => ['invoice_number', 'invoice_date', 'public_notes', 'private_notes'],
            ]);
            $invoice->last_modified = DateTime::now();
            $invoice->last_mod_by = $this->currentUserName();
            if ($this->Invoices->save($invoice, ['associated' => []])) {
                $this->Flash->success('Your Invoice Has Been Updated');

                return $this->redirect(['action' => 'edit', $id]);
            }
            $this->Flash->error('Unable To Update Your Invoice');
        }

        $taxRates = $this->fetchTable('Taxrates')->rateOptions();
        $settings = $this->fetchTable('Settings')->current();
        $newItem = $this->fetchTable('Items')->newEmptyEntity();
        $newPayment = $this->fetchTable('Payments')->newEntity(['date' => Date::today()], ['validate' => false]);
        $this->set(compact('invoice', 'taxRates', 'settings', 'newItem', 'newPayment'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Invoices->delete($this->Invoices->get($id))) {
            $this->Flash->success('The Invoice Has Been Deleted');
        } else {
            $this->Flash->error('Unable To Delete Invoice');
        }

        return $this->redirect(['action' => 'index']);
    }

    /** Preview the email (template with shortcodes filled in) before sending. */
    public function email(?string $id = null): void
    {
        $invoice = $this->loadFull($id);
        $settings = $this->fetchTable('Settings')->current();
        $this->set('thisInvoice', $invoice);
        $this->set('settings', $settings);
        $this->set('message', $this->renderMessage($invoice, $settings));
        $this->set('to', $invoice->client->client_email);
        $this->set('from', $settings->company_email);
    }

    /**
     * Email the invoice PDF to the client. Recipient and sender come from the
     * client and company records, not the form (the legacy form let any user
     * post arbitrary to/from addresses).
     */
    public function send(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'put']);
        $invoice = $this->loadFull($id ?? (string)$this->request->getData('id'));
        $settings = $this->fetchTable('Settings')->current();
        $to = (string)$invoice->client->client_email;
        $from = (string)$settings->company_email;
        if ($to === '' || $from === '') {
            $this->Flash->error('Both the client email and your company email (Settings) are required to send invoices.');

            return $this->redirect(['action' => 'email', $invoice->id]);
        }
        $message = (string)($this->request->getData('message') ?: $this->renderMessage($invoice, $settings));

        try {
            (new DocumentMailer())->send('document', [
                $to,
                $from,
                'New Invoice (' . $invoice->invoice_number . ')',
                $message,
                'Invoice_' . $invoice->invoice_number . '.pdf',
                $this->renderPdf($invoice, $settings),
            ]);
            $this->Flash->success('Email Sent');
        } catch (Throwable $e) {
            $this->log('Invoice email failed: ' . $e->getMessage());
            $this->Flash->error('The email could not be sent. Please check the mail settings.');
        }

        return $this->redirect(['action' => 'view', $invoice->id]);
    }

    /** Download the invoice as a PDF (replaces the legacy public webroot/temp files). */
    public function pdf(?string $id = null): Response
    {
        $invoice = $this->loadFull($id);
        if ($this->clientDeniedFor($invoice->client_id)) {
            return $this->denyOtherClient();
        }
        $bytes = $this->renderPdf($invoice, $this->fetchTable('Settings')->current());

        return $this->response
            ->withType('pdf')
            ->withStringBody($bytes)
            ->withDownload('Invoice_' . $invoice->invoice_number . '.pdf');
    }

    private function loadFull(?string $id): Invoice
    {
        /** @var \App\Model\Entity\Invoice */
        return $this->Invoices->get($id, contain: ['Clients', 'Items', 'Payments']);
    }

    private function renderPdf(Invoice $invoice, Setting $settings): string
    {
        return DocumentPdf::render(
            'INVOICE',
            (string)$invoice->invoice_number,
            $invoice->invoice_date->format('Y-m-d'),
            $invoice->public_notes,
            $invoice->client,
            $invoice->items,
            $settings,
        );
    }

    private function renderMessage(Invoice $invoice, Setting $settings): string
    {
        $template = $this->fetchTable('EmailTemplates')->get(EmailTemplatesTable::CLIENT_INVOICE);

        return EmailShortcodes::render(
            (string)$template->content,
            'INVOICE',
            (string)$invoice->invoice_number,
            $invoice->invoice_date->format('Y-m-d'),
            number_format((float)$invoice->invoice_total, 2, '.', ','),
            $invoice->client,
            $settings,
            $this->currentUserName(),
        );
    }
}

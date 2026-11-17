<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\DocumentMailer;
use App\Model\Entity\Quote;
use App\Model\Entity\Setting;
use App\Model\Table\EmailTemplatesTable;
use App\Model\Table\QuotesTable;
use App\Service\DocumentPdf;
use App\Service\EmailShortcodes;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Throwable;

/**
 * @property \App\Model\Table\QuotesTable $Quotes
 */
class QuotesController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'quotes');
        $this->confineClients($event, ['view', 'pdf']);
    }

    public function index(): void
    {
        $quotes = $this->Quotes->find()->contain(['Clients'])->orderByDesc('quote_date')->all();
        $settings = $this->fetchTable('Settings')->current();
        $this->set(compact('quotes', 'settings'));
    }

    public function view(?string $id = null): ?Response
    {
        $quote = $this->loadFull($id);
        if ($this->clientDeniedFor($quote->client_id)) {
            return $this->denyOtherClient();
        }
        $this->set('thisQuote', $quote);
        $this->set('settings', $this->fetchTable('Settings')->current());

        return null;
    }

    public function add(): ?Response
    {
        $quote = $this->Quotes->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $quote = $this->Quotes->patchEntity($quote, $data, [
                'fields' => ['client_id', 'quote_number', 'quote_date'],
            ]);
            // Expiry = quote date + "Days To Expire" (default 30), as in the legacy form.
            $days = max(0, (int)($data['expire_days'] ?? 30));
            if ($quote->quote_date instanceof Date) {
                $quote->expires = $quote->quote_date->addDays($days);
            }
            $client = $this->fetchTable('Clients')->find()->where(['id' => (int)($data['client_id'] ?? 0)])->first();
            $now = DateTime::now();
            $quote->quote_total = 0;
            $quote->status = 'Pending';
            $quote->created = $quote->modified = $now;
            $quote->created_by = $quote->last_mod_by = $this->currentUserName();

            if ($client === null && !$quote->getError('client_id')) {
                $quote->setError('client_id', ['Please select a client']);
            }
            if ($client !== null && $this->Quotes->save($quote)) {
                $this->Flash->success('Quote Successfully Added');

                return $this->redirect(['action' => 'edit', $quote->id]);
            }
            $this->Flash->error('Unable To Add Quote');
        } else {
            $quote->quote_date = Date::today();
            $quote->quote_number = $this->Quotes->nextNumber(Date::today());
        }

        $clients = $this->fetchTable('Clients')->find('list')->orderBy(['client_name' => 'ASC'])->toArray();
        // Last 4 digits; the form's JavaScript prefixes YYMMDD when the date changes.
        $nextSequence = substr($this->Quotes->nextNumber(Date::today()), -4);
        $this->set(compact('quote', 'clients', 'nextSequence'));

        return null;
    }

    public function edit(?string $id = null): ?Response
    {
        $quote = $this->loadFull($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $quote = $this->Quotes->patchEntity($quote, $this->request->getData(), [
                'fields' => ['quote_number', 'quote_date', 'expires', 'status', 'public_notes', 'private_notes'],
            ]);
            $quote->modified = DateTime::now();
            $quote->last_mod_by = $this->currentUserName();
            if ($this->Quotes->save($quote, ['associated' => []])) {
                $this->Flash->success('Your Quote Has Been Updated');

                return $this->redirect(['action' => 'edit', $id]);
            }
            $this->Flash->error('Unable To Update Your Quote');
        }

        $taxRates = $this->fetchTable('Taxrates')->rateOptions();
        $settings = $this->fetchTable('Settings')->current();
        $newItem = $this->fetchTable('QuoteItems')->newEmptyEntity();
        $statuses = array_combine(QuotesTable::STATUSES, QuotesTable::STATUSES);
        $this->set(compact('quote', 'taxRates', 'settings', 'newItem', 'statuses'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Quotes->delete($this->Quotes->get($id))) {
            $this->Flash->success('The Quote Has Been Deleted');
        } else {
            $this->Flash->error('Unable To Delete Quote');
        }

        return $this->redirect(['action' => 'index']);
    }

    /** Preview the email (template with shortcodes filled in) before sending. */
    public function email(?string $id = null): void
    {
        $quote = $this->loadFull($id);
        $settings = $this->fetchTable('Settings')->current();
        $this->set('thisQuote', $quote);
        $this->set('settings', $settings);
        $this->set('message', $this->renderMessage($quote, $settings));
        $this->set('to', $quote->client->client_email);
        $this->set('from', $settings->company_email);
    }

    /**
     * Email the quote PDF to the client. Recipient and sender come from the
     * client and company records, not the form (the legacy form let any user
     * post arbitrary to/from addresses).
     */
    public function send(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'put']);
        $quote = $this->loadFull($id ?? (string)$this->request->getData('id'));
        $settings = $this->fetchTable('Settings')->current();
        $to = (string)$quote->client->client_email;
        $from = (string)$settings->company_email;
        if ($to === '' || $from === '') {
            $this->Flash->error('Both the client email and your company email (Settings) are required to send quotes.');

            return $this->redirect(['action' => 'email', $quote->id]);
        }
        $message = (string)($this->request->getData('message') ?: $this->renderMessage($quote, $settings));

        try {
            (new DocumentMailer())->send('document', [
                $to,
                $from,
                'New Quote (' . $quote->quote_number . ')',
                $message,
                'Quote_' . $quote->quote_number . '.pdf',
                $this->renderPdf($quote, $settings),
            ]);
            $this->Flash->success('Email Sent');
        } catch (Throwable $e) {
            $this->log('Quote email failed: ' . $e->getMessage());
            $this->Flash->error('The email could not be sent. Please check the mail settings.');
        }

        return $this->redirect(['action' => 'view', $quote->id]);
    }

    /** Download the quote as a PDF (replaces the legacy public webroot/temp files). */
    public function pdf(?string $id = null): Response
    {
        $quote = $this->loadFull($id);
        if ($this->clientDeniedFor($quote->client_id)) {
            return $this->denyOtherClient();
        }
        $bytes = $this->renderPdf($quote, $this->fetchTable('Settings')->current());

        return $this->response
            ->withType('pdf')
            ->withStringBody($bytes)
            ->withDownload('Quote_' . $quote->quote_number . '.pdf');
    }

    private function loadFull(?string $id): Quote
    {
        /** @var \App\Model\Entity\Quote */
        return $this->Quotes->get($id, contain: ['Clients', 'QuoteItems']);
    }

    private function renderPdf(Quote $quote, Setting $settings): string
    {
        return DocumentPdf::render(
            'QUOTE',
            (string)$quote->quote_number,
            $quote->quote_date->format('Y-m-d'),
            $quote->public_notes,
            $quote->client,
            $quote->quote_items,
            $settings,
        );
    }

    private function renderMessage(Quote $quote, Setting $settings): string
    {
        $template = $this->fetchTable('EmailTemplates')->get(EmailTemplatesTable::CLIENT_QUOTE);

        return EmailShortcodes::render(
            (string)$template->content,
            'QUOTE',
            (string)$quote->quote_number,
            $quote->quote_date->format('Y-m-d'),
            number_format((float)$quote->quote_total, 2, '.', ','),
            $quote->client,
            $settings,
            $this->currentUserName(),
        );
    }
}

<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Payments against an invoice. Saving or deleting one recalculates the
 * invoice status (see PaymentsTable).
 *
 * @property \App\Model\Table\PaymentsTable $Payments
 */
class PaymentsController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->confineClients($event);
    }

    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $payment = $this->Payments->newEntity($this->request->getData(), [
            'fields' => ['invoice_id', 'date', 'method', 'amount'],
        ]);
        // Client comes from the invoice rather than a hidden form field.
        $invoice = $this->fetchTable('Invoices')->find()->where(['id' => (int)$payment->invoice_id])->first();
        $payment->client_id = $invoice?->client_id;

        if ($invoice !== null && $this->Payments->save($payment)) {
            $this->Flash->success('Payment Successfully Added');
        } else {
            $this->Flash->error('Unable To Add Payment - Please Complete All Required Payment Fields');
        }

        return $this->redirect(['controller' => 'Invoices', 'action' => 'edit', $payment->invoice_id]);
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $payment = $this->Payments->get($id);
        if ($this->Payments->delete($payment)) {
            $this->Flash->success('The Payment Has Been Deleted');
        }

        return $this->redirect(['controller' => 'Invoices', 'action' => 'edit', $payment->invoice_id]);
    }
}

<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Invoice line items. Saving or deleting an item recalculates the invoice
 * total and status (see LineItemsTable).
 *
 * @property \App\Model\Table\ItemsTable $Items
 */
class ItemsController extends AppController
{
    protected string $parentController = 'Invoices';

    protected string $parentKey = 'invoice_id';

    protected string $tableName = 'Items';

    private const FIELDS = ['title', 'details', 'quantity', 'unit_price', 'tax_rate'];

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->confineClients($event);
    }

    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $table = $this->fetchTable($this->tableName);
        $item = $table->newEntity($this->request->getData(), [
            'fields' => [...self::FIELDS, $this->parentKey],
        ]);
        if (!$table->save($item)) {
            $this->Flash->error('Unable To Add Item - Please Complete All Required Item Fields');
        }

        return $this->redirect(['controller' => $this->parentController, 'action' => 'edit', $item->get($this->parentKey)]);
    }

    public function edit(?string $id = null): ?Response
    {
        $table = $this->fetchTable($this->tableName);
        $item = $table->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $item = $table->patchEntity($item, $this->request->getData(), ['fields' => self::FIELDS]);
            if ($table->save($item)) {
                $this->Flash->success('Your Item Has Been Updated');

                return $this->redirect(['controller' => $this->parentController, 'action' => 'edit', $item->get($this->parentKey)]);
            }
            $this->Flash->error('Unable To Update Your Item');
        }
        $this->set('item', $item);
        $this->set('taxRates', $this->fetchTable('Taxrates')->rateOptions());

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $table = $this->fetchTable($this->tableName);
        $item = $table->get($id);
        $table->delete($item);

        return $this->redirect(['controller' => $this->parentController, 'action' => 'edit', $item->get($this->parentKey)]);
    }
}

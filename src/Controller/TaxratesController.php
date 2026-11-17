<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * @property \App\Model\Table\TaxratesTable $Taxrates
 */
class TaxratesController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'settings');
        $this->requireAdmin($event, 'You Are Not Authorised To Access Settings');
        $this->viewBuilder()->setLayout('settings');
    }

    public function index(): void
    {
        $this->set('taxRates', $this->Taxrates->find()->orderBy(['rate' => 'ASC'])->all());
        $this->set('taxRate', $this->Taxrates->newEmptyEntity());
    }

    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $rate = $this->Taxrates->newEntity($this->request->getData(), ['fields' => ['name', 'rate']]);
        if ($this->Taxrates->save($rate)) {
            $this->Flash->success('Tax Rate successfully added');
        } else {
            $this->Flash->error('Unable to add tax rate');
        }

        return $this->redirect(['action' => 'index']);
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Taxrates->delete($this->Taxrates->get($id))) {
            $this->Flash->success('The tax rate has been deleted.');
        }

        return $this->redirect(['action' => 'index']);
    }
}

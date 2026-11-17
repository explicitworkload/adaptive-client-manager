<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\EmailTemplatesTable;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * @property \App\Model\Table\EmailTemplatesTable $EmailTemplates
 */
class EmailTemplatesController extends AppController
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
        $this->set('emailTemplates', $this->EmailTemplates->find()->all());
    }

    public function edit(?string $id = null): ?Response
    {
        return $this->editTemplate((int)$id);
    }

    public function editClientInvoice(): ?Response
    {
        return $this->editTemplate(EmailTemplatesTable::CLIENT_INVOICE);
    }

    public function editClientQuote(): ?Response
    {
        return $this->editTemplate(EmailTemplatesTable::CLIENT_QUOTE);
    }

    private function editTemplate(int $id): ?Response
    {
        $template = $this->EmailTemplates->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $template = $this->EmailTemplates->patchEntity($template, $this->request->getData(), ['fields' => ['content']]);
            if ($this->EmailTemplates->save($template)) {
                $this->Flash->success('Your Email Template Has Been Updated');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Unable To Update Your Email Template');
        }
        $this->set('template', $template);

        return null;
    }
}

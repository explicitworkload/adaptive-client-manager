<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\Date;

class DashboardController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'Dashboard');
        $this->confineClients($event, [], false);
    }

    public function index(): void
    {
        $quotesTable = $this->fetchTable('Quotes');
        $quotesTable->expireOverdue();

        $today = Date::today();

        // Invoices dated from 1 January of the previous year onwards.
        $invoices = $this->fetchTable('Invoices')->find()
            ->where(['invoice_date >=' => new Date(($today->year - 1) . '-01-01')])
            ->orderByDesc('invoice_date')
            ->all();

        // Open quotes expiring within the next 7 days.
        $quotes = $quotesTable->find()
            ->contain(['Clients'])
            ->where([
                'Quotes.status NOT IN' => ['Accepted', 'Rejected'],
                'Quotes.expires >' => $today,
                'Quotes.expires <=' => $today->addDays(7),
            ])
            ->orderByDesc('Quotes.expires')
            ->all();

        $settings = $this->fetchTable('Settings')->current();
        $this->set(compact('invoices', 'quotes', 'settings'));
    }
}

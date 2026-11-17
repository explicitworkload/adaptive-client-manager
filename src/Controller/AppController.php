<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ThemeCss;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Base controller.
 *
 * Access model (unchanged from the legacy app):
 *  - admin:   everything, including Settings, Users, Tax Rates, Email Templates.
 *  - manager: everything except the Settings area.
 *  - client:  only their own client area and their own invoices/quotes. A client
 *             user is linked to a client record by name (users.first_name ==
 *             clients.client_name).
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        // ErrorController extends this class without loading Authentication.
        $identity = $this->components()->has('Authentication') ? $this->Authentication->getIdentity() : null;
        $this->set('currentUser', $identity?->getOriginalData());

        // Rebuild the theme stylesheet after an update changes its template.
        if (!ThemeCss::isCurrent()) {
            try {
                $color = $this->fetchTable('Settings')->find()->select(['primary_color'])->first()?->primary_color;
                ThemeCss::build((string)($color ?: '#339966'));
            } catch (\Throwable $e) {
                $this->log('Could not rebuild theme CSS: ' . $e->getMessage(), 'warning');
            }
        }
    }

    protected function role(): ?string
    {
        return $this->Authentication->getIdentity()?->get('role');
    }

    protected function isClient(): bool
    {
        return $this->role() === 'client';
    }

    /** "First Last" of the signed-in user, recorded in created_by / last_mod_by. */
    protected function currentUserName(): string
    {
        $identity = $this->Authentication->getIdentity();

        $name = trim($identity?->get('first_name') . ' ' . $identity?->get('last_name'));

        // created_by / last_mod_by are varchar(66) in the legacy database.
        return mb_substr($name, 0, 66);
    }

    /** Id of the client record a client user belongs to. */
    protected function currentClientId(): ?int
    {
        return $this->fetchTable('Clients')
            ->findIdForClientUser($this->Authentication->getIdentity()?->get('first_name'));
    }

    /** True when a client user is looking at a record belonging to another client. */
    protected function clientDeniedFor(?int $clientId): bool
    {
        return $this->isClient() && $clientId !== $this->currentClientId();
    }

    /**
     * For use in beforeFilter(): send client users to their own area unless the
     * current action is one of $allowedActions.
     *
     * @param list<string> $allowedActions
     */
    protected function confineClients(EventInterface $event, array $allowedActions = [], bool $flash = true): void
    {
        if (!$this->isClient() || in_array($this->request->getParam('action'), $allowedActions, true)) {
            return;
        }
        $clientId = $this->currentClientId();
        if ($clientId === null) {
            // A client login whose client record was renamed or deleted.
            $this->Flash->error('Your account is not linked to a client. Please contact us.');
            $this->Authentication->logout();
            $this->stop($event, $this->redirect(['controller' => 'Users', 'action' => 'login']));

            return;
        }
        if ($flash) {
            $this->Flash->error('Clients Are Only Authorised To View Their Own Area');
        }
        $this->stop($event, $this->redirect(['controller' => 'Clients', 'action' => 'clientArea', $clientId]));
    }

    /**
     * For use in beforeFilter(): only admins may continue.
     *
     * @param list<string> $openActions actions anyone signed in may use
     */
    protected function requireAdmin(EventInterface $event, string $message, array $openActions = []): void
    {
        if ($this->role() === 'admin' || in_array($this->request->getParam('action'), $openActions, true)) {
            return;
        }
        $this->Flash->error($message);
        $this->stop($event, $this->redirect(['controller' => 'Dashboard', 'action' => 'index']));
    }

    /** Redirect a client away from a record that isn't theirs. */
    protected function denyOtherClient(): Response
    {
        $this->Flash->error('Clients Are Only Authorised To View Their Own Area');

        return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
    }

    private function stop(EventInterface $event, ?Response $response): void
    {
        $event->setResult($response);
        $event->stopPropagation();
    }
}

<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->set('title', 'Settings');
        $this->Authentication->addUnauthenticatedActions(['login', 'setup']);
        if ($this->Authentication->getIdentity() !== null) {
            $this->requireAdmin($event, 'You Are Not Authorised To Access Settings', ['login', 'logout']);
        }
        $this->viewBuilder()->setLayout('settings');
    }

    public function index(): void
    {
        $this->set('users', $this->Users->find()->orderBy(['role' => 'ASC', 'username' => 'ASC'])->all());
    }

    public function view(?string $id = null): void
    {
        $this->set('thisUser', $this->Users->get($id));
    }

    /** Creates either a staff user (admin/manager) or a client login. */
    public function add(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (($data['role'] ?? null) === 'client') {
                // A client login is tied to its client record by name.
                $data['first_name'] = $data['client_name'] ?? '';
                $data['last_name'] = 'Client';
            }
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success('User Successfully Added');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Unable To Add User');
        }
        $staffRoles = ['manager' => 'Manager', 'admin' => 'Admin'];
        $clientNames = $this->fetchTable('Clients')->find('list', keyField: 'client_name', valueField: 'client_name')
            ->orderBy(['client_name' => 'ASC'])->toArray();
        $this->set(compact('user', 'staffRoles', 'clientNames'));

        return null;
    }

    public function edit(?string $id = null): ?Response
    {
        $user = $this->Users->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            if (($data['password'] ?? '') === '') {
                unset($data['password']); // blank = keep the current password
            }
            if ($user->role === 'client') {
                // The client link (first_name) and role can't be changed here.
                unset($data['first_name'], $data['role']);
            }
            if ($user->id === $this->Authentication->getIdentity()?->getIdentifier()) {
                // Don't let admins demote themselves out of the settings area.
                unset($data['role']);
            }
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success('The User Account Has Been Updated');

                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error('Unable To Update User Account');
        }
        $user->unset('password');
        $roles = $user->role === 'client' ? ['client' => 'Client'] : ['manager' => 'Manager', 'admin' => 'Admin'];
        $this->set(compact('user', 'roles'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        if ((int)$id === (int)$this->Authentication->getIdentity()?->getIdentifier()) {
            $this->Flash->error('You Can Not Delete Your Own Account');
        } elseif ($this->Users->delete($this->Users->get($id))) {
            $this->Flash->success('The User Has Been Deleted');
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * First-run page: creates the first admin login on a fresh install.
     *
     * Only available while the users table is empty (404 afterwards), so it
     * can't be used to add accounts once the site is set up.
     */
    public function setup(): ?Response
    {
        if (!$this->Users->find()->all()->isEmpty()) {
            throw new NotFoundException();
        }
        $this->viewBuilder()->setLayout('login');
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['role'] = 'admin'; // the first account is always an admin
            $user = $this->Users->patchEntity($user, $data, [
                'fields' => ['username', 'password', 'first_name', 'last_name', 'email', 'role'],
            ]);
            if ($this->Users->save($user)) {
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Your admin account has been created. Next, fill in your company details.');

                return $this->redirect(['controller' => 'Settings', 'action' => 'editConfig', 1]);
            }
            $this->Flash->error('Unable To Create The Admin Account');
        }
        $user->unset('password');
        $this->set(compact('user'));

        return null;
    }

    public function login(): ?Response
    {
        // Fresh install: there is no one to log in as yet.
        if ($this->Users->find()->all()->isEmpty()) {
            return $this->redirect(['action' => 'setup']);
        }
        $this->viewBuilder()->setLayout('login');
        $result = $this->Authentication->getResult();

        if ($result !== null && $result->isValid()) {
            if ($this->request->is('post')) {
                $this->upgradeLegacyPassword();
            }
            $target = $this->Authentication->getLoginRedirect() ?? ['controller' => 'Dashboard', 'action' => 'index'];

            return $this->redirect($target);
        }
        if ($this->request->is('post')) {
            $this->Flash->error('Invalid Username/Password');
        }

        return null;
    }

    public function logout(): Response
    {
        $this->Authentication->logout();

        return $this->redirect(['action' => 'login']);
    }

    /**
     * Users from the legacy app have sha1 password hashes. After a successful
     * login, store a bcrypt hash of the password they just typed instead.
     */
    private function upgradeLegacyPassword(): void
    {
        $authenticator = $this->Authentication->getAuthenticationService()->getAuthenticationProvider();
        if ($authenticator === null || !method_exists($authenticator, 'getIdentifier')) {
            return;
        }
        $identifier = $authenticator->getIdentifier();
        if (!method_exists($identifier, 'needsPasswordRehash') || !$identifier->needsPasswordRehash()) {
            return;
        }
        // A legacy users.password column may be too short for bcrypt (60 chars).
        // Re-hashing into it would truncate the hash and lock the user out, so keep
        // the legacy hash until the column is widened (see config/schema/upgrade_existing_database.sql).
        if (!$this->Users->canStoreModernHashes()) {
            $this->log('users.password is too short for bcrypt; skipping password upgrade.', 'warning');

            return;
        }
        $user = $this->Users->get($this->Authentication->getIdentity()->getIdentifier());
        $user->password = (string)$this->request->getData('password');
        $this->Users->save($user, ['validate' => false]);
    }
}

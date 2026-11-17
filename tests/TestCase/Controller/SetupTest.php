<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * First-run page that creates the first admin on a fresh install.
 */
class SetupTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Users', 'app.Settings', 'app.Clients'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
    }

    private function emptyUsers(): void
    {
        $this->fetchTable('Users')->deleteAll([]);
    }

    public function testLoginRedirectsToSetupOnFreshInstall(): void
    {
        $this->emptyUsers();
        $this->get('/users/login');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'setup']);
    }

    public function testSetupCreatesAdminAndSignsIn(): void
    {
        $this->emptyUsers();
        $this->get('/users/setup');
        $this->assertResponseOk();

        $this->post('/users/setup', [
            'username' => 'boss', 'password' => 'secret123', 'first_name' => 'Big',
            'last_name' => 'Boss', 'email' => 'boss@example.com', 'role' => 'client',
        ]);
        $this->assertRedirect(['controller' => 'Settings', 'action' => 'editConfig', 1]);

        $user = $this->fetchTable('Users')->find()->where(['username' => 'boss'])->firstOrFail();
        $this->assertSame('admin', $user->role, 'the first account is always an admin');
        $this->assertTrue(password_verify('secret123', $user->password));
    }

    public function testSetupIsGoneOnceAUserExists(): void
    {
        $this->get('/users/setup');
        $this->assertResponseCode(404);

        $this->post('/users/setup', [
            'username' => 'intruder', 'password' => 'secret123', 'first_name' => 'X',
            'last_name' => 'Y', 'email' => 'x@example.com',
        ]);
        $this->assertResponseCode(404);
        $this->assertFalse($this->fetchTable('Users')->exists(['username' => 'intruder']));
    }
}

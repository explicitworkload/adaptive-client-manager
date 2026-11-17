<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Authentication, legacy password migration and the role rules carried over
 * from the CakePHP 2 app.
 */
class AccessControlTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users', 'app.Clients', 'app.Invoices', 'app.Items', 'app.Payments',
        'app.Quotes', 'app.QuoteItems', 'app.Settings', 'app.EmailTemplates', 'app.Taxrates',
    ];

    private const ADMIN = 1;
    private const MANAGER = 2;
    private const CLIENT = 3; // client user for client 1 (Globex)

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableRetainFlashMessages();
    }

    private function loginAs(int $userId): void
    {
        $this->session(['Auth' => $userId]);
    }

    public function testUnauthenticatedUsersAreSentToLogin(): void
    {
        $this->get('/invoices');
        $this->assertRedirectContains('/users/login?redirect=%2Finvoices');
    }

    public function testLegacySha1PasswordLogsInAndIsUpgradedToBcrypt(): void
    {
        $this->post('/users/login', ['username' => 'legacy', 'password' => 'oldpass1']);
        $this->assertRedirect('/');

        $hash = $this->fetchTable('Users')->get(4)->password;
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify('oldpass1', $hash));
    }

    public function testWrongPasswordIsRejected(): void
    {
        $this->post('/users/login', ['username' => 'legacy', 'password' => 'nope']);
        $this->assertResponseOk();
        $this->assertFlashMessage('Invalid Username/Password');
        $this->assertSession(null, 'Auth');
    }

    public function testLegacyUnderscoredUrlsStillRoute(): void
    {
        $this->loginAs(self::MANAGER);
        $this->get('/settings/edit_config/1'); // routed, then refused for non-admins
        $this->assertRedirect(['controller' => 'Dashboard', 'action' => 'index']);
        $this->assertFlashMessage('You Are Not Authorised To Access Settings');
    }

    public function testClientIsConfinedToOwnArea(): void
    {
        $this->loginAs(self::CLIENT);

        $this->get('/invoices');
        $this->assertRedirect(['controller' => 'Clients', 'action' => 'clientArea', 1]);

        $this->get('/dashboard');
        $this->assertRedirect(['controller' => 'Clients', 'action' => 'clientArea', 1]);

        $this->get('/clients/client_area/2');
        $this->assertRedirect(['controller' => 'Dashboard', 'action' => 'index']);
    }

    public function testClientCanOnlyDownloadOwnInvoicePdf(): void
    {
        $this->loginAs(self::CLIENT);

        $this->get('/invoices/pdf/1');
        $this->assertResponseOk();
        $this->assertContentType('application/pdf');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());

        $this->get('/invoices/pdf/2');
        $this->assertRedirect(['controller' => 'Dashboard', 'action' => 'index']);
    }

    public function testClientStatementIgnoresPostedClientId(): void
    {
        $this->loginAs(self::CLIENT);
        $this->post('/reports/client-statement', ['client_name_list' => 2, 'print_table' => 'Yes']);
        $this->assertSame(1, $this->viewVariable('client')->id);
    }

    public function testDeletesRequirePost(): void
    {
        $this->loginAs(self::ADMIN);
        $this->get('/invoices/delete/1');
        $this->assertResponseCode(405);
        $this->assertTrue($this->fetchTable('Invoices')->exists(['id' => 1]));
    }

    public function testDeletingAnItemReturnsToItsInvoice(): void
    {
        // Legacy ItemsController::delete redirected using an undefined variable.
        $this->loginAs(self::ADMIN);
        $this->post('/items/delete/1');
        $this->assertRedirect(['controller' => 'Invoices', 'action' => 'edit', 1]);
        $this->assertFalse($this->fetchTable('Items')->exists(['id' => 1]));
    }

    public function testPaymentTakesClientFromInvoiceNotForm(): void
    {
        $this->loginAs(self::ADMIN);
        $this->post('/payments/add', [
            'invoice_id' => 1, 'client_id' => 2, 'date' => '2026-11-17', 'method' => 'Cash', 'amount' => 10,
        ]);
        $this->assertRedirect(['controller' => 'Invoices', 'action' => 'edit', 1]);
        $payment = $this->fetchTable('Payments')->find()->firstOrFail();
        $this->assertSame(1, $payment->client_id);
    }

    public function testUserCannotDeleteOwnAccount(): void
    {
        $this->loginAs(self::ADMIN);
        $this->post('/users/delete/1');
        $this->assertFlashMessage('You Can Not Delete Your Own Account');
        $this->assertTrue($this->fetchTable('Users')->exists(['id' => 1]));
    }

    public function testThemeColourMustBeHex(): void
    {
        // The colour is written into a CSS file, so arbitrary input must be refused.
        $this->loginAs(self::ADMIN);
        $this->post('/settings/edit-theme/1', ['primary_color' => 'red;}body{display:none']);
        $this->assertNoRedirect();
        $this->assertSame('#339966', $this->fetchTable('Settings')->get(1)->primary_color);
    }

    public function testRenamingAClientKeepsItsLoginWorking(): void
    {
        $this->loginAs(self::ADMIN);
        $this->post('/clients/edit/1', ['client_name' => 'Globex Corp', 'client_address_line_1' => '5 Volcano Rd']);
        $this->assertRedirect(['controller' => 'Clients', 'action' => 'view', 1]);

        $this->assertSame('Globex Corp', $this->fetchTable('Users')->get(self::CLIENT)->first_name);
        $this->assertSame('Globex Corp', $this->fetchTable('Invoices')->get(1)->client_name);

        $this->loginAs(self::CLIENT);
        $this->get('/invoices');
        $this->assertRedirect(['controller' => 'Clients', 'action' => 'clientArea', 1]);
    }

    public function testItemForMissingInvoiceIsRejected(): void
    {
        $this->loginAs(self::ADMIN);
        $this->post('/items/add', ['invoice_id' => 999, 'title' => 'X', 'quantity' => 1, 'unit_price' => 1, 'tax_rate' => 0]);
        $this->assertFlashMessage('Unable To Add Item - Please Complete All Required Item Fields');
        $this->assertFalse($this->fetchTable('Items')->exists(['invoice_id' => 999]));
    }

    public function testAdminCannotDemoteThemselves(): void
    {
        $this->loginAs(self::ADMIN);
        $this->post('/users/edit/1', ['username' => 'admin', 'first_name' => 'Ada', 'last_name' => 'Min',
            'email' => 'a@x.test', 'role' => 'manager', 'password' => '']);
        $this->assertSame('admin', $this->fetchTable('Users')->get(1)->role);
    }

    public function testHeaderShowsVersionFromVersionFile(): void
    {
        $this->loginAs(self::ADMIN);
        $this->get('/dashboard');
        $this->assertResponseContains('| V' . trim((string)file_get_contents(ROOT . DS . 'VERSION')));
    }
}

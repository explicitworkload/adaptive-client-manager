<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

class UsersTableTest extends TestCase
{
    protected array $fixtures = ['app.Users'];

    public function testChecksLivePasswordColumnNotCachedSchema(): void
    {
        $users = $this->fetchTable('Users');
        $this->assertTrue($users->canStoreModernHashes());

        // A stale cached schema must not matter: the live column is checked.
        $users->getSchema()->addColumn('password', ['type' => 'string', 'length' => 40]);
        $this->assertTrue($users->canStoreModernHashes());
    }
}

<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public array $records = [
        // Password hashes: 'secret123' (bcrypt) and, for 'legacy', sha1('deadPIXEL' . 'oldpass1') as the CakePHP 2 app stored them.
        ['id' => 1, 'username' => 'admin', 'password' => '$2y$12$NR44v9Z1HFWs0TGaWK5roeDxThXBmHPi1zphShkffrkbbl6JX4Yui', 'first_name' => 'Ada', 'last_name' => 'Min', 'email' => 'a@x.test', 'role' => 'admin'],
        ['id' => 2, 'username' => 'manny', 'password' => '$2y$12$NR44v9Z1HFWs0TGaWK5roeDxThXBmHPi1zphShkffrkbbl6JX4Yui', 'first_name' => 'Manny', 'last_name' => 'Ager', 'email' => 'm@x.test', 'role' => 'manager'],
        ['id' => 3, 'username' => 'globex', 'password' => '$2y$12$NR44v9Z1HFWs0TGaWK5roeDxThXBmHPi1zphShkffrkbbl6JX4Yui', 'first_name' => 'Globex', 'last_name' => 'Client', 'email' => 'g@x.test', 'role' => 'client'],
        ['id' => 4, 'username' => 'legacy', 'password' => 'e0538bbee82708d6c27bd44aa79981197fb0ebe2', 'first_name' => 'Lee', 'last_name' => 'Gacy', 'email' => 'l@x.test', 'role' => 'admin'],
    ];
}

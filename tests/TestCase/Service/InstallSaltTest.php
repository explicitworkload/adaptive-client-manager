<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\InstallSalt;
use Cake\TestSuite\TestCase;

class InstallSaltTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = TMP . 'tests' . DS . 'salt_' . bin2hex(random_bytes(4)) . DS;
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $file) {
            chmod($file, 0644);
            unlink($file);
        }
        rmdir($this->dir);
        parent::tearDown();
    }

    private function appLocal(string $salt = InstallSalt::PLACEHOLDER): string
    {
        $file = $this->dir . 'app_local.php';
        file_put_contents($file, "<?php\nreturn ['Security' => ['salt' => '" . $salt . "']];\n");

        return $file;
    }

    public function testConfiguredSaltIsKept(): void
    {
        $file = $this->appLocal('mysalt');
        $this->assertSame('mysalt', InstallSalt::resolve('mysalt', $file, $this->dir . 'fallback'));
        $this->assertStringContainsString("'mysalt'", (string)file_get_contents($file));
    }

    public function testPlaceholderIsReplacedInAppLocal(): void
    {
        $file = $this->appLocal();
        $salt = InstallSalt::resolve(InstallSalt::PLACEHOLDER, $file, $this->dir . 'fallback');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $salt);
        $this->assertStringContainsString("'" . $salt . "'", (string)file_get_contents($file));
        $this->assertStringNotContainsString(InstallSalt::PLACEHOLDER, (string)file_get_contents($file));
        $this->assertSame($salt, (require $file)['Security']['salt']);
        $this->assertFileDoesNotExist($this->dir . 'fallback');
    }

    public function testConcurrentFirstRequestGetsTheSameSalt(): void
    {
        // A second request that still saw the placeholder (e.g. cached config)
        // must reuse the salt already written, not create another.
        $file = $this->appLocal();
        $first = InstallSalt::resolve(InstallSalt::PLACEHOLDER, $file, $this->dir . 'fallback');
        $second = InstallSalt::resolve(InstallSalt::PLACEHOLDER, $file, $this->dir . 'fallback');
        $this->assertSame($first, $second);
    }

    public function testFallsBackToTmpWhenAppLocalIsReadOnly(): void
    {
        $file = $this->appLocal();
        chmod($file, 0444);
        $fallback = $this->dir . 'fallback';

        $salt = @InstallSalt::resolve(InstallSalt::PLACEHOLDER, $file, $fallback);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $salt);
        $this->assertSame($salt, trim((string)file_get_contents($fallback)));
        $this->assertSame($salt, InstallSalt::resolve(InstallSalt::PLACEHOLDER, $file, $fallback), 'reused on later requests');
        $this->assertStringContainsString(InstallSalt::PLACEHOLDER, (string)file_get_contents($file));
    }
}

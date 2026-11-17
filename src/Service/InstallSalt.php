<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Gives every installation its own Security.salt without a build step.
 *
 * The release package ships config/app_local.php with the placeholder
 * '__SALT__'. On the first request the placeholder is replaced with a random
 * salt, written back into app_local.php. If that file isn't writable, the salt
 * is kept in a fallback file under tmp/ instead.
 */
final class InstallSalt
{
    public const PLACEHOLDER = '__SALT__';

    public static function resolve(?string $configured, string $appLocalFile, string $fallbackFile): string
    {
        if ($configured !== null && $configured !== '' && $configured !== self::PLACEHOLDER) {
            return $configured;
        }

        $quoted = "'" . self::PLACEHOLDER . "'";
        if (is_file($appLocalFile) && is_writable($appLocalFile)) {
            $salt = self::writeIntoAppLocal($appLocalFile, $quoted);
            if ($salt !== null) {
                return $salt;
            }
        }

        return self::fallback($fallbackFile);
    }

    /**
     * Replace the placeholder under an exclusive lock. If another request got
     * there first, return the salt it wrote instead of generating a new one.
     */
    private static function writeIntoAppLocal(string $file, string $quoted): ?string
    {
        $handle = fopen($file, 'c+');
        if ($handle === false) {
            return null;
        }
        try {
            flock($handle, LOCK_EX);
            $content = (string)stream_get_contents($handle);
            if (str_contains($content, $quoted)) {
                $salt = bin2hex(random_bytes(32));
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, str_replace($quoted, "'" . $salt . "'", $content));
                fflush($handle);
            } elseif (preg_match("/'salt'\\s*=>\\s*'([0-9a-f]{64})'/", $content, $m)) {
                $salt = $m[1];
            } else {
                return null;
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }

        return $salt;
    }

    private static function fallback(string $file): string
    {
        if (is_file($file)) {
            $stored = trim((string)file_get_contents($file));
            if (preg_match('/^[0-9a-f]{64}$/', $stored)) {
                return $stored;
            }
        }
        $salt = bin2hex(random_bytes(32));
        // 'x' fails if another request created the file first; use theirs then.
        $handle = @fopen($file, 'x');
        if ($handle === false) {
            $stored = is_file($file) ? trim((string)file_get_contents($file)) : '';

            return preg_match('/^[0-9a-f]{64}$/', $stored) ? $stored : $salt;
        }
        fwrite($handle, $salt);
        fclose($handle);
        trigger_error(
            'config/app_local.php is not writable, so the security salt was stored in ' . $file
            . '. Copy it into app_local.php (\'salt\' => \'...\') so clearing tmp/ does not change it.',
            E_USER_WARNING,
        );

        return $salt;
    }
}

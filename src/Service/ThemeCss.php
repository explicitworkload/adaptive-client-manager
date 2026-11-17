<?php
declare(strict_types=1);

namespace App\Service;

/**
 * webroot/css/dead_pixel.css is generated from webroot/css/dp_template.css by
 * replacing PRIMARY_COLOR with the theme colour from Settings.
 *
 * The generated file records a hash of the template it was built from, so when
 * an update ships a changed template the stylesheet is rebuilt automatically
 * with the site's own colour (instead of being overwritten by the package).
 */
final class ThemeCss
{
    private const TEMPLATE = 'css' . DS . 'dp_template.css';
    private const OUTPUT = 'css' . DS . 'dead_pixel.css';

    /** Rebuild the stylesheet if it is missing or was built from an older template. */
    public static function isCurrent(): bool
    {
        $output = WWW_ROOT . self::OUTPUT;
        if (!is_file($output)) {
            return false;
        }
        $head = (string)file_get_contents($output, false, null, 0, 512);

        return str_contains($head, self::templateHash());
    }

    public static function build(string $primaryColor): void
    {
        $css = str_replace('PRIMARY_COLOR', $primaryColor, (string)file_get_contents(WWW_ROOT . self::TEMPLATE));
        $header = sprintf(
            "/* Generated from dp_template.css (%s) with the theme colour from Settings. Edit the template, not this file. */\n",
            self::templateHash(),
        );
        // @charset must stay the very first statement, so the marker goes after it.
        if (str_starts_with($css, '@charset')) {
            [$charset, $rest] = explode("\n", $css, 2) + [1 => ''];
            $css = $charset . "\n" . $header . $rest;
        } else {
            $css = $header . $css;
        }
        file_put_contents(WWW_ROOT . self::OUTPUT, $css, LOCK_EX);
    }

    private static function templateHash(): string
    {
        return md5_file(WWW_ROOT . self::TEMPLATE) ?: '';
    }
}

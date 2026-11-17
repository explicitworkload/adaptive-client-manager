<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Client;
use App\Model\Entity\Setting;

/**
 * Builds the one-line address/contact strings printed on PDFs.
 */
final class AddressFormatter
{
    public static function company(Setting $s): string
    {
        $line = self::join([
            $s->company_address_line_1, $s->company_address_line_2, $s->company_town,
            $s->company_city, $s->company_state, $s->company_zip,
        ]);
        if ($s->company_phone) {
            $line .= ' | TEL: ' . $s->company_phone;
        }
        if ($s->company_email) {
            $line .= ' | EMAIL: ' . $s->company_email;
        }
        if ($s->custom_1) {
            $line .= ' | ' . $s->custom_1;
        }

        return $line;
    }

    public static function client(Client $c): string
    {
        return self::join([
            $c->client_address_line_1, $c->client_address_line_2, $c->client_town,
            $c->client_city, $c->client_state, $c->client_zip,
        ]);
    }

    public static function clientContact(Client $c): string
    {
        $parts = [];
        if ($c->client_phone) {
            $parts[] = 'TEL: ' . $c->client_phone;
        }
        if ($c->client_email) {
            $parts[] = 'EMAIL: ' . $c->client_email;
        }
        if ($c->custom_1) {
            $parts[] = $c->custom_1;
        }

        return implode('    ', $parts);
    }

    /**
     * @param array<string|null> $parts
     */
    private static function join(array $parts): string
    {
        return implode(', ', array_filter($parts, fn($p) => $p !== null && $p !== ''));
    }
}

<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Client;
use App\Model\Entity\Setting;

/**
 * Fills [SHORTCODE] placeholders in the email templates edited under Settings.
 *
 * Moved out of the legacy Invoices/email.ctp and Quotes/email.ctp views.
 * Values are HTML-escaped because the template is HTML (TinyMCE).
 */
final class EmailShortcodes
{
    /**
     * @param string $type 'INVOICE' or 'QUOTE' (prefix for the document shortcodes)
     */
    public static function render(
        string $template,
        string $type,
        string $number,
        string $date,
        string $total,
        Client $client,
        Setting $settings,
        string $userName,
    ): string {
        $values = [
            'COMPANY_NAME' => $settings->company_name,
            'COMPANY_EMAIL' => $settings->company_email,
            'COMPANY_ADDRESS_1' => $settings->company_address_line_1,
            'COMPANY_ADDRESS_2' => $settings->company_address_line_2,
            'COMPANY_TOWN' => $settings->company_town,
            'COMPANY_CITY' => $settings->company_city,
            'COMPANY_STATE' => $settings->company_state,
            'COMPANY_ZIP' => $settings->company_zip,
            'PAYMENT_INFO' => $settings->payment_info,
            $type . '_NUMBER' => $number,
            $type . '_DATE' => $date,
            $type . '_TOTAL' => $settings->currency . $total,
            'CLIENT_NAME' => $client->client_name,
            'CLIENT_CONTACT' => $client->client_contact,
            'CLIENT_ADDRESS_1' => $client->client_address_line_1,
            'CLIENT_ADDRESS_2' => $client->client_address_line_2,
            'CLIENT_TOWN' => $client->client_town,
            'CLIENT_CITY' => $client->client_city,
            'CLIENT_STATE' => $client->client_state,
            'CLIENT_ZIP' => $client->client_zip,
            'USER' => $userName,
        ];

        $search = [];
        $replace = [];
        foreach ($values as $code => $value) {
            $search[] = '[' . $code . ']';
            $replace[] = h((string)$value);
        }

        return str_replace($search, $replace, $template);
    }
}

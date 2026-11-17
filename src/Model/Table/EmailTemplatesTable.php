<?php
declare(strict_types=1);

namespace App\Model\Table;

/**
 * Email bodies for outgoing invoices/quotes.
 *
 * The legacy model's validation was a copy of the Client rules for fields this
 * table doesn't have, and the app only ever updated `content`, so none is kept.
 */
class EmailTemplatesTable extends AppTable
{
    public const CLIENT_INVOICE = 1;
    public const CLIENT_QUOTE = 2;

    /**
     * Default template bodies: installed with a new database and restored by
     * the "Load Default" button under Settings > Email.
     */
    public const DEFAULTS = [
        'invoice' => '<p><span style="font-size: small;">Hi [CLIENT_CONTACT],</span></p><p><span style="font-size: small;">Please find&nbsp;Invoice Number: [INVOICE_NUMBER] (Total: [INVOICE_TOTAL]) attached to this email.</span></p><p><span style="font-size: small;">If you have any questions then please contact us at [COMPANY_EMAIL].</span></p><p><span style="font-size: small;">&nbsp;</span></p><p><span style="font-size: small;">Regards,</span></p><p>&nbsp;</p><p><span style="font-size: small;">[USER]</span></p><p><span style="font-size: small;">[COMPANY_NAME]</span></p>',
        'quote' => '<p><span style="font-size: small;">Hi [CLIENT_CONTACT],</span></p><p><span style="font-size: small;">Please find Quote Number: [QUOTE_NUMBER] attached to this email.</span></p><p><span style="font-size: small;">If you have any questions then please contact us at [COMPANY_EMAIL].</span></p><p><span style="font-size: small;">&nbsp;</span></p><p><span style="font-size: small;">Regards,</span></p><p>&nbsp;</p><p><span style="font-size: small;">[USER]</span></p><p><span style="font-size: small;">[COMPANY_NAME]</span></p>',
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('email_templates');
        $this->setDisplayField('name');
    }
}

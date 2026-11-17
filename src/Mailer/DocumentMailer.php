<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;

/**
 * Sends an invoice or quote to a client with the PDF attached.
 * Transport is configured in config/app_local.php (EmailTransport.default).
 */
class DocumentMailer extends Mailer
{
    public function document(
        string $to,
        string $from,
        string $subject,
        string $htmlBody,
        string $pdfName,
        string $pdfBytes,
    ): void {
        $this->setTo($to)
            ->setFrom($from)
            ->setReturnPath($from)
            ->setSubject($subject)
            ->setEmailFormat('html')
            ->setAttachments([
                $pdfName => ['data' => $pdfBytes, 'mimetype' => 'application/pdf'],
            ]);
        // Mailer::send() delivers after this method returns, so only configure here.
        $this->setViewVars(['body' => $htmlBody]);
        $this->viewBuilder()->setTemplate('document')->disableAutoLayout();
    }
}

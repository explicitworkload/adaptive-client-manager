<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Client;
use App\Model\Entity\Setting;
use tFPDF;

/**
 * Renders an invoice or quote as a PDF.
 *
 * Layout is carried over unchanged from the legacy InvoicesController::email()
 * and QuotesController::email(), which were duplicates of each other.
 */
final class DocumentPdf
{
    /**
     * @param string $title 'INVOICE' or 'QUOTE'
     * @param iterable<\Cake\Datasource\EntityInterface> $items line items
     * @return string PDF bytes
     */
    public static function render(
        string $title,
        string $number,
        string $date,
        ?string $notes,
        Client $client,
        iterable $items,
        Setting $settings,
    ): string {
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', self::fontDir());
        }
        $currency = (string)$settings->currency;

        $pdf = new tFPDF();
        $pdf->AddPage();
        $pdf->AddFont('Ostrich', '', 'ostrich-regular-webfont.php');
        $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetDrawColor(238, 238, 238);
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetTextColor(80, 80, 80);

        // Company logo
        $logo = WWW_ROOT . 'img' . DS . 'logo.png';
        if (is_file($logo)) {
            $pdf->Image($logo, 10, 10, 88);
        }

        // Document details
        $pdf->SetXY(180, 14);
        $pdf->SetFont('Ostrich', '', 40);
        $pdf->Cell(0, 4, $title, 0, 2, 'R');
        $pdf->SetFont('Ostrich', '', 26);
        $pdf->Cell(0, 16, '#' . $number, 0, 2, 'R');
        $pdf->SetFont('DejaVu', '', 10);
        $pdf->Cell(0, 10, 'Date: ' . $date, 0, 1, 'R');
        $pdf->Cell(0, 0, 'Due: ' . $date, 0, 1, 'R');

        // Client details
        $pdf->SetXY(10, 38);
        $pdf->SetFont('DejaVu', '', 12);
        $pdf->Cell(0, 2, 'To: ' . $client->client_name, 0, 2);
        $pdf->SetFont('DejaVu', '', 9);
        $pdf->Cell(0, 8, AddressFormatter::client($client), 0, 2);
        $pdf->Cell(0, 0, AddressFormatter::clientContact($client), 0, 2);
        $pdf->SetXY(10, 58);

        // Items
        $pdf->SetFont('Ostrich', '', 14);
        $pdf->Cell(122, 10, 'Item', 0, 0);
        $pdf->Cell(16, 10, 'QTY', 0, 0, 'C');
        $pdf->Cell(26, 10, 'Price', 0, 0, 'R');
        $pdf->Cell(26, 10, 'Net SubTotal', 0, 1, 'R');

        $subTotal = 0.0;
        $tax = 0.0;
        foreach ($items as $item) {
            $itemSubTotal = (float)$item->quantity * (float)$item->unit_price;
            $subTotal += $itemSubTotal;
            $tax += $itemSubTotal * (float)$item->tax_rate / 100;

            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetFont('DejaVu', '', 9);
            $pdf->Cell(122, 6, (string)$item->title, 0, 0, '', true);
            $pdf->Cell(16, 6, self::num($item->quantity, 0), 0, 0, 'C', true);
            $pdf->Cell(26, 6, self::num($item->unit_price), 0, 0, 'R', true);
            $pdf->Cell(26, 6, self::num($itemSubTotal), 0, 1, 'R', true);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->MultiCell(0, 5, (string)$item->details, 0, 'L');
        }

        // Totals
        $rows = [
            [200, 16, 'Net Total:', $subTotal],
            [208, 16, 'Tax:', $tax],
            [218, 18, 'Total:', $subTotal + $tax],
        ];
        foreach ($rows as [$y, $size, $label, $amount]) {
            $pdf->SetXY(10, $y);
            $pdf->SetFont('Ostrich', '', $size);
            $pdf->Cell(156, 10, $label, 0, 2, 'R');
            $pdf->SetXY(10, $y);
            $pdf->SetFont('DejaVu', '', $size);
            $pdf->Cell(0, 10, $currency . self::num($amount), 0, 2, 'R');
        }

        // Notes and payment information
        self::box($pdf, 236, 'Notes', (string)$notes);
        self::box($pdf, 256, 'Payment Information', (string)$settings->payment_info);

        // Footer: company details
        $pdf->SetXY(0, 280);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFont('DejaVu', '', 7);
        $pdf->Cell(0, 4, AddressFormatter::company($settings), 0, 2, 'C');

        return $pdf->Output('S');
    }

    /**
     * Writable copy of the PDF fonts under tmp/.
     *
     * tFPDF caches font metrics next to the .ttf and records the font's
     * absolute path in that cache. Shipping a cache built elsewhere (or moving
     * the site) breaks PDF output with "Can't open file ...", so the fonts are
     * copied to tmp/ and any cache pointing at another path is discarded.
     */
    private static function fontDir(): string
    {
        $source = RESOURCES . 'fonts' . DS;
        $target = TMP . 'fonts' . DS;
        $files = [
            'ostrich-regular-webfont.php',
            'ostrich-regular-webfont.z',
            'unifont' . DS . 'DejaVuSansCondensed.ttf',
        ];
        if (!is_dir($target . 'unifont')) {
            mkdir($target . 'unifont', 0775, true);
        }
        // tFPDF ships the same font; used if resources/fonts/ is incomplete.
        $fallback = [
            'unifont' . DS . 'DejaVuSansCondensed.ttf' => ROOT . DS . 'vendor' . DS . 'setasign' . DS . 'tfpdf'
                . DS . 'font' . DS . 'unifont' . DS . 'DejaVuSansCondensed.ttf',
        ];
        foreach ($files as $file) {
            if (is_file($target . $file)) {
                continue;
            }
            $from = is_file($source . $file) ? $source . $file : ($fallback[$file] ?? null);
            if ($from === null || !is_file($from) || !copy($from, $target . $file)) {
                throw new \RuntimeException('PDF font missing: restore resources/fonts/' . str_replace(DS, '/', $file));
            }
        }

        $cache = $target . 'unifont' . DS . 'dejavusanscondensed';
        $ttf = $target . 'unifont' . DS . 'DejaVuSansCondensed.ttf';
        if (is_file($cache . '.mtx.php') && !str_contains((string)file_get_contents($cache . '.mtx.php'), "'" . $ttf . "'")) {
            foreach (['.mtx.php', '.cw.dat', '.cw127.php'] as $ext) {
                @unlink($cache . $ext);
            }
        }

        return $target;
    }

    private static function box(tFPDF $pdf, int $y, string $heading, string $body): void
    {
        $pdf->SetXY(10, $y);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFont('DejaVu', '', 9);
        $pdf->Cell(0, 6, $heading, 0, 2, '', true);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->MultiCell(0, 6, $body, 0, 'L', true);
    }

    private static function num(mixed $value, int $decimals = 2): string
    {
        $n = (float)$value;
        // Quantities print without decimals when whole, e.g. "5" not "5.00".
        if ($decimals === 0 && floor($n) != $n) {
            $decimals = 2;
        }

        return number_format($n, $decimals, '.', ',');
    }
}

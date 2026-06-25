<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\System\Repository\SystemSettingRepositoryInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class DocumentPdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        private readonly SystemSettingRepositoryInterface $settings,
        #[Autowire('%kernel.project_dir%/var/project_files')] private readonly string $projectFilesDir,
        #[Autowire('%kernel.project_dir%/var/customer_files')] private readonly string $customerFilesDir,
    ) {}

    public function generateForInvoice(Invoice $invoice, string $colorMode): GeneratedPdf
    {
        $html = $this->twig->render('pdf/invoice.html.twig', [
            'invoice'              => $invoice,
            'colorMode'            => $colorMode,
            'show_company_address' => $this->settings->get('PDF_SHOW_COMPANY_ADDRESS', '1') !== '0',
        ]);

        $fileName  = sprintf('invoice-%s-%s.pdf', $this->safeNumber($invoice->getNumber()), date('Ymd-His'));
        $directory = $this->resolveInvoiceDirectory($invoice);
        $filePath  = $directory . '/' . $fileName;

        $this->renderPdf($html, $filePath);

        return new GeneratedPdf($filePath, $fileName);
    }

    public function generateForQuote(Quote $quote, string $colorMode): GeneratedPdf
    {
        $html = $this->twig->render('pdf/quote.html.twig', [
            'quote'                => $quote,
            'colorMode'            => $colorMode,
            'show_company_address' => $this->settings->get('PDF_SHOW_COMPANY_ADDRESS', '1') !== '0',
        ]);

        $fileName  = sprintf('quote-%s-%s.pdf', $this->safeNumber($quote->getNumber()), date('Ymd-His'));
        $directory = $this->resolveQuoteDirectory($quote);
        $filePath  = $directory . '/' . $fileName;

        $this->renderPdf($html, $filePath);

        return new GeneratedPdf($filePath, $fileName);
    }

    private function renderPdf(string $html, string $outputPath): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = dirname($outputPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Cannot create directory: %s', $dir));
        }

        file_put_contents($outputPath, $dompdf->output());
    }

    private function safeNumber(string $number): string
    {
        return preg_replace('/[\/\\\\:*?"<>|]/', '-', $number) ?? $number;
    }

    private function resolveInvoiceDirectory(Invoice $invoice): string
    {
        if ($invoice->getProject() !== null) {
            return $this->projectFilesDir . '/' . $invoice->getProject()->getId() . '/invoices';
        }

        return $this->customerFilesDir . '/' . $invoice->getCustomer()->getId() . '/invoices';
    }

    private function resolveQuoteDirectory(Quote $quote): string
    {
        if ($quote->getProject() !== null) {
            return $this->projectFilesDir . '/' . $quote->getProject()->getId() . '/quotes';
        }

        return $this->customerFilesDir . '/' . $quote->getCustomer()->getId() . '/quotes';
    }
}

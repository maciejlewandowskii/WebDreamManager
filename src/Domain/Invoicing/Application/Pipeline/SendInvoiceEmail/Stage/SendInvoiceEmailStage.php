<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\Stage;

use App\Domain\Integration\Application\IntegrationStatusService;
use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Domain\Invoicing\Infrastructure\DoctrineInvoicePdfRecordRepository;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AutoconfigureTag('app.invoice.send_email', attributes: ['priority' => 200])]
final readonly class SendInvoiceEmailStage implements PipelineHandlerInterface
{
    public function __construct(
        private DoctrineInvoicePdfRecordRepository $pdfRecords,
        private MailerInterface $mailer,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private IntegrationStatusService $integrations,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendInvoiceEmailCommand);

        $invoice = $payload->invoice;

        $record = $payload->pdfId !== null ? $this->pdfRecords->find($payload->pdfId) : null;
        if ($record === null) {
            $record = $this->pdfRecords->findByInvoice($invoice)[0] ?? null;
        }

        $paymentUrl = null;
        if ($invoice->getPaymentToken() !== null && $this->integrations->isEnabled('stripe')) {
            $paymentUrl = $this->urlGenerator->generate(
                'app_payment_show',
                ['token' => $invoice->getPaymentToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $html  = $this->twig->render('email/invoice.html.twig', [
            'invoice'    => $invoice,
            'paymentUrl' => $paymentUrl,
        ]);
        $email = new Email()
            ->to((string) $invoice->getCustomer()->getEmail())
            ->subject('Invoice ' . $invoice->getNumber())
            ->html($html);

        if ($record !== null && is_file($record->getFilePath())) {
            $attachmentName = str_replace(['/', '\\'], '-', $invoice->getNumber()) . '.pdf';
            $email->attach(
                (string) file_get_contents($record->getFilePath()),
                $attachmentName,
                'application/pdf'
            );
        }

        $this->mailer->send($email);

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\SendQuoteEmailCommand;
use App\Domain\Invoicing\Infrastructure\DoctrineQuotePdfRecordRepository;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AutoconfigureTag('app.quote.send_email', attributes: ['priority' => 200])]
final class SendQuoteEmailStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly DoctrineQuotePdfRecordRepository $pdfRecords,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
    ) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendQuoteEmailCommand);

        $quote = $payload->quote;

        $record = $payload->pdfId !== null ? $this->pdfRecords->find($payload->pdfId) : null;
        if ($record === null) {
            $record = $this->pdfRecords->findByQuote($quote)[0] ?? null;
        }

        $html  = $this->twig->render('email/quote.html.twig', ['quote' => $quote]);
        $email = (new Email())
            ->to((string) $quote->getCustomer()->getEmail())
            ->subject('Quote ' . $quote->getNumber())
            ->html($html);

        if ($record !== null && is_file($record->getFilePath())) {
            $attachmentName = str_replace(['/', '\\'], '-', $quote->getNumber()) . '.pdf';
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

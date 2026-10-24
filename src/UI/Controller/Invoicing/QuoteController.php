<?php

declare(strict_types=1);

namespace App\UI\Controller\Invoicing;

use App\Domain\Invoicing\Application\Pipeline\DeleteQuote\DeleteQuoteCommand;
use App\Domain\Invoicing\Application\Pipeline\GenerateQuotePdf\GenerateQuotePdfCommand;
use App\Domain\Invoicing\Application\Pipeline\SendQuoteEmail\SendQuoteEmailCommand;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Infrastructure\DoctrineQuotePdfRecordRepository;
use App\Domain\IssueTracker\Application\IssueTrackerService;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use SimpleThings\EntityAudit\AuditReader;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/quotes', name: 'app_quote_')]
final class QuoteController extends AppController
{
    /**
     * @param iterable<PipelineHandlerInterface> $generatePdfHandlers
     * @param iterable<PipelineHandlerInterface> $sendEmailHandlers
     * @param iterable<PipelineHandlerInterface> $deleteHandlers
     */
    public function __construct(
        private readonly DoctrineQuotePdfRecordRepository $pdfRecords,
        private readonly AuditReader $auditReader,
        #[AutowireIterator('app.quote.generate_pdf')] private readonly iterable $generatePdfHandlers,
        #[AutowireIterator('app.quote.send_email')] private readonly iterable $sendEmailHandlers,
        #[AutowireIterator('app.quote.delete')] private readonly iterable $deleteHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/invoicing/quote/index.html.twig');
    }

    #[Route('/new', name: 'new')]
    public function new(): Response
    {
        return $this->render('views/invoicing/quote/new.html.twig');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(Quote $quote): Response
    {
        $allRevisions = $this->auditReader->findRevisions(Quote::class, $quote->getId());
        $revisions = [];

        foreach ($allRevisions as $i => $revision) {
            $currentRev = (int) $revision->getRev();
            $previousRev = isset($allRevisions[$i + 1]) ? (int) $allRevisions[$i + 1]->getRev() : null;

            if ($previousRev === null) {
                // First revision is always kept
                $revisions[] = $revision;
                continue;
            }

            $currentEntity = $this->auditReader->find(Quote::class, $quote->getId(), $currentRev);
            $previousEntity = $this->auditReader->find(Quote::class, $quote->getId(), $previousRev);

            $hasChanges = $currentEntity->getNumber() !== $previousEntity->getNumber()
                || $currentEntity->getCustomer()->getId() !== $previousEntity->getCustomer()->getId()
                || $currentEntity->getIssuedAt()->format('Y-m-d') !== $previousEntity->getIssuedAt()->format('Y-m-d')
                || ($currentEntity->getValidUntil() ? $currentEntity->getValidUntil()->format('Y-m-d') : null) !== ($previousEntity->getValidUntil() ? $previousEntity->getValidUntil()->format('Y-m-d') : null)
                || $currentEntity->getCurrency() !== $previousEntity->getCurrency()
                || $currentEntity->getDefaultTaxRate() !== $previousEntity->getDefaultTaxRate()
                || $currentEntity->getIntroText() !== $previousEntity->getIntroText()
                || $currentEntity->getNotes() !== $previousEntity->getNotes()
                || json_encode($currentEntity->getItemsSnapshot()) !== json_encode($previousEntity->getItemsSnapshot());

            if ($hasChanges) {
                $revisions[] = $revision;
            }
        }

        return $this->render('views/invoicing/quote/show.html.twig', [
            'quote'     => $quote,
            'pdfs'      => $this->pdfRecords->findByQuote($quote),
            'revisions' => $revisions,
        ]);
    }

    #[Route('/{id}/revision/{rev}', name: 'revision_view', requirements: ['id' => '[0-9a-f-]{36}', 'rev' => '\d+'])]
    public function revisionView(Quote $quote, int $rev): Response
    {
        $entity       = $this->auditReader->find(Quote::class, $quote->getId(), $rev);
        $revisionInfo = $this->auditReader->findRevision($rev);

        return $this->render('views/invoicing/quote/revision_modal.html.twig', [
            'quote'        => $quote,
            'entity'       => $entity,
            'revisionInfo' => $revisionInfo,
            'rev'          => $rev,
            'mode'         => 'view',
            'previous'     => null,
        ]);
    }

    #[Route('/{id}/revision/{rev}/diff', name: 'revision_diff', requirements: ['id' => '[0-9a-f-]{36}', 'rev' => '\d+'])]
    public function revisionDiff(Quote $quote, int $rev): Response
    {
        $allRevisions = $this->auditReader->findRevisions(Quote::class, $quote->getId());

        $currentRevision  = null;
        $previousRevision = null;
        foreach ($allRevisions as $i => $revision) {
            if ((int) $revision->getRev() === $rev) {
                $currentRevision  = $revision;
                $previousRevision = $allRevisions[$i + 1] ?? null;
                break;
            }
        }

        if ($currentRevision === null) {
            throw $this->createNotFoundException('Revision not found.');
        }

        $entity   = $this->auditReader->find(Quote::class, $quote->getId(), $rev);
        $previous = $previousRevision !== null
            ? $this->auditReader->find(Quote::class, $quote->getId(), (int) $previousRevision->getRev())
            : null;

        return $this->render('views/invoicing/quote/revision_modal.html.twig', [
            'quote'        => $quote,
            'entity'       => $entity,
            'revisionInfo' => $currentRevision,
            'rev'          => $rev,
            'mode'         => 'diff',
            'previous'     => $previous,
        ]);
    }

    #[Route('/{id}/generate-pdf', name: 'generate_pdf', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function generatePdf(Quote $quote, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('generate_pdf_quote_' . $quote->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        new PipelineProcessor($this->generatePdfHandlers)->run(new GenerateQuotePdfCommand($quote));

        $this->addFlash('success', 'PDF generated successfully.');

        return $this->redirectToRoute('app_quote_show', ['id' => $quote->getId()]);
    }

    #[Route('/{id}/pdf/{pdfId}', name: 'download_pdf', requirements: ['id' => '[0-9a-f-]{36}', 'pdfId' => '[0-9a-f-]{36}'])]
    public function downloadPdf(Quote $quote, string $pdfId): Response
    {
        $record = $this->pdfRecords->find($pdfId);
        if ($record === null || $record->getQuote()->getId() !== $quote->getId()) {
            throw $this->createNotFoundException('PDF not found.');
        }

        if (!is_file($record->getFilePath())) {
            throw $this->createNotFoundException('PDF file missing from disk.');
        }

        $safeFileName = str_replace(['/', '\\'], '-', $record->getFileName());
        $response     = new BinaryFileResponse($record->getFilePath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $safeFileName);

        return $response;
    }

    #[Route('/{id}/send-email', name: 'send_email', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function sendEmail(Quote $quote, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('send_email_quote_' . $quote->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $customerEmail = $quote->getCustomer()->getEmail();
        if ($customerEmail === null) {
            $this->addFlash('error', 'Customer has no email address.');
            return $this->redirectToRoute('app_quote_show', ['id' => $quote->getId()]);
        }

        $pdfId = (string) $request->request->get('pdf_id');
        new PipelineProcessor($this->sendEmailHandlers)->run(
            new SendQuoteEmailCommand($quote, $pdfId !== '' ? $pdfId : null)
        );

        $this->addFlash('success', 'Quote sent to ' . $customerEmail . '.');

        return $this->redirectToRoute('app_quote_show', ['id' => $quote->getId()]);
    }

    #[Route('/{id}/export-issues', name: 'export_issues', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function exportIssues(Quote $quote, Request $request, IssueTrackerService $service): Response
    {
        $project = $quote->getProject();
        if ($project === null || !$project->hasTracker()) {
            $this->addFlash('error', 'This quote has no linked project with a configured tracker.');

            return $this->redirectToRoute('app_quote_show', ['id' => $quote->getId()]);
        }

        if (!$this->isCsrfTokenValid('export_issues_' . $quote->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('app_quote_show', ['id' => $quote->getId()]);
        }

        $count = $service->createIssuesFromQuote($quote, $project);
        $this->addFlash('success', "Created {$count} issues in {$project->getTrackerType()->label()}.");

        return $this->redirectToRoute('app_project_issues', ['id' => $project->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(Quote $quote, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_quote_' . $quote->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteQuoteCommand($quote));
            $this->addFlash('success', 'Quote deleted successfully.');
        }

        return $this->redirectToReferer($request, 'app_quote_index');
    }
}

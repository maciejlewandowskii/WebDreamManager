<?php

declare(strict_types=1);

namespace App\UI\Controller\Invoicing;

use App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\CreateInvoiceFromTimeTrackingCommand;
use App\Domain\Invoicing\Application\Pipeline\DeleteInvoice\DeleteInvoiceCommand;
use App\Domain\Invoicing\Application\Pipeline\GenerateInvoicePdf\GenerateInvoicePdfCommand;
use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Infrastructure\DoctrineInvoicePdfRecordRepository;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use SimpleThings\EntityAudit\AuditReader;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/invoices', name: 'app_invoice_')]
final class InvoiceController extends AppController
{
    public function __construct(
        private readonly DoctrineInvoicePdfRecordRepository $pdfRecords,
        private readonly AuditReader $auditReader,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly TimeRecordRepositoryInterface $timeRecordRepository,
        #[AutowireIterator('app.invoice.generate_pdf')] private readonly iterable $generatePdfHandlers,
        #[AutowireIterator('app.invoice.send_email')] private readonly iterable $sendEmailHandlers,
        #[AutowireIterator('app.invoice.from_time_tracking')] private readonly iterable $fromTimeTrackingHandlers,
        #[AutowireIterator('app.invoice.delete')] private readonly iterable $deleteHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/invoicing/invoice/index.html.twig');
    }

    #[Route('/new', name: 'new')]
    public function new(): Response
    {
        return $this->render('views/invoicing/invoice/new.html.twig');
    }

    #[Route('/from-time-tracking', name: 'from_time_tracking', methods: ['GET'], condition: "request.headers.get('Turbo-Frame')")]
    public function fromTimeTracking(Request $request): Response
    {
        $projectId       = (string) $request->query->get('projectId', '');
        $selectedProject = $projectId !== '' ? $this->projectRepository->findById($projectId) : null;
        $records         = $selectedProject !== null
            ? $this->timeRecordRepository->findUninvoicedByProject($selectedProject)
            : [];

        return $this->render('views/invoicing/invoice/from_time_tracking.html.twig', [
            'projects'        => $this->projectRepository->findAll(),
            'selectedProject' => $selectedProject,
            'records'         => $records,
        ]);
    }

    #[Route('/from-time-tracking', name: 'from_time_tracking_create', methods: ['POST'])]
    public function fromTimeTrackingCreate(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('from_time_tracking', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $projectId       = (string) $request->request->get('projectId', '');
        $selectedProject = $projectId !== '' ? $this->projectRepository->findById($projectId) : null;

        if ($selectedProject === null) {
            $this->addFlash('error', 'Project not found.');
            return $this->redirectToReferer($request, 'app_invoice_from_time_tracking');
        }

        /** @var string[] $recordIds */
        $recordIds = $request->request->all('recordIds');
        $command   = new CreateInvoiceFromTimeTrackingCommand($selectedProject, $recordIds);
        new PipelineProcessor($this->fromTimeTrackingHandlers)->run($command);

        $this->addFlash('success', sprintf('Invoice created from %d time record(s).', count($recordIds)));

        assert($command->result !== null);
        return $this->redirectToRoute('app_invoice_show', ['id' => $command->result->getId()]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(Invoice $invoice): Response
    {
        $allRevisions = $this->auditReader->findRevisions(Invoice::class, $invoice->getId());
        $revisions = [];

        foreach ($allRevisions as $i => $revision) {
            $currentRev = (int) $revision->getRev();
            $previousRev = isset($allRevisions[$i + 1]) ? (int) $allRevisions[$i + 1]->getRev() : null;

            if ($previousRev === null) {
                // First revision is always kept
                $revisions[] = $revision;
                continue;
            }

            $currentEntity = $this->auditReader->find(Invoice::class, $invoice->getId(), $currentRev);
            $previousEntity = $this->auditReader->find(Invoice::class, $invoice->getId(), $previousRev);

            $hasChanges = $currentEntity->getNumber() !== $previousEntity->getNumber()
                || $currentEntity->getCustomer()->getId() !== $previousEntity->getCustomer()->getId()
                || $currentEntity->getIssuedAt()->format('Y-m-d') !== $previousEntity->getIssuedAt()->format('Y-m-d')
                || $currentEntity->getDueAt()->format('Y-m-d') !== $previousEntity->getDueAt()->format('Y-m-d')
                || $currentEntity->getCurrency() !== $previousEntity->getCurrency()
                || $currentEntity->getDefaultTaxRate() !== $previousEntity->getDefaultTaxRate()
                || $currentEntity->getBankAccount() !== $previousEntity->getBankAccount()
                || $currentEntity->getPaymentTerms() !== $previousEntity->getPaymentTerms()
                || $currentEntity->getNotes() !== $previousEntity->getNotes()
                || json_encode($currentEntity->getItemsSnapshot()) !== json_encode($previousEntity->getItemsSnapshot());

            if ($hasChanges) {
                $revisions[] = $revision;
            }
        }

        return $this->render('views/invoicing/invoice/show.html.twig', [
            'invoice'   => $invoice,
            'pdfs'      => $this->pdfRecords->findByInvoice($invoice),
            'revisions' => $revisions,
        ]);
    }

    #[Route('/{id}/revision/{rev}', name: 'revision_view', requirements: ['id' => '[0-9a-f-]{36}', 'rev' => '\d+'])]
    public function revisionView(Invoice $invoice, int $rev): Response
    {
        $entity       = $this->auditReader->find(Invoice::class, $invoice->getId(), $rev);
        $revisionInfo = $this->auditReader->findRevision($rev);

        return $this->render('views/invoicing/invoice/revision_modal.html.twig', [
            'invoice'      => $invoice,
            'entity'       => $entity,
            'revisionInfo' => $revisionInfo,
            'rev'          => $rev,
            'mode'         => 'view',
            'previous'     => null,
        ]);
    }

    #[Route('/{id}/revision/{rev}/diff', name: 'revision_diff', requirements: ['id' => '[0-9a-f-]{36}', 'rev' => '\d+'])]
    public function revisionDiff(Invoice $invoice, int $rev): Response
    {
        $allRevisions = $this->auditReader->findRevisions(Invoice::class, $invoice->getId());

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

        $entity   = $this->auditReader->find(Invoice::class, $invoice->getId(), $rev);
        $previous = $previousRevision !== null
            ? $this->auditReader->find(Invoice::class, $invoice->getId(), (int) $previousRevision->getRev())
            : null;

        return $this->render('views/invoicing/invoice/revision_modal.html.twig', [
            'invoice'      => $invoice,
            'entity'       => $entity,
            'revisionInfo' => $currentRevision,
            'rev'          => $rev,
            'mode'         => 'diff',
            'previous'     => $previous,
        ]);
    }

    #[Route('/{id}/generate-pdf', name: 'generate_pdf', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function generatePdf(Invoice $invoice, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('generate_pdf_invoice_' . $invoice->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        new PipelineProcessor($this->generatePdfHandlers)->run(new GenerateInvoicePdfCommand($invoice));

        $this->addFlash('success', 'PDF generated successfully.');

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/pdf/{pdfId}', name: 'download_pdf', requirements: ['id' => '[0-9a-f-]{36}', 'pdfId' => '[0-9a-f-]{36}'])]
    public function downloadPdf(Invoice $invoice, string $pdfId): Response
    {
        $record = $this->pdfRecords->find($pdfId);
        if ($record === null || $record->getInvoice()->getId() !== $invoice->getId()) {
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
    public function sendEmail(Invoice $invoice, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('send_email_invoice_' . $invoice->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $customerEmail = $invoice->getCustomer()->getEmail();
        if ($customerEmail === null) {
            $this->addFlash('error', 'Customer has no email address.');
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        $pdfId = (string) $request->request->get('pdf_id');
        new PipelineProcessor($this->sendEmailHandlers)->run(
            new SendInvoiceEmailCommand($invoice, $pdfId !== '' ? $pdfId : null)
        );

        $this->addFlash('success', 'Invoice sent to ' . $customerEmail . '.');

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '[0-9a-f-]{36}'])]
    public function delete(Invoice $invoice, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_invoice_' . $invoice->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteInvoiceCommand($invoice));
            $this->addFlash('success', 'Invoice deleted successfully.');
        }

        return $this->redirectToReferer($request, 'app_invoice_index');
    }
}

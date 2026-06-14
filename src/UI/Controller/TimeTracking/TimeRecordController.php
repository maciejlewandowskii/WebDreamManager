<?php

declare(strict_types=1);

namespace App\UI\Controller\TimeTracking;

use App\Domain\Identity\Entity\User;
use App\Domain\TimeTracking\Application\Data\TimeRecordData;
use App\Domain\TimeTracking\Application\Pipeline\CreateTimeRecord\CreateTimeRecordCommand;
use App\Domain\TimeTracking\Application\Pipeline\DeleteTimeRecord\DeleteTimeRecordCommand;
use App\Domain\TimeTracking\Application\Pipeline\UpdateTimeRecord\UpdateTimeRecordCommand;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\TimeRecordType;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/time', name: 'app_time_')]
final class TimeRecordController extends AppController
{
    /**
     * @param iterable<PipelineHandlerInterface> $createHandlers
     * @param iterable<PipelineHandlerInterface> $updateHandlers
     * @param iterable<PipelineHandlerInterface> $deleteHandlers
     */
    public function __construct(
        #[AutowireIterator('app.time_record.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.time_record.update')] private readonly iterable $updateHandlers,
        #[AutowireIterator('app.time_record.delete')] private readonly iterable $deleteHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/time_tracking/index.html.twig');
    }

    #[Route('/new', name: 'new', condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function new(Request $request, \App\Domain\Project\Repository\ProjectRepositoryInterface $projectRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = new TimeRecordData();
        
        if ($request->query->has('date')) {
            try {
                $data->date = new \DateTimeImmutable($request->query->getString('date'));
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }
        
        if ($request->query->has('project')) {
            $projectId = $request->query->get('project');
            if ($projectId) {
                $project = $projectRepository->findById($projectId);
                if ($project !== null) {
                    $data->project = $project;
                }
            }
        }

        $form = $this->createForm(TimeRecordType::class, $data, [
            'action' => $this->generateUrl('app_time_new', $request->query->all()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->createHandlers)->run(new CreateTimeRecordCommand($data, $user));
            $this->addFlash('success', 'Time record saved.');

            return $this->noContentResponse('time-record:mutated');
        }

        return $this->render('views/time_tracking/new.html.twig', [
            'form' => $form,
        ], new Response(
            null,
            $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function edit(TimeRecord $record, Request $request): Response
    {
        $data = TimeRecordData::fromEntity($record);
        $form = $this->createForm(TimeRecordType::class, $data, [
            'action' => $this->generateUrl('app_time_edit', ['id' => $record->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->updateHandlers)->run(new UpdateTimeRecordCommand($record, $data));
            $this->addFlash('success', 'Time record updated.');

            return $this->noContentResponse('time-record:mutated');
        }

        return $this->render('views/time_tracking/edit.html.twig', [
            'form'   => $form,
            'record' => $record,
        ], new Response(
            null,
            $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function delete(TimeRecord $record, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_time_' . $record->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteTimeRecordCommand($record));
            $this->addFlash('success', 'Time record deleted.');

            return $this->noContentResponse('time-record:mutated');
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

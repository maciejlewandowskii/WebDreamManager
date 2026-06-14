<?php

declare(strict_types=1);

namespace App\UI\Controller\Customer;

use App\Domain\Customer\Application\Data\CustomerData;
use App\Domain\Customer\Application\Pipeline\CreateCustomer\CreateCustomerCommand;
use App\Domain\Customer\Application\Pipeline\CreateMeeting\CreateMeetingCommand;
use App\Domain\Customer\Application\Pipeline\DeleteCustomer\DeleteCustomerCommand;
use App\Domain\Customer\Application\Pipeline\UpdateCustomer\UpdateCustomerCommand;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\External\Google\GoogleCalendarService;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\CustomerType;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/customers', name: 'app_customer_')]
final class CustomerController extends AppController
{
    /**
     * @param iterable<PipelineHandlerInterface> $createHandlers
     * @param iterable<PipelineHandlerInterface> $updateHandlers
     * @param iterable<PipelineHandlerInterface> $deleteHandlers
     * @param iterable<PipelineHandlerInterface> $meetHandlers
     */
    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
        private readonly GoogleCalendarService $google,
        #[AutowireIterator('app.customer.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.customer.update')] private readonly iterable $updateHandlers,
        #[AutowireIterator('app.customer.delete')] private readonly iterable $deleteHandlers,
        #[AutowireIterator('app.customer.meet')] private readonly iterable $meetHandlers,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('views/customer/index.html.twig', [
            'customers' => $this->repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function new(Request $request): Response
    {
        $data = new CustomerData();
        $form = $this->createForm(CustomerType::class, $data, [
            'action' => $this->generateUrl('app_customer_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CreateCustomerCommand::fromData($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            $this->addFlash('success', 'Customer created successfully.');

            assert($command->result !== null);
            return $this->redirectToRoute('app_customer_show', ['id' => $command->result->getId()]);
        }

        return $this->render('views/customer/new.html.twig', [
            'form' => $form,
        ], new Response(
            null,
            $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '[0-9a-f-]{36}'], condition: "request.isMethod('POST') or request.headers.get('Turbo-Frame')")]
    public function edit(Customer $customer, Request $request): Response
    {
        $data = CustomerData::fromEntity($customer);
        $form = $this->createForm(CustomerType::class, $data, [
            'action' => $this->generateUrl('app_customer_edit', ['id' => $customer->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->updateHandlers)->run(new UpdateCustomerCommand($customer, $data));
            $this->addFlash('success', 'Customer updated successfully.');

            return $this->redirectToReferer($request, 'app_customer_show', ['id' => $customer->getId()]);
        }

        return $this->render('views/customer/edit.html.twig', [
            'form'     => $form,
            'customer' => $customer,
        ], new Response(
            null,
            $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        ));
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '[0-9a-f-]{36}'])]
    public function show(Customer $customer): Response
    {
        return $this->render('views/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/meet', name: 'meet', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function createMeet(Customer $customer): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getGoogleRefreshToken() === null) {
            return $this->redirect($this->google->getAuthUrl($customer->getId()));
        }

        $accessToken = $this->google->getAccessToken($user->getGoogleRefreshToken());
        $command     = new CreateMeetingCommand($customer, $user, $accessToken);
        new PipelineProcessor($this->meetHandlers)->run($command);

        $this->addFlash('success', 'Google Meet created' . ($command->meetUrl ? ': ' . $command->meetUrl : '') . '.');
        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '[0-9a-f-]{36}'], methods: ['POST'])]
    public function delete(Customer $customer, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_customer_' . $customer->getId(), (string) $request->request->get('_token'))) {
            new PipelineProcessor($this->deleteHandlers)->run(new DeleteCustomerCommand($customer));
            $this->addFlash('success', 'Customer deleted successfully.');
        }

        return $this->redirectToReferer($request, 'app_customer_index');
    }
}

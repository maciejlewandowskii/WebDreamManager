<?php

declare(strict_types=1);

namespace App\UI\Controller\Google;

use App\Domain\Customer\Application\Pipeline\CreateMeeting\CreateMeetingCommand;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Infrastructure\External\Google\GoogleCalendarService;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class GoogleAuthController extends AppController
{
    public function __construct(
        private readonly GoogleCalendarService $google,
        private readonly UserRepositoryInterface $userRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        #[AutowireIterator('app.customer.meet')] private readonly iterable $meetHandlers,
    ) {}

    #[Route('/auth/google/callback', name: 'app_google_auth_callback')]
    public function callback(Request $request): RedirectResponse
    {
        $code  = $request->query->get('code');
        $state = $request->query->get('state', '');

        if (!$code) {
            $this->addFlash('error', 'Google authorisation failed.');
            return $this->redirectToRoute('app_customer_index');
        }

        /** @var User $user */
        $user   = $this->getUser();
        $tokens = $this->google->exchangeCode((string) $code);

        if (isset($tokens['refresh_token']) && is_string($tokens['refresh_token'])) {
            $user->setGoogleRefreshToken($tokens['refresh_token']);
            $this->userRepository->save($user);
        }

        $customerId = $state ?: null;
        if ($customerId === null) {
            $this->addFlash('success', 'Google Calendar connected.');
            return $this->redirectToRoute('app_customer_index');
        }

        $customer = $this->customerRepository->findById((string) $customerId);
        if ($customer === null) {
            $this->addFlash('error', 'Customer not found.');
            return $this->redirectToRoute('app_customer_index');
        }

        $accessToken = $tokens['access_token'] ?? '';
        $command = new CreateMeetingCommand($customer, $user, is_string($accessToken) ? $accessToken : '');
        new PipelineProcessor($this->meetHandlers)->run($command);

        $this->addFlash('success', 'Google Meet created' . ($command->meetUrl ? ': ' . $command->meetUrl : '') . '.');
        return $this->redirectToRoute('app_customer_show', ['id' => $customer->getId()]);
    }
}

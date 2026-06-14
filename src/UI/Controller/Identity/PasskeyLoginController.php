<?php

declare(strict_types=1);

namespace App\UI\Controller\Identity;

use App\Domain\Identity\Repository\PasskeyCredentialRepositoryInterface;
use DateTimeImmutable;
use JsonException;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/passkey', name: 'app_passkey_')]
final class PasskeyLoginController extends AbstractController
{
    private const string WEBAUTHN_RP_NAME = 'WebDream Manager';

    public function __construct(
        private readonly PasskeyCredentialRepositoryInterface $passkeyRepository,
        private readonly Security $security,
        #[Autowire(env: 'WEBAUTHN_RP_ID')] private readonly string $webAuthnRpId,
    ) {}

    #[Route('/options', name: 'options', methods: ['GET'])]
    public function options(Request $request): Response
    {
        $webAuthn = $this->createWebAuthn();
        $args = $webAuthn->getGetArgs([], 60);

        $request->getSession()->set(
            'passkey_login_challenge',
            bin2hex($webAuthn->getChallenge()->getBinaryString()),
        );

        return $this->json($args);
    }

    #[Route('/authenticate', name: 'authenticate', methods: ['POST'])]
    public function authenticate(Request $request): Response
    {
        $challengeHex = $request->getSession()->get('passkey_login_challenge');
        if (!is_string($challengeHex)) {
            return $this->json(['error' => 'Session expired.'], 400);
        }

        try {
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->json(['error' => 'Invalid request.'], 400);
        }
        /** @var array<string, mixed> $body */

        $credentialId = $body['id'] ?? null;
        if (!is_string($credentialId) || $credentialId === '') {
            return $this->json(['error' => 'Missing credential ID.'], 400);
        }

        $credential = $this->passkeyRepository->findByCredentialId($credentialId);
        if ($credential === null) {
            return $this->json(['error' => 'Unknown credential.'], 400);
        }

        try {
            $webAuthn = $this->createWebAuthn();
            $response = is_array($body['response'] ?? null) ? $body['response'] : [];
            /** @var array<string, mixed> $response */
            $clientData  = base64_decode(is_string($response['clientDataJSON'] ?? null) ? $response['clientDataJSON'] : '');
            $authData    = base64_decode(is_string($response['authenticatorData'] ?? null) ? $response['authenticatorData'] : '');
            $signature   = base64_decode(is_string($response['signature'] ?? null) ? $response['signature'] : '');
            $challenge   = ByteBuffer::fromHex($challengeHex);

            $webAuthn->processGet(
                $clientData,
                $authData,
                $signature,
                $credential->getPublicKey(),
                $challenge,
                $credential->getSignCount(),
                false,
                true,
            );

            $newCount = $webAuthn->getSignatureCounter();
            if ($newCount !== null) {
                $credential->setSignCount($newCount);
            }
            $credential->setLastUsedAt(new DateTimeImmutable());
            $this->passkeyRepository->save($credential);

            $request->getSession()->remove('passkey_login_challenge');

            $this->security->login($credential->getUser(), 'security.authenticator.form_login.main', 'main');

            return $this->json(['ok' => true, 'redirect' => $this->generateUrl('app_dashboard')]);
        } catch (WebAuthnException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    private function createWebAuthn(): WebAuthn
    {
        return new WebAuthn(self::WEBAUTHN_RP_NAME, $this->webAuthnRpId, null, true);
    }
}

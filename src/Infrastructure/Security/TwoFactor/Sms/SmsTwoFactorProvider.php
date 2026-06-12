<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\TwoFactor\Sms;

use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

final readonly class SmsTwoFactorProvider implements TwoFactorProviderInterface
{
    public function __construct(
        private PersisterInterface $persister,
        private SmsCodeSender $sender,
        private TwoFactorFormRendererInterface $formRenderer,
    ) {}

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();

        return $user instanceof SmsTwoFactorInterface
            && $user->isSmsAuthEnabled()
            && $user->getSmsAuthRecipient() !== null;
    }

    public function needsPreparation(): bool
    {
        return true;
    }

    public function prepareAuthentication(object $user): void
    {
        if (!$user instanceof SmsTwoFactorInterface) {
            return;
        }

        $user->setSmsAuthCode((string) random_int(100000, 999999));
        $this->persister->persist($user);
        $this->sender->sendCode($user);
    }

    public function validateAuthenticationCode(object $user, string $authenticationCode): bool
    {
        if (!$user instanceof SmsTwoFactorInterface) {
            return false;
        }

        return $user->getSmsAuthCode() === str_replace(' ', '', $authenticationCode);
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}

<?php

declare(strict_types=1);

namespace App\UI\Controller\Identity;

use App\Domain\Identity\Application\Data\ChangePasswordData;
use App\Domain\Identity\Application\Data\ProfileData;
use App\Domain\Identity\Application\Data\WorkSettingsData;
use App\Domain\Identity\Application\Pipeline\ChangePassword\ChangePasswordCommand;
use App\Domain\Identity\Application\Pipeline\UpdateProfile\UpdateProfileCommand;
use App\Domain\Identity\Application\Pipeline\UpdateWorkSettings\UpdateWorkSettingsCommand;
use App\Domain\Identity\Entity\PasskeyCredential;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\PasskeyCredentialRepositoryInterface;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Notifications\Entity\NotificationChannelType;
use App\Domain\Notifications\Repository\NotificationRuleRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use App\UI\Controller\AppController;
use App\UI\Form\ChangePasswordType;
use App\UI\Form\ProfileType;
use App\UI\Form\WorkSettingsType;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use JsonException;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Throwable;

#[Route('/settings', name: 'app_settings')]
final class SettingsController extends AppController
{
    private const string WEBAUTHN_RP_NAME = 'WebDream Manager';

    public function __construct(
        private readonly PasskeyCredentialRepositoryInterface $passkeyRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SluggerInterface $slugger,
        private readonly TotpAuthenticator $totpAuthenticator,
        #[Autowire(param: 'uploads_dir')] private readonly string $uploadsDir,
        #[Autowire(env: 'WEBAUTHN_RP_ID')] private readonly string $webAuthnRpId,
        #[AutowireIterator('app.profile.update')] private readonly iterable $profileUpdateHandlers,
        #[AutowireIterator('app.work_settings.update')] private readonly iterable $workSettingsHandlers,
        #[AutowireIterator('app.password.change')] private readonly iterable $passwordChangeHandlers,
    ) {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('app_settings_profile');
    }

    #[Route('/profile', name: '_profile', methods: ['GET'])]
    public function profile(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, ProfileData::fromUser($user), [
            'action' => $this->generateUrl('app_settings_profile_save'),
        ]);

        return $this->render('views/settings/index.html.twig', [
            'section'      => 'profile',
            'profile_form' => $form,
        ]);
    }

    #[Route('/profile', name: '_profile_save', methods: ['POST'])]
    public function profileSave(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = ProfileData::fromUser($user);
        $form = $this->createForm(ProfileType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile !== null) {
                $ext      = $avatarFile->guessExtension() ?? 'bin';
                $filename = $this->slugger->slug($user->getId()) . '-' . uniqid('', true) . '.' . $ext;
                $avatarFile->move($this->uploadsDir . '/avatars', $filename);
                $user->setAvatarUrl('/uploads/avatars/' . $filename);
            }

            new PipelineProcessor($this->profileUpdateHandlers)->run(new UpdateProfileCommand($user, $data));
            $this->addFlash('success', 'Profile updated.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_settings_profile');
    }

    #[Route('/work', name: '_work', methods: ['GET'])]
    public function work(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(WorkSettingsType::class, WorkSettingsData::fromUser($user), [
            'action' => $this->generateUrl('app_settings_work_save'),
        ]);

        return $this->render('views/settings/index.html.twig', [
            'section'   => 'work',
            'work_form' => $form,
        ]);
    }

    #[Route('/work', name: '_work_save', methods: ['POST'])]
    public function workSave(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = WorkSettingsData::fromUser($user);
        $form = $this->createForm(WorkSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            new PipelineProcessor($this->workSettingsHandlers)->run(new UpdateWorkSettingsCommand($user, $data));
            $this->addFlash('success', 'Work preferences saved.');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_settings_work');
    }

    #[Route('/security', name: '_security', methods: ['GET'])]
    public function security(): Response
    {
        $form = $this->createForm(ChangePasswordType::class, new ChangePasswordData(), [
            'action' => $this->generateUrl('app_settings_security_save'),
        ]);

        return $this->render('views/settings/index.html.twig', [
            'section'       => 'security',
            'password_form' => $form,
        ]);
    }

    #[Route('/security', name: '_security_save', methods: ['POST'])]
    public function securitySave(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = new ChangePasswordData();
        $form = $this->createForm(ChangePasswordType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->passwordHasher->isPasswordValid($user, $data->currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
            } else {
                new PipelineProcessor($this->passwordChangeHandlers)->run(new ChangePasswordCommand($user, $data->newPassword));
                $this->addFlash('success', 'Password changed successfully.');
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_settings_security');
    }

    #[Route('/2fa', name: '_2fa', methods: ['GET'])]
    public function twoFactor(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $pendingTotpSecret = $request->getSession()->get('totp_pending_secret');
        $totpQrUri = null;
        if ($pendingTotpSecret !== null) {
            $totpQrUri = $this->generateTotpQrUri($user, $pendingTotpSecret);
        }

        return $this->render('views/settings/index.html.twig', [
            'section'             => '2fa',
            'totp_qr_uri'         => $totpQrUri,
            'totp_pending_secret' => $pendingTotpSecret,
        ]);
    }

    #[Route('/2fa/email', name: '_2fa_email', methods: ['POST'])]
    public function toggleEmail(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setEmailAuthEnabled(!$user->isEmailAuthEnabled());
        $this->userRepository->save($user);

        $state = $user->isEmailAuthEnabled() ? 'enabled' : 'disabled';
        $this->addFlash('success', "Email two-factor authentication $state.");

        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/2fa/totp/generate', name: '_2fa_totp_generate', methods: ['POST'])]
    public function totpGenerate(Request $request): RedirectResponse
    {
        $secret = $this->totpAuthenticator->generateSecret();
        $request->getSession()->set('totp_pending_secret', $secret);

        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/2fa/totp/enable', name: '_2fa_totp_enable', methods: ['POST'])]
    public function totpEnable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user   = $this->getUser();
        $secret = $request->getSession()->get('totp_pending_secret');
        $code   = $request->request->getString('totp_code');

        if ($secret === null) {
            $this->addFlash('error', 'Session expired. Please restart the setup.');
            return $this->redirectToRoute('app_settings_2fa');
        }

        $user->setTotpSecret($secret);
        $user->setTotpAuthEnabled(true);

        if (!$this->totpAuthenticator->checkCode($user, $code)) {
            $user->setTotpSecret(null);
            $user->setTotpAuthEnabled(false);
            $this->addFlash('error', 'Invalid verification code. Please try again.');
            return $this->redirectToRoute('app_settings_2fa');
        }

        $this->userRepository->save($user);
        $request->getSession()->remove('totp_pending_secret');
        $this->addFlash('success', 'Authenticator app set up successfully.');

        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/2fa/totp/disable', name: '_2fa_totp_disable', methods: ['POST'])]
    public function totpDisable(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setTotpSecret(null);
        $user->setTotpAuthEnabled(false);
        $this->userRepository->save($user);
        $this->addFlash('success', 'Authenticator app removed.');

        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/2fa/totp/cancel', name: '_2fa_totp_cancel', methods: ['POST'])]
    public function totpCancel(Request $request): RedirectResponse
    {
        $request->getSession()->remove('totp_pending_secret');
        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/2fa/sms', name: '_2fa_sms', methods: ['POST'])]
    public function toggleSms(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getPhone() === null) {
            $this->addFlash('error', 'Please add a phone number to your profile first.');
            return $this->redirectToRoute('app_settings_2fa');
        }

        $user->setSmsAuthEnabled(!$user->isSmsAuthEnabled());
        $this->userRepository->save($user);

        $state = $user->isSmsAuthEnabled() ? 'enabled' : 'disabled';
        $this->addFlash('success', "SMS two-factor authentication $state.");

        return $this->redirectToRoute('app_settings_2fa');
    }

    #[Route('/passkeys', name: '_passkeys', methods: ['GET'])]
    public function passkeys(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('views/settings/index.html.twig', [
            'section'  => 'passkeys',
            'passkeys' => $this->passkeyRepository->findByUser($user),
        ]);
    }

    #[Route('/passkeys/register/options', name: '_passkey_register_options', methods: ['GET'])]
    public function passkeyRegisterOptions(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $webAuthn = $this->createWebAuthn();

        $existingIds = array_filter(array_map(
            static function (PasskeyCredential $c): ?ByteBuffer {
                try {
                    return ByteBuffer::fromBase64Url($c->getCredentialId());
                } catch (WebAuthnException) {
                    return null;
                }
            },
            $this->passkeyRepository->findByUser($user),
        ));

        $args = $webAuthn->getCreateArgs(
            $user->getId(),
            $user->getEmail(),
            $user->getFullName(),
            60,
            'preferred',
            true,
            null,
            $existingIds,
        );

        $challengeBin = $webAuthn->getChallenge()->getBinaryString();
        $request->getSession()->set('passkey_challenge', bin2hex($challengeBin));

        return $this->json($args);
    }

    #[Route('/passkeys/register', name: '_passkey_register', methods: ['POST'])]
    public function passkeyRegister(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $challengeHex = $request->getSession()->get('passkey_challenge');
        if ($challengeHex === null) {
            return $this->json(['error' => 'Session expired.'], 400);
        }

        try {
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->json(['error' => 'Invalid request.'], 400);
        }
        if (!is_array($body)) {
            return $this->json(['error' => 'Invalid request.'], 400);
        }

        try {
            $webAuthn   = $this->createWebAuthn();
            $clientData = base64_decode($body['response']['clientDataJSON'] ?? '');
            $attObj     = base64_decode($body['response']['attestationObject'] ?? '');
            $challenge  = ByteBuffer::fromHex($challengeHex);

            $data = $webAuthn->processCreate($clientData, $attObj, $challenge, false, true, false);

            $credentialId = rtrim(strtr(base64_encode($data->credentialId), '+/', '-_'), '=');
            $publicKey    = $data->credentialPublicKey;
            $signCount    = $data->signatureCounter ?? 0;
            $name         = trim($body['name'] ?? '') ?: 'Passkey';

            $credential = new PasskeyCredential($user, $credentialId, $publicKey, $signCount, $name);
            $this->passkeyRepository->save($credential);
            $request->getSession()->remove('passkey_challenge');

            return $this->json(['ok' => true]);
        } catch (WebAuthnException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/passkeys/{id}/delete', name: '_passkey_delete', methods: ['POST'])]
    public function passkeyDelete(string $id): RedirectResponse
    {
        /** @var User $user */
        $user       = $this->getUser();
        $credential = $this->passkeyRepository->findById($id);

        if ($credential !== null && $credential->getUser() === $user) {
            $this->passkeyRepository->remove($credential);
            $this->addFlash('success', 'Passkey removed.');
        }

        return $this->redirectToRoute('app_settings_passkeys');
    }

    #[Route('/notifications', name: '_notifications', methods: ['GET'])]
    public function notifications(NotificationRuleRepositoryInterface $ruleRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $rules = $ruleRepository->findAll();
        $preferences = $user->getNotificationPreferences() ?? [];

        return $this->render('views/settings/index.html.twig', [
            'section'     => 'notifications',
            'rules'       => $rules,
            'preferences' => $preferences,
            'channels'    => NotificationChannelType::cases(),
        ]);
    }

    #[Route('/notifications', name: '_notifications_save', methods: ['POST'])]
    public function notificationsSave(
        Request $request,
        NotificationRuleRepositoryInterface $ruleRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $rules = $ruleRepository->findAll();

        $preferences = [];
        $postedPrefs = $request->request->all('prefs');

        foreach ($rules as $rule) {
            $eventName = $rule->getEventName();
            $enabledChannels = [];

            if (isset($postedPrefs[$eventName]) && is_array($postedPrefs[$eventName])) {
                foreach ($postedPrefs[$eventName] as $channelStr) {
                    if (is_string($channelStr)) {
                        $channel = NotificationChannelType::tryFrom($channelStr);
                        if ($channel !== null && $rule->hasChannel($channel)) {
                            $enabledChannels[] = $channel->value;
                        }
                    }
                }
            }
            $preferences[$eventName] = $enabledChannels;
        }

        $user->setNotificationPreferences($preferences);
        $this->userRepository->save($user);

        $this->addFlash('success', 'Notification preferences updated.');

        return $this->noContentResponse();
    }

    private function createWebAuthn(): WebAuthn
    {
        return new WebAuthn(self::WEBAUTHN_RP_NAME, $this->webAuthnRpId, null, true);
    }

    private function generateTotpQrUri(User $user, string $pendingSecret): string
    {
        $original = $user->getTotpSecret();
        $user->setTotpSecret($pendingSecret);

        $qrContent = $this->totpAuthenticator->getQRContent($user);

        $user->setTotpSecret($original);

        try {
            $result = new Builder(
                writer: new SvgWriter(),
                data: $qrContent,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 200,
                margin: 0,
                foregroundColor: new Color(231, 228, 236),
                backgroundColor: new Color(0, 0, 0, 127),
                logoPath: __DIR__ . '/../../../../assets/images/logo.png',
                logoResizeToWidth: 40,
                logoPunchoutBackground: true,
            );

            return $result->build()->getDataUri();
        } catch (Throwable $e) {
            $this->addFlash('error', 'Failed to generate TOTP QR code: ' . $e->getMessage());

            return '';
        }
    }
}

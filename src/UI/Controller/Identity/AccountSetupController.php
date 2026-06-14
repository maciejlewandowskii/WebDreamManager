<?php

declare(strict_types=1);

namespace App\UI\Controller\Identity;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account/setup/{token}', name: 'app_account_setup')]
final class AccountSetupController extends AbstractController
{
    private const string SESSION_KEY = 'account_setup_step';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function __invoke(string $token, Request $request): Response
    {
        $user = $this->userRepository->findBySetupToken($token);

        if ($user === null || !$user->isSetupTokenValid()) {
            return $this->render('views/identity/account_setup_invalid.html.twig');
        }

        $session = $request->getSession();
        $sessionStep = $session->get(self::SESSION_KEY . '_' . substr($token, 0, 8), 1);
        $step = is_int($sessionStep) ? $sessionStep : 1;

        if (!$request->isMethod('POST')) {
            return $this->render('views/identity/account_setup.html.twig', [
                'step'   => $step,
                'user'   => $user,
                'token'  => $token,
                'errors' => [],
            ]);
        }

        $errors = [];

        if ($step === 1) {
            /** @var UploadedFile|null $file */
            $file = $request->files->get('avatar');
            if ($file !== null && $file->isValid()) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    $errors['avatar'] = 'Upload directory could not be created.';
                } else {
                    $ext = $file->guessExtension() ?? 'jpg';
                    $hex = bin2hex(random_bytes(8));
                    $filename = substr($hex, 0, 16) . '.' . $ext;
                    $file->move($uploadDir, $filename);
                    $user->setAvatarUrl('/uploads/avatars/' . $filename);
                    $this->userRepository->save($user);
                }
            }
        } elseif ($step === 2) {
            $fullName = trim((string) $request->request->get('fullName', ''));
            $phone = trim((string) $request->request->get('phone', ''));
            if ($phone === '') {
                $phone = null;
            }

            if (mb_strlen($fullName) < 2) {
                $errors['fullName'] = 'Full name must be at least 2 characters.';
            }
            if ($phone !== null && mb_strlen($phone) > 50) {
                $errors['phone'] = 'Phone number cannot exceed 50 characters.';
            }

            if (empty($errors)) {
                $user->setFullName($fullName);
                $user->setPhone($phone);
                $this->userRepository->save($user);
            }
        } elseif ($step === 3) {
            $password = (string) $request->request->get('password', '');
            $confirm = (string) $request->request->get('confirmPassword', '');
            if (mb_strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $errors['confirmPassword'] = 'Passwords do not match.';
            } else {
                $user->setPassword($this->hasher->hashPassword($user, $password));
                $user->clearSetupToken();
                $this->userRepository->save($user);
                $session->remove(self::SESSION_KEY . '_' . substr($token, 0, 8));
                $this->addFlash('success', 'Your account is ready — sign in to get started.');

                return $this->redirectToRoute('app_identity_login');
            }
        }

        if (!empty($errors)) {
            return $this->render('views/identity/account_setup.html.twig', [
                'step'   => $step,
                'user'   => $user,
                'token'  => $token,
                'errors' => $errors,
            ]);
        }

        $nextStep = min($step + 1, 3);
        $session->set(self::SESSION_KEY . '_' . substr($token, 0, 8), $nextStep);

        return $this->redirectToRoute('app_account_setup', ['token' => $token]);
    }
}

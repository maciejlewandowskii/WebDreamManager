<?php

declare(strict_types=1);

namespace App\UI\Controller\Identity;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TwoFactorController extends AbstractController
{
    #[Route('/2fa', name: 'app_2fa_login')]
    public function form(): Response
    {
        return $this->render('identity/two_factor/form.html.twig');
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SessionController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route('/login/{_locale}', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED')) {
            return $this->redirect('/'); // send already logged-in users to root
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error !== null) {
            $this->addFlash('danger', $this->translator->trans('login_failed'));
        }

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // empty on purpose logout is configured in security.yaml but needs this route config
    }
}

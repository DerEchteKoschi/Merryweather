<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_user');
    }

    #[Route('/user', name: 'app_admin_user')]
    public function user(): Response
    {
        return $this->render('admin/user.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/distribution', name: 'app_admin_distribution')]
    public function distribution(): Response
    {
        return $this->render('admin/distribution.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}

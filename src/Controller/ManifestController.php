<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManifestController extends AbstractController
{
    #[Route('/manifest.{_locale}.json', name: 'app_manifest_loc')]
    #[Route('/manifest.json', name: 'app_manifest')]
    public function index(): Response
    {
        return $this->render('manifest.json.twig', [
        ], new JsonResponse());
    }
}

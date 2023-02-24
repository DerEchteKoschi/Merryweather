<?php

namespace App\Controller\Admin;

use App\Merryweather\Admin\LogMessage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/admin/{_locale}')]
class LogsController extends AbstractDashboardController
{
    public function __construct(
        private string $logpath
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/logs', name: 'admin_logs')]
    public function logs(Request $request, AdminUrlGenerator $adminUrlGenerator): Response
    {
        [$logfiles, $logs] = $this->loadLog($request, $adminUrlGenerator);

        return $this->render('admin/logs.html.twig', [
            'logs' => $logs,
            'logfiles' => $logfiles,
        ]);
    }


    /**
     * @param Request           $request
     * @param AdminUrlGenerator $adminUrlGenerator
     * @return mixed[]
     */
    protected function loadLog(Request $request, AdminUrlGenerator $adminUrlGenerator): array
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, new ReflectionExtractor())];

        $serializer = new Serializer($normalizers, $encoders);

        $logfiles = [];
        $glob = glob($this->logpath . '/*.log');
        $glob = array_reverse($glob);
        $logs = [];
        $active = '#';
        $first = true;
        if ($request->query->has('log')) {
            $first = false;
            $active = $request->query->get('log');
        }
        foreach ($glob as $file) {
            $pi = pathinfo($file);
            $logfiles[] = [
                'name' => $pi['basename'],
                'url' => $adminUrlGenerator->setRoute('admin_logs')->set('log', $pi['basename'])->generateUrl(),
                'active' => $active === $pi['basename'] || $first
            ];
            if ($active === $pi['basename'] || $first) {
                foreach (file($file) as $line) {
                    $logs[] = $serializer->deserialize($line, LogMessage::class, 'json');
                }
                $active = '#';
            }
            $first = false;
        }

        return [$logfiles, $logs];
    }
}

<?php

namespace App\Controller\Admin;

use App\MerryWeather\AppConfig as DashboardCfg;
use App\Repository\AppConfigRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ConfigController extends AbstractDashboardController
{
    public function __construct(
        private DashboardCfg $dashboardConfig,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/config', name: 'admin_config')]
    public function config(Request $request, DashboardCfg $appConfig, AppConfigRepository $configRepository): Response
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $requestCfg = $request->request->all('cfg');
            foreach (DashboardCfg::CONFIG_KEYS as $key => $value) {
                if (isset($requestCfg[$key])) {
                    $this->dashboardConfig->setConfigValue($key, $requestCfg[$key]);
                } else {
                    $this->dashboardConfig->setConfigValue($key, 'off');
                }
            }
            $this->addFlash('success', 'Einstellungen gespeichert');
        }
        $data = [];
        foreach (DashboardCfg::CONFIG_KEYS as $key => $value) {
            $data[$key] = ['name' => $value, 'type' => DashboardCfg::CONFIG_DEFINITION[$key][DashboardCfg::TYPE], 'value' => $this->dashboardConfig->getConfigValue($key)];
        }

        return $this->render('admin/config.html.twig', [
            'config' => $data
        ]);
    }
}

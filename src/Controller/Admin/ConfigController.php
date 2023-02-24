<?php

namespace App\Controller\Admin;

use App\Merryweather\AppConfig as DashboardCfg;
use App\Repository\AppConfigRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}')]
class ConfigController extends AbstractDashboardController
{
    public function __construct(
        private DashboardCfg $dashboardConfig,
        private TranslatorInterface $translator
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
            foreach (DashboardCfg::CONFIG_DEFINITION as $key => $value) {
                if (isset($requestCfg[$key])) {
                    $this->dashboardConfig->setConfigValue($key, $requestCfg[$key]);
                } else {
                    $this->dashboardConfig->setConfigValue($key, 'off');
                }
            }
            $this->addFlash('success', $this->translator->trans('config_saved'));
        }
        $data = [];
        foreach (DashboardCfg::CONFIG_DEFINITION as $key => $value) {
            $data[$key] = ['type' => DashboardCfg::CONFIG_DEFINITION[$key][DashboardCfg::TYPE], 'value' => $this->dashboardConfig->getConfigValue($key)];
        }

        return $this->render('admin/config.html.twig', [
            'config' => $data
        ]);
    }
}

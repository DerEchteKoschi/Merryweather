<?php

namespace App\Controller\Admin;

use App\Merryweather\AppConfig;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}')]
class ConfigController extends AbstractDashboardController
{
    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/config', name: 'admin_config')]
    public function config(Request $request): Response
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $requestCfg = $request->request->all('cfg');
            foreach (AppConfig::CONFIG_DEFINITION as $key => $value) {
                if (isset($requestCfg[$key])) {
                    $this->appConfig->setConfigValue($key, $requestCfg[$key]);
                } else {
                    $this->appConfig->setConfigValue($key, 'off');
                }
            }
            $this->addFlash('success', $this->translator->trans('config_saved'));
        }
        $data = [];
        foreach (AppConfig::CONFIG_DEFINITION as $key => $value) {
            $data[$key] = ['type' => AppConfig::CONFIG_DEFINITION[$key][AppConfig::TYPE], 'value' => $this->appConfig->getConfigValue($key)];
        }

        return $this->render('admin/config.html.twig', [
            'config' => $data
        ]);
    }
}

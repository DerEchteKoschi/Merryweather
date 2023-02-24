<?php

namespace App\Controller\Admin;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/{_locale}')]
class Deploy2FAController extends AbstractDashboardController
{
    public function __construct(private readonly string $kernelSecret, private readonly TranslatorInterface $translator, private readonly bool $poorMansDeploymentActive = false)
    {
    }

    /**
     * @throws \Exception
     */
    #[Route('/2fa', name: 'admin_2fa')]
    public function twofa(Request $request): Response
    {
        if ($this->poorMansDeploymentActive) {
            $result = (new QRCode(new QROptions([
                'outputType' => QRCode::OUTPUT_MARKUP_SVG
            ])))->render(sprintf('otpauth://totp/Deployment?secret=%s&issuer=MerryWeather', preg_replace('/[^2-7A-Z]/', "", strtoupper($this->kernelSecret))));

            return $this->render('admin/2fa.html.twig', [
                'qrcode' => $result
            ]);
        }
        return new Response($this->translator->trans('feature_deactivated'));
    }
}

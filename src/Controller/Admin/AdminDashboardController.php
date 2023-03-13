<?php

namespace App\Controller\Admin;

use App\Entity\Crontab;
use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Merryweather\Admin\Month;
use App\Merryweather\AppConfig;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/admin/{_locale}')]
class AdminDashboardController extends AbstractDashboardController
{
    private int $slotCount;
    private int $userCount;
    private int $distCount;

    /**
     * @param UserRepository         $userRepository
     * @param SlotRepository         $slotRepository
     * @param DistributionRepository $distributionRepository
     * @param AppConfig              $appConfig
     * @param string                 $appTitle
     * @param string[]               $supportedLocales
     * @param bool                   $poorMansDeploymentActive
     *                                                        @codeCoverageIgnore
     */
    public function __construct(
        UserRepository $userRepository,
        SlotRepository $slotRepository,
        private readonly DistributionRepository $distributionRepository,
        private readonly AppConfig $appConfig,
        private readonly string $appTitle,
        private readonly array $supportedLocales,
        private readonly bool $poorMansDeploymentActive = false
    ) {
        $this->slotCount = $slotRepository->count([]);
        $this->userCount = $userRepository->count([]);
        $this->distCount = $distributionRepository->count([]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function configureActions(): Actions
    {
        return parent::configureActions()->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    /**
     * @codeCoverageIgnore
     */
    public function configureAssets(): Assets
    {
        return parent::configureAssets()->addCssFile('css/app.css');
    }

    /**
     * @codeCoverageIgnore
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
                        ->setTitle($this->appTitle)->setFaviconPath('/favicon.ico')->setLocales($this->supportedLocales)->disableDarkMode()->generateRelativeUrls();
    }

    /**
     * @codeCoverageIgnore
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl(new TranslatableMessage('back_to_app'), 'fa fa-home', $this->generateUrl('app_slots'));
        yield MenuItem::linkToDashboard(new TranslatableMessage('calendar'), 'fa fa-table-columns');
        yield MenuItem::section(new TranslatableMessage('distributions'));
        yield MenuItem::linkToCrud(new TranslatableMessage('new_distribution'), 'fa fa-cart-plus', Distribution::class)->setAction('new');
        yield MenuItem::linkToCrud(new TranslatableMessage('distributions'), 'fa fa-cart-shopping', Distribution::class)->setBadge($this->distCount);
        yield MenuItem::linkToCrud(new TranslatableMessage('slots'), 'fa fa-table-list', Slot::class)->setBadge($this->slotCount);
        yield MenuItem::section(new TranslatableMessage('system'));
        yield MenuItem::linkToCrud(new TranslatableMessage('add_user'), 'fa fa-user-plus', User::class)->setAction('new');
        yield MenuItem::linkToCrud(new TranslatableMessage('users'), 'fa fa-users', User::class)->setBadge($this->userCount);
        if ($this->appConfig->isCronActive()) {
            yield MenuItem::linkToCrud(new TranslatableMessage('cron_jobs'), 'fa fa-clock', Crontab::class);
        }
        yield MenuItem::linkToRoute(new TranslatableMessage('configurations'), 'fa fa-wrench', 'admin_config');
        yield MenuItem::linkToRoute(new TranslatableMessage('logs'), 'fa fa-list-ul', 'admin_logs');
        if ($this->poorMansDeploymentActive) {
            yield MenuItem::linkToRoute(new TranslatableMessage('deployment_2fa'), 'fa fa-qrcode', 'admin_2fa');
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)->addMenuItems([
            MenuItem::linkToUrl(new TranslatableMessage('my_profile'), 'fas fa-user', $this->generateUrl('app_profile'))
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        $months = [];
        $date = new \DateTimeImmutable('first day of this month');
        $currentMonth = $this->distributionRepository->findDistributionsOfMonth((int)$date->format('n'), (int)$date->format('Y'));

        for ($i = 0; $i < $this->appConfig->getMonthCount(); $i++) {
            $months[] = new Month($i, $currentMonth);
            $date = $date->add(new \DateInterval('P1M'));
            $currentMonth = $this->distributionRepository->findDistributionsOfMonth((int)$date->format('n'), (int)$date->format('Y'));
        }

        return $this->render('admin/dashboard.html.twig', [
            'months' => $months
        ]);
    }
}

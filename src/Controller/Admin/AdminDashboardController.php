<?php

namespace App\Controller\Admin;

use App\Entity\Crontab;
use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\MerryWeather\Admin\Month;
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

class AdminDashboardController extends AbstractDashboardController
{
    private int $slotCount;
    private int $userCount;
    private int $distCount;

    public function __construct(UserRepository $userRepository, SlotRepository $slotRepository, private DistributionRepository $distributionRepository)
    {
        $this->slotCount = $slotRepository->count([]);
        $this->userCount = $userRepository->count([]);
        $this->distCount = $distributionRepository->count([]);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()->addCssFile('css/app.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
                        ->setTitle('MerryWeather')->setFaviconPath('/favicon.ico')->setLocales(['de']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Zurück zur Anwendung', 'fa fa-home', $this->generateUrl('app_slots'));
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-table-columns');
        yield MenuItem::section('Verteilungen');
        yield MenuItem::linkToCrud('neue Verteilung', 'fa fa-cart-plus', Distribution::class)->setAction('new');
        yield MenuItem::linkToCrud('Verteilungen', 'fa fa-cart-shopping', Distribution::class)->setBadge($this->distCount);
        yield MenuItem::linkToCrud('Slots', 'fa fa-table-list', Slot::class)->setBadge($this->slotCount);
        yield MenuItem::section('System');
        yield MenuItem::linkToCrud('Benutzer hinzufügen', 'fa fa-user-plus', User::class)->setAction('new');
        yield MenuItem::linkToCrud('Benutzer Liste', 'fa fa-users', User::class)->setBadge($this->userCount);
        yield MenuItem::linkToCrud('Cron jobs', 'fa fa-clock', Crontab::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)->addMenuItems([
            MenuItem::linkToUrl('My Profile', 'fas fa-user', $this->generateUrl('app_profile'))
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $months = [];
        $date = new \DateTimeImmutable('first day of this month');
        $currentMonth = $this->distributionRepository->findDistributionsOfMonth((int)$date->format('n'), (int)$date->format('Y'));

        for ($i = 0; $i < 3; $i++) {
            $months[] = new Month($i, $currentMonth);
            $date = $date->add(new \DateInterval('P1M'));
            $currentMonth = $this->distributionRepository->findDistributionsOfMonth((int)$date->format('n'), (int)$date->format('Y'));
        }

        return $this->render('admin/dashboard.html.twig', [
            'months' => $months
        ]);
    }
}

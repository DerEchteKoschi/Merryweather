<?php

namespace App\Command;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'Create:Distribution',
    description: 'create a distiribution in DB',
)]
class CreateDistributionCommand extends Command
{

    public function __construct(
        private readonly DistributionRepository $distributionRepository,
        private readonly SlotRepository $slotRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = $io->ask('Datum', date('d.m.Y'));
        $duration = $io->ask('Tage vorher buchbar', 6);
        $activeTill = new DateTimeImmutable($date, new DateTimeZone('Europe/Berlin'));
        $activeFrom = $activeTill->sub(new DateInterval('P' . $duration . 'D'));

        $title = $io->ask('Description', sprintf('Verteilung [%s] buchbar ab [%s]', $activeTill->format('d.m.Y'), $activeFrom->format('d.m.Y H:i:s')));

        $dist = new Distribution();
        $dist->setText($title);
        $dist->setActiveFrom($activeFrom);
        $dist->setActiveTill($activeTill);
        $this->distributionRepository->save($dist, true);

        $from =$io->ask('Verteilung ab:', '17:00');
        $startTime = new DateTimeImmutable($from);
        $till = $io->ask('Verteilung bis:', '19:30');
        $size = $io->ask('Slotgröße in Minuten:', '10');

        $targetTime = new DateTimeImmutable($till);

        while ($startTime < $targetTime) {
            $slot = new Slot();
            $slot->setStartAt($startTime);
            $slot->setText($dist->getText() . ': Slot ' . $startTime->format('H:i'));
            $startTime = $startTime->add(new DateInterval('PT' . $size . 'M'));
            $slot->setDistribution($dist);
            $this->slotRepository->save($slot);
        }
        $this->slotRepository->flush();


        return Command::SUCCESS;
    }

}

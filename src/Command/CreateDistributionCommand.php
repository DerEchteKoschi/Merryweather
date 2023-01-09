<?php

namespace App\Command;

use App\Entity\Distribution;
use App\Entity\Slot;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = $io->ask('Datum', date('d.m.Y'));
        $duration = $io->ask('Tage vorher buchbar', 6);
        $activeTill = new \DateTimeImmutable($date, new \DateTimeZone('Europe/Berlin'));
        $activeFrom = $activeTill->sub(new \DateInterval('P' . $duration . 'D'));

        $title = $io->ask('Description', sprintf('Verteilung [%s] buchbar ab [%s]', $activeTill->format('d.m.Y'), $activeFrom->format('d.m.Y H:i:s')));
        $from =$io->ask('Verteilung ab:', '17:00');
        $starTime = new \DateTimeImmutable($from);

        $dist = new Distribution();
        $dist->setText($title);
        $dist->setActiveFrom($activeFrom);
        $dist->setActiveTill($activeTill);
        $dist->setStartAt($starTime);
        $this->distributionRepository->save($dist, true);

        $till = $io->ask('Verteilung bis:', '19:30');
        $size = $io->ask('Slotgröße in Minuten:', '10');

        $targetTime = new \DateTimeImmutable($till);

        $current = $starTime;
        $seq = 1;
        while ($current < $targetTime) {
            $current = $current->add(new \DateInterval('PT' . $size . 'M'));
            $slot = new Slot();
            $slot->setSequence($seq);
            $slot->setText($dist->getText() . ': Slot ' . $seq);
            $seq++;
            $slot->setDistribution($dist);
            $this->slotRepository->save($slot);
        }
        $this->slotRepository->flush();


        return Command::SUCCESS;
    }

}

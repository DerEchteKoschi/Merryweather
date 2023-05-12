<?php

namespace App\DataFixtures;

use App\Entity\Distribution;
use App\Entity\Slot;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DistributionFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->createDistribution($manager, 'yesterday', 'tomorrow');
        $this->createDistribution($manager, 'yesterday', 'tomorrow +1day');
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function createDistribution(ObjectManager $manager, $from, $till): void
    {
        $dist = new Distribution();
        $dist->setActiveTill(new DateTimeImmutable($till));
        $dist->setActiveFrom(new DateTimeImmutable($from));
        $dist->setText('test');

        $startTime = new DateTimeImmutable('00:00');

        $manager->persist($dist);
        for ($i = 0; $i < 48; $i++) {
            $slot = new Slot();
            $slot->setText('slot #' . $i);
            $slot->setStartAt($startTime);
            $slot->setDistribution($dist);
            $slot->setUser($this->getReference('user' . (($i % 3)+1)));
            $startTime = $startTime->add(new DateInterval('PT30M'));
            $dist->addSlot($slot);
            $manager->persist($slot);
        }
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}

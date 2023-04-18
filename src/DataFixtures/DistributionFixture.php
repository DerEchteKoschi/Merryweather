<?php

namespace App\DataFixtures;

use App\Entity\Distribution;
use App\Entity\Slot;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DistributionFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $dist = new Distribution();
        $dist->setActiveTill(new DateTimeImmutable('tomorrow'));
        $dist->setActiveFrom(new DateTimeImmutable('yesterday'));
        $dist->setText('test');

        $startTime = new DateTimeImmutable('00:00');

        $manager->persist($dist);
        for ($i = 0; $i < 48; $i++) {
            $slot = new Slot();
            $slot->setText('slot #'.$i);
            $slot->setStartAt($startTime);
            $slot->setDistribution($dist);
            $startTime = $startTime->add(new DateInterval('PT30M'));
            $dist->addSlot($slot);
            $manager->persist($slot);
        }
        $manager->flush();
    }
}

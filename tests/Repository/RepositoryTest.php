<?php

namespace Repository;

use App\Entity\AppConfig;
use App\Entity\Crontab;
use App\Entity\Distribution;
use App\Entity\Slot;
use App\Entity\User;
use App\Repository\AppConfigRepository;
use App\Repository\CrontabRepository;
use App\Repository\DistributionRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\Persisters\SqlValueVisitor;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class RepositoryTest extends TestCase
{
    public function repositories(): \Generator
    {
        yield [AppConfigRepository::class, AppConfig::class];
        yield [CrontabRepository::class, Crontab::class];
        yield [DistributionRepository::class, Distribution::class];
        yield [SlotRepository::class, Slot::class];
        yield [UserRepository::class, User::class];
    }

    /**
     * @dataProvider repositories
     * @param $repoClass
     * @param $entityClass
     */
    public function testRepositoraBaseFunc($repoClass, $entityClass)
    {
        $entity = new $entityClass();
        $cmMock = $this->createMock(ClassMetadata::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->exactly(2))->method('persist')->with($this->equalTo($entity));
        $emMock->expects($this->exactly(2))->method('remove')->with($this->equalTo($entity));
        $emMock->expects($this->exactly(2))->method('flush');
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->method('getManagerForClass')->willReturn($emMock);
        $emMock->method('getClassMetadata')->willReturn($cmMock);
        $repo = new $repoClass($managerRegistryMock);
        $repo->save($entity);
        $repo->save($entity, true);
        $repo->remove($entity);
        $repo->remove($entity, true);
        $this->assertTrue(true);
    }

    public function testSlotRepositorySpecifics()
    {
        $slot = new Slot();

        $cmMock = $this->createMock(ClassMetadata::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())->method('lock')->with($this->equalTo($slot), $this->equalTo(LockMode::OPTIMISTIC), $this->equalTo(1));
        $emMock->expects($this->once())->method('flush');
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->method('getManagerForClass')->willReturn($emMock);
        $emMock->method('getClassMetadata')->willReturn($cmMock);

        $slotRepo = new SlotRepository($managerRegistryMock);
        $slotRepo->flush();
        $slotRepo->lock($slot);
    }

    public function testUserRepositorySpecifics()
    {
        $user = new User();
        $user->setPhone('123');


        $epMock = $this->createMock(EntityPersister::class);
        $epMock->expects($this->once())->method('load')->with($this->equalTo(['phone' => '123']))->willReturn($user);
        $uoWMock = $this->createMock(UnitOfWork::class);
        $uoWMock->expects($this->once())->method('getEntityPersister')->willReturn($epMock);
        $cmMock = $this->createMock(ClassMetadata::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())->method('isOpen')->willReturn(true);
        $emMock->expects($this->once())->method('persist')->with($this->equalTo($user));
        $emMock->expects($this->once())->method('flush');
        $emMock->expects($this->once())->method('getUnitOfWork')->willReturn($uoWMock);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->method('getManagerForClass')->willReturn($emMock);
        $emMock->method('getClassMetadata')->willReturn($cmMock);

        $userRepository = new UserRepository($managerRegistryMock);
        $this->assertTrue($userRepository->isEntityManagerOpen());
        $this->assertEquals($user, $userRepository->loadUserByIdentifier('123'));
        $userRepository->upgradePassword($user, 'emil');
        $paui = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $this->expectException(UnsupportedUserException::class);
        $userRepository->upgradePassword($paui, 'emil');
    }

    public function testDistributionSpecificsMonth()
    {
        $epMock = $this->createMock(EntityPersister::class);
        $epMock->expects($this->once())->method('loadCriteria')->willReturnCallback(function ($crit) {
            $v = new SqlValueVisitor();
            $crit->getWhereExpression()->visit($v);
            $critPaT = $v->getParamsAndTypes();
            $this->assertNotEmpty($critPaT);
            $this->assertCount(2, $critPaT);
            $this->assertCount(2, $critPaT[0]);
            $this->assertInstanceOf(\DateTimeImmutable::class, $critPaT[0][0]);
            $this->assertInstanceOf(\DateTimeImmutable::class, $critPaT[0][1]);
            $this->assertCount(2, $critPaT[1]);
            $this->assertEquals('active_till', $critPaT[1][0][0]);
            $this->assertEquals('<', $critPaT[1][0][2]);
            $this->assertEquals('active_till', $critPaT[1][1][0]);
            $this->assertEquals('>', $critPaT[1][1][2]);

            return [];
        });
        $uoWMock = $this->createMock(UnitOfWork::class);
        $uoWMock->expects($this->once())->method('getEntityPersister')->willReturn($epMock);
        $cmMock = $this->createMock(ClassMetadata::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->method('getManagerForClass')->willReturn($emMock);
        $emMock->method('getClassMetadata')->willReturn($cmMock);
        $emMock->expects($this->once())->method('getUnitOfWork')->willReturn($uoWMock);
        $distributionRepo = new DistributionRepository($managerRegistryMock);
        $distributionRepo->findDistributionsOfMonth(1, 2023);
    }

    public function testDistributionSpecificsCurrent()
    {
        $epMock = $this->createMock(EntityPersister::class);
        $epMock->expects($this->once())->method('loadCriteria')->willReturnCallback(function ($crit) {
            $v = new SqlValueVisitor();
            $crit->getWhereExpression()->visit($v);
            $critPaT = $v->getParamsAndTypes();
            $this->assertNotEmpty($critPaT);
            $this->assertCount(2, $critPaT);
            $this->assertCount(2, $critPaT[0]);
            $this->assertInstanceOf(\DateTimeImmutable::class, $critPaT[0][0]);
            $this->assertInstanceOf(\DateTimeImmutable::class, $critPaT[0][1]);
            $this->assertCount(2, $critPaT[1]);
            $this->assertEquals('active_from', $critPaT[1][0][0]);
            $this->assertEquals('<=', $critPaT[1][0][2]);
            $this->assertEquals('active_till', $critPaT[1][1][0]);
            $this->assertEquals('>=', $critPaT[1][1][2]);

            return [];
        });
        $uoWMock = $this->createMock(UnitOfWork::class);
        $uoWMock->expects($this->once())->method('getEntityPersister')->willReturn($epMock);
        $cmMock = $this->createMock(ClassMetadata::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->method('getManagerForClass')->willReturn($emMock);
        $emMock->method('getClassMetadata')->willReturn($cmMock);
        $emMock->expects($this->once())->method('getUnitOfWork')->willReturn($uoWMock);
        $distributionRepo = new DistributionRepository($managerRegistryMock);
        $distributionRepo->findCurrentDistributions();
    }


}

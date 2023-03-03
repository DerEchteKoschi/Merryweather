<?php

namespace App\Repository;

use App\Entity\Distribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Distribution>
 *
 * @method Distribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Distribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Distribution[]    findAll()
 * @method Distribution[]    findBy(array|Criteria $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Distribution::class);
    }

    /**
     * @return Distribution[]
     */
    public function findCurrentDistributions(): array
    {
        $crit = Criteria::create();
        $crit->where(Criteria::expr()->lte('active_from', new \DateTimeImmutable('today')));
        $crit->andWhere(Criteria::expr()->gte('active_till', new \DateTimeImmutable('today')));
        return $this->findByCriteria($crit);
    }

    /**
     * @return Distribution[]
     * @throws \Exception
     */
    public function findDistributionsOfMonth(int $month, int $year): array
    {
        $date = new \DateTimeImmutable(sprintf('1.%d.%d', $month, $year));

        $crit = Criteria::create();
        $crit->where(Criteria::expr()->lt('active_till', $date->add(new \DateInterval('P1M'))));
        $crit->andWhere(Criteria::expr()->gt('active_till', $date->sub(new \DateInterval('P1D'))));
        return $this->findByCriteria($crit);
    }

    public function remove(Distribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Criteria $criteria
     * @return Distribution[]
     */
    private function findByCriteria(Criteria $criteria): array
    {
        $persister = $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->getEntityName());

        return $persister->loadCriteria($criteria);
    }

    public function save(Distribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

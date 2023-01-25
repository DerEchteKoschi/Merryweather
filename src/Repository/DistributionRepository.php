<?php

namespace App\Repository;

use App\Entity\Distribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Distribution>
 *
 * @method Distribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Distribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Distribution[]    findAll()
 * @method Distribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
        $qb = $this->createQueryBuilder('dist')
                   ->where('dist.active_from <= :now')
                   ->andWhere('dist.active_till >= :now')
                   ->setParameter('now', new \DateTimeImmutable('now'));

        return $qb->getQuery()->execute();
    }

    /**
     * @throws \Exception
     */
    public function findDistributionsOfMonth(int $month, int $year): array
    {
        $date = new \DateTimeImmutable(sprintf('1.%d.%d', $month, $year));

        $qb = $this->createQueryBuilder('dist')
                   ->where('dist.active_till < :nextmonth')
                   ->andWhere('dist.active_till > :prevmonth')
                   ->setParameter('nextmonth', $date->add(new \DateInterval('P1M')))
                   ->setParameter('prevmonth', $date->sub(new \DateInterval('P1D')));

        return $qb->getQuery()->execute();
    }

    public function remove(Distribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(Distribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Distribution[] Returns an array of Distribution objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Distribution
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

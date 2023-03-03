<?php

namespace App\Repository;

use App\Entity\Crontab;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crontab>
 *
 * @method Crontab|null find($id, $lockMode = null, $lockVersion = null)
 * @method Crontab|null findOneBy(array $criteria, array $orderBy = null)
 * @method Crontab[]    findAll()
 * @method Crontab[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrontabRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crontab::class);
    }

    public function save(Crontab $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Crontab $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

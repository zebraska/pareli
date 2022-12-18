<?php

namespace App\Repository;

use App\Entity\PlanningWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlanningWeek>
 *
 * @method PlanningWeek|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanningWeek|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanningWeek[]    findAll()
 * @method PlanningWeek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanningWeekRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanningWeek::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PlanningWeek $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(PlanningWeek $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return PlanningWeek[] Returns an array of PlanningWeek objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlanningWeek
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

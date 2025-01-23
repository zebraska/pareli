<?php

namespace App\Repository;

use App\Entity\PlanningLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlanningLine>
 *
 * @method PlanningLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanningLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanningLine[]    findAll()
 * @method PlanningLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanningLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanningLine::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PlanningLine $entity, bool $flush = true): void
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
    public function remove(PlanningLine $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
    * @return PlanningLine[] Returns an array of PlanningLine objects
    */
    public function findForPlanning($pWeek, $day, $filter)
    {
        $qb = $this->createQueryBuilder('p');
        if($filter==0){
            $qb = $qb->andWhere("p.attachment = 'Vertou' OR p.attachment = 'Tous'");
        }
        else if($filter==1){
            $qb = $qb->andWhere("p.attachment = 'Saint-Nazaire' OR p.attachment = 'Tous'");
        }
        
        return $qb->andWhere('p.day = :day')
        ->setParameter('day', $day)
        ->andWhere('p.planningWeek = :planningWeek')
        ->setParameter('planningWeek', $pWeek)
        ->getQuery()
        ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?PlanningLine
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

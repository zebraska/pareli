<?php

namespace App\Repository;

use App\Entity\PlanningWeek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\Planning\Manager;

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
    
    public function getAllByDateInterval(\DateTime $dateStart, \DateTime $dateEnd)
    {
        $years = Manager::getAllYearsBetweenDates($dateStart, $dateEnd);
        $WeekNumberStart = $dateStart->format('W');
        $WeekNumberEnd = $dateEnd->format('W');
        if (count($years) === 1){
            $qb = $this->createQueryBuilder('p')
                    ->where('p.year = :year')
                    ->andWhere('p.number BETWEEN :numberStart AND :numberEnd')
                    ->setParameter('year', $years[0])
                    ->setParameter('numberStart', $WeekNumberStart)
                    ->setParameter('numberEnd', $WeekNumberEnd);
                    
        } else {
            $qb = $this->createQueryBuilder('p')
                    ->where('p.year = :yearS AND p.number >= :numberS')
                    ->orWhere('p.year = :yearE AND p.number <= :numberE')
                    ->setParameter('yearS', $years[0])
                    ->setParameter('numberS', $WeekNumberStart)
                    ->setParameter('yearE', end($years))
                    ->setParameter('numberE', $WeekNumberEnd);
            if (count($years) > 2) {
               foreach ($years as $key => $year){
                   if ($key !== 0 && $key !== array_key_last($years)){
                       $qb = $qb->orWhere('p.year = :yearB'.$key)
                               ->setParameter('yearB'.$key, $year);
                   }
               }
            }
        }
                $qb = $qb->orderBy('p.mondayDate', 'ASC');
                return $qb->getQuery();
        
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

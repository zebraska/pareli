<?php

namespace App\Repository;

use App\Entity\RemovalContainerQuantity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RemovalContainerQuantity>
 *
 * @method RemovalContainerQuantity|null find($id, $lockMode = null, $lockVersion = null)
 * @method RemovalContainerQuantity|null findOneBy(array $criteria, array $orderBy = null)
 * @method RemovalContainerQuantity[]    findAll()
 * @method RemovalContainerQuantity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemovalContainerQuantityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemovalContainerQuantity::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(RemovalContainerQuantity $entity, bool $flush = true): void
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
    public function remove(RemovalContainerQuantity $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return RemovalContainerQuantity[] Returns an array of RemovalContainerQuantity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RemovalContainerQuantity
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\Recycler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recycler>
 *
 * @method Recycler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recycler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recycler[]    findAll()
 * @method Recycler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecyclerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recycler::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Recycler $entity, bool $flush = true): void
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
    public function remove(Recycler $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    
    /**
     * @return Query Return dql query for the main vue
     */
    public function getPaginationMainQuery(String $search = '', String $filter = ''): Query
    {
        $qb = $this->createQueryBuilder('r');
        if ($search!='') {
            $qb = $qb->andWhere('r.name LIKE :search OR r.city LIKE :search')->setParameter('search', '%'.$search.'%');
        }

        return $qb->orderBy('r.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery();
    }

    // /**
    //  * @return Recycler[] Returns an array of Recycler objects
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
    public function findOneBySomeField($value): ?Recycler
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

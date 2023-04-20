<?php

namespace App\Repository;

use App\Entity\Removal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Removal>
 *
 * @method Removal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Removal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Removal[]    findAll()
 * @method Removal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Removal::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Removal $entity, bool $flush = true): void
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
    public function remove(Removal $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Removal[] Return dql query for the main vue
     */
    public function getPaginationMainQuery(String $search = '', String $filter = '', string $filterattach = ''): Query
    {
        $qb = $this->createQueryBuilder('r')->join('r.provider', 'p');
        if ($search != '') {
            $qb = $qb->andWhere('p.name LIKE :search OR p.city LIKE :search')->setParameter('search', '%' . $search . '%');
        }

        if ($filter == '1') {
            $qb = $qb->andWhere('r.state=0');
        } elseif ($filter == '2') {
            $qb = $qb->andWhere('r.state=1');
        } elseif ($filter == '3') {
            $qb = $qb->andWhere('r.state=2');
        }

        if ($filterattach == '1') {
            $qb = $qb->andWhere('p.attachment = :attach')->setParameter('attach', 'Vertou');
        } elseif ($filterattach == '2') {
            $qb = $qb->andWhere('p.attachment=:attach')->setParameter('attach', 'Saint-Nazaire');
        }

        return $qb->orderBy('r.dateRequest', 'ASC')
            ->setMaxResults(10)
            ->getQuery();
    }

    public function getLastRemovalByProvider(int $providerId, int $nbremoval): Query
    {
        $qb = $this->createQueryBuilder('r')->join('r.provider', 'p')
            ->where('p.id = :providerId')->setParameter('providerId', $providerId)
            ->orderBy('r.dateRequest', 'DESC')
            ->setMaxResults($nbremoval);

        return $qb->getQuery();
    }

    public function getAllRemovalsByInterval(\DateTime $dateStart, \DateTime $dateEnd, String $filter = '')
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.dateCreate BETWEEN :dateStart AND :dateEnd')
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->orderBy('r.dateCreate', 'DESC');

        if ($filter == '1') {
            $qb = $qb->andWhere('r.state=0');
        } elseif ($filter == '2') {
            $qb = $qb->andWhere('r.state=1');
        } elseif ($filter == '3') {
            $qb = $qb->andWhere('r.state=2');
        }

        return $qb->getQuery();
    }
    // /**
    //  * @return Removal[] Returns an array of Removal objects
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
    public function findOneBySomeField($value): ?Removal
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

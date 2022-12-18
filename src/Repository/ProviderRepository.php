<?php

namespace App\Repository;

use App\Entity\Provider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Provider>
 *
 * @method Provider|null find($id, $lockMode = null, $lockVersion = null)
 * @method Provider|null findOneBy(array $criteria, array $orderBy = null)
 * @method Provider[]    findAll()
 * @method Provider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Provider::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Provider $entity, bool $flush = true): void
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
    public function remove(Provider $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Provider[] Return dql query for the main vue
     */
    public function getPaginationMainQuery(String $search = '', String $filter = ''): Query
    {
        $qb = $this->createQueryBuilder('p');
        if ($search!='') {
            $qb = $qb->andWhere('p.name LIKE :search OR p.city LIKE :search')->setParameter('search', '%'.$search.'%');
        }

        if ($filter=='1') {
            $qb = $qb->andWhere('p.containersQuantitys is empty');
        }

        return $qb->orderBy('p.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery();
    }
}

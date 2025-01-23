<?php

namespace App\Repository;

use App\Entity\Delivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Delivery>
 *
 * @method Delivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Delivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Delivery[]    findAll()
 * @method Delivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Delivery $entity, bool $flush = true): void
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
    public function remove(Delivery $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Query Return dql query for the main vue
     */
    public function getPaginationMainQuery(String $search = '', String $filter = '', string $filterattach = ''): Query
    {
        $qb = $this->createQueryBuilder('d')->join('d.recycler', 'r');
        if ($search != '') {
            $qb = $qb->andWhere('r.name LIKE :search OR r.city LIKE :search')->setParameter('search', '%' . $search . '%');
        }

        if ($filter == '1') {
            $qb = $qb->andWhere('d.state=0');
        } elseif ($filter == '2') {
            $qb = $qb->andWhere('d.state=1');
        } elseif ($filter == '3') {
            $qb = $qb->andWhere('d.state=2');
        }

        if ($filterattach == '1') {
            $qb = $qb->andWhere('r.attachment = :attach')->setParameter('attach', 'Vertou');
        } elseif ($filterattach == '2') {
            $qb = $qb->andWhere('r.attachment=:attach')->setParameter('attach', 'Saint-Nazaire');
        }

        return $qb->orderBy('d.dateCreate', 'DESC')
            ->setMaxResults(10)
            ->getQuery();
    }


    public function getAllDeliverysByInterval(\DateTime $dateStart, \DateTime $dateEnd, String $filter = '')
    {
        $qb = $this->createQueryBuilder('d')
            ->where('(d.state = 0 AND (d.dateCreate BETWEEN :dateStart AND :dateEnd)) OR (d.state != 0 AND (d.datePlanified BETWEEN :dateStart AND :dateEnd))')
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->addOrderBy('d.datePlanified', 'DESC')
            ->addOrderBy('d.dateCreate', 'DESC');

        if ($filter == '1') {
            $qb = $qb->andWhere('d.state=0');
        } elseif ($filter == '2') {
            $qb = $qb->andWhere('d.state=1');
        } elseif ($filter == '3') {
            $qb = $qb->andWhere('d.state=2');
        }

        return $qb->getQuery();
    }

    public function getPlanningSelection(String $attachment = null): array
    {
        $qb = $this->createQueryBuilder('d')->join('d.recycler', 'r');

        if ($attachment) {
            $qb = $qb
                ->andWhere('r.attachment = :attachment')
                ->setParameter('attachment', $attachment);
        }

        return $qb->andWhere('d.state = 0')
            ->getQuery()
            ->getResult();
    }
}

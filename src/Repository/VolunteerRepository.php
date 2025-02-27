<?php

namespace App\Repository;

use App\Entity\Volunteer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Volunteer>
 *
 * @method Volunteer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Volunteer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Volunteer[]    findAll()
 * @method Volunteer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Volunteer::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Volunteer $entity, bool $flush = true): void
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
    public function remove(Volunteer $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findVolunteersForSelection($attachment = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->orderBy('v.lastname', 'ASC');

        if ($attachment) {
            $qb = $qb->andWhere('v.attachment = :attachment')
                ->setParameter('attachment', $attachment);
        }
        return $qb->andWhere('v.enable = true')
            ->getQuery()
            ->getResult();
    }

    public function findDriversForSelection(string $attachment = null)
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.type = :valPL OR v.type = :valVL')
            ->setParameter('valPL', 'PL')
            ->setParameter('valVL', 'VL');

        if ($attachment) {
            $qb = $qb->andWhere('v.attachment = :attachment')
                ->setParameter('attachment', $attachment);
        }

        return $qb->andWhere('v.enable = 1')
            ->orderBy('v.lastname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findHgvDriversForSelection(string $attachment = null)
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.type = :valPL')
            ->setParameter('valPL', 'PL');

        if ($attachment) {
            $qb = $qb->andWhere('v.attachment = :attachment')
                ->setParameter('attachment', $attachment);
        }

        return $qb->andWhere('v.enable = 1')
            ->orderBy('v.lastname', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function getVolunteerByName(String $attachment, String $search = '', String $type = '')
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.enable = true');

        if ($attachment != "Tous") {
            $qb = $qb->andWhere('v.attachment = :attachment')
            ->setParameter('attachment', $attachment);
        }
        
        if ($type === 'driver') {
            $qb = $qb->andWhere('v.type = :valPL OR v.type = :valVL')
                ->setParameter('valPL', 'PL')
                ->setParameter('valVL', 'VL');
        }
        if ($type === 'hgvDriver') {
            $qb = $qb->andWhere('v.type = :valPL')
                ->setParameter('valPL', 'PL');
        }
        if ($search != '') {
            $searchArray = explode(' ', $search);
            foreach ($searchArray as $searchElement) {
                if ($searchElement !== ' ') {
                    $qb = $qb->andWhere('v.firstname LIKE :search OR v.lastname LIKE :search')->setParameter('search', '%' . $searchElement . '%');
                }
            }
        }
        return $qb->orderBy('v.lastname', 'ASC')->getQuery();
    }

    /*
    public function findOneBySomeField($value): ?Volunteer
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

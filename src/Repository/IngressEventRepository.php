<?php

namespace App\Repository;

use App\Entity\IngressEvent;
use App\Helper\Paginator\PaginatorOptions;
use App\Helper\Paginator\PaginatorRepoTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IngressEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method IngressEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method IngressEvent[]    findAll()
 * @method IngressEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngressEventRepository extends ServiceEntityRepository
{
    use PaginatorRepoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngressEvent::class);
    }

    public function findFutureEvents()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return IngressEvent[]
     */
    public function findFutureFS()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->andWhere('i.type = :type')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->setParameter('type', 'fs')
            ->orderBy('i.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return IngressEvent[]
     */
    public function findFutureMD()
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date_start >= :val')
            ->andWhere('i.type = :type')
            ->setParameter('val', (new \DateTime())->format('Y-m-d)'))
            ->setParameter('type', 'md')
            ->orderBy('i.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findAllByDate()
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.date_start', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getPaginatedList(PaginatorOptions $options): Paginator
    {
        $query = $this->createQueryBuilder('e');

        $query->orderBy('e.'.$options->getOrder(), $options->getOrderDir());

        $query = $query->getQuery();

        return $this->paginate($query, $options->getPage(), $options->getLimit());
    }
}

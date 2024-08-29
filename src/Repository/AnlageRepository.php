<?php

namespace App\Repository;

use App\Entity\Anlage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Anlage>
 *
 * @method Anlage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anlage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anlage[]    findAll()
 * @method Anlage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnlageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anlage::class);
    }

//    /**
//     * @return Anlage[] Returns an array of Anlage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Anlage
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}

<?php

namespace App\Repository\School\Exam\Operation\Period\Module;

use App\Entity\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MarkPeriodModuleCalculationRelation>
 *
 * @method MarkPeriodModuleCalculationRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarkPeriodModuleCalculationRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarkPeriodModuleCalculationRelation[]    findAll()
 * @method MarkPeriodModuleCalculationRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkPeriodModuleCalculationRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkPeriodModuleCalculationRelation::class);
    }

    public function save(MarkPeriodModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MarkPeriodModuleCalculationRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MarkPeriodModuleCalculationRelation[] Returns an array of MarkPeriodModuleCalculationRelation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MarkPeriodModuleCalculationRelation
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

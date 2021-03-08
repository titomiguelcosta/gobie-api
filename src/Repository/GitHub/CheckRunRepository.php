<?php

namespace App\Repository\GitHub;

use App\Entity\GitHub\CheckRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheckRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckRun[]    findAll()
 * @method CheckRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckRun::class);
    }
}

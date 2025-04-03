<?php

namespace App\Repository;

use App\Entity\Tache;
use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }
    // src/Repository/TacheRepository.php
public function findByProject(Projet $projet)
{
    return $this->createQueryBuilder('t')
        ->andWhere('t.projet = :projet')
        ->setParameter('projet', $projet)
        ->getQuery()
        ->getResult();
}
    // Add custom methods as needed
}
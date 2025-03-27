<?php

namespace App\Repository;

use App\Entity\Equipe_employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Equipe_employeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe_employe::class);
    }

    // Add custom methods as needed
}
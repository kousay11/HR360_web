<?php

namespace App\Repository;

use App\Entity\Projet_equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Projet_equipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet_equipe::class);
    }

    // Add custom methods as needed
}
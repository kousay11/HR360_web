<?php

namespace App\Repository;

use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    // Add custom methods as needed
}
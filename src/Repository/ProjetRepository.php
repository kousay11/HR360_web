<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }
    public function search(string $query): array
{
    return $this->createQueryBuilder('p')
        ->where('p.nom LIKE :query')
        ->orWhere('p.description LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->getQuery()
        ->getResult();
}
    // Add custom methods as needed
}
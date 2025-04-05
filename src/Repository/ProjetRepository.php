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
        ->setParameter('query', '%'.$query.'%')
        ->getQuery()
        ->getResult();
}
public function prioritize(): array
{
    return $this->createQueryBuilder('t')
        ->orderBy('t.dateFin', 'ASC')
        ->getQuery()
        ->getResult();
}

    // Add custom methods as needed
}
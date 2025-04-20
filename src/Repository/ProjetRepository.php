<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Utilisateur;
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
public function searchFront(string $query,Utilisateur $userid): array
{
    return $this->createQueryBuilder('p')
        ->innerJoin('p.projetequipes', 'pe')
        ->innerJoin('pe.equipe', 'e')
        ->innerJoin('e.equipeemployes', 'ee')
        ->where('ee.utilisateur = :userId')
        ->Andwhere('p.nom LIKE :query')
        ->setParameter('userId', $userid)
        ->setParameter('query', '%'.$query.'%')
        ->getQuery()
        ->getResult();
}
public function prioritizeFront(Utilisateur $userid): array
{
    return $this->createQueryBuilder('t')
    ->innerJoin('t.projetequipes', 'pe')
        ->innerJoin('pe.equipe', 'e')
        ->innerJoin('e.equipeemployes', 'ee')
        ->where('ee.utilisateur = :userId')
        ->setParameter('userId', $userid)
        ->orderBy('t.dateFin', 'ASC')
        ->getQuery()
        ->getResult();
}
    // Add custom methods as needed
}
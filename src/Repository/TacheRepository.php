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
public function search(string $query): array
{
    return $this->createQueryBuilder('t')
        ->where('t.nom LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->getQuery()
        ->getResult();
}

public function searchByProject(Projet $projet, string $query): array
{
    return $this->createQueryBuilder('t')
        ->where('t.projet = :projet')
        ->andWhere('(t.nom LIKE :query)')
        ->setParameter('projet', $projet)
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

public function prioritizeByProject(Projet $projet): array
{
    return $this->createQueryBuilder('t')
        ->where('t.projet = :projet')
        ->setParameter('projet', $projet)
        ->orderBy('t.dateFin', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findAllForExport(?Projet $projet = null): array
{
    $qb = $this->createQueryBuilder('t')
        ->orderBy('t.statut', 'ASC')
        ->addOrderBy('t.dateFin', 'ASC');

    if ($projet) {
        $qb->andWhere('t.projet = :projet')
           ->setParameter('projet', $projet);
    }

    return $qb->getQuery()->getResult();
}
}
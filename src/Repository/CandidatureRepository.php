<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    // Add custom methods as needed
    // src/Repository/CandidatureRepository.php
public function findAllWithOffre()
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.offre', 'o')
        ->addSelect('o')
        ->orderBy('c.dateCandidature', 'DESC')
        ->getQuery()
        ->getResult();
}
// src/Repository/CandidatureRepository.php

public function findByStatut(string $statut)
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.offre', 'o')
        ->addSelect('o')
        ->where('c.statut = :statut')
        ->setParameter('statut', $statut)
        ->orderBy('c.dateCandidature', 'DESC')
        ->getQuery()
        ->getResult();
}
}
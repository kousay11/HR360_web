<?php

// UtilisateurRepository.php
namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function searchEmployees(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'Employe');

        // Filtre par recherche textuelle
        if (!empty($criteria['search'])) {
            $queryBuilder
                ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.poste LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        // Filtre par salaire minimum
        if (!empty($criteria['min_salary'])) {
            $queryBuilder
                ->andWhere('u.salaire >= :min_salary')
                ->setParameter('min_salary', $criteria['min_salary']);
        }

        // Filtre par salaire maximum
        if (!empty($criteria['max_salary'])) {
            $queryBuilder
                ->andWhere('u.salaire <= :max_salary')
                ->setParameter('max_salary', $criteria['max_salary']);
        }

        // Filtre par poste
        if (!empty($criteria['poste'])) {
            $queryBuilder
                ->andWhere('u.poste = :poste')
                ->setParameter('poste', $criteria['poste']);
        }

        // Tri
        $sortField = $criteria['sort'] ?? 'nom';
        $sortDirection = $criteria['direction'] ?? 'ASC';
        
        $queryBuilder->orderBy('u.' . $sortField, $sortDirection);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findDistinctPostes(): array
    {
        return $this->createQueryBuilder('u')
            ->select('DISTINCT u.poste')
            ->where('u.role = :role')
            ->setParameter('role', 'Employe')
            ->andWhere('u.poste IS NOT NULL')
            ->orderBy('u.poste', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
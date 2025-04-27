<?php

namespace App\Repository;

use App\Entity\Ressource;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RessourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressource::class);
    }

    public function findAvailableSimilarResources(array $excludedIds, Ressource $referenceResource, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
{
    // Récupérer les IDs des ressources déjà réservées pendant la période
    $unavailableResourceIds = $this->getEntityManager()
        ->getRepository(Reservation::class)
        ->findUnavailableResourceIds($startDate, $endDate);

    // Ajouter les IDs des ressources exclues à la liste des ressources indisponibles
    $excludedIds = array_merge($excludedIds, $unavailableResourceIds);

    // Création de la requête pour trouver les ressources disponibles et similaires
    $qb = $this->createQueryBuilder('r')
        ->where('r.id NOT IN (:excluded)')
        ->andWhere('r.type = :type') // même type que la ressource initiale
        ->andWhere('r.etat = :etat') // seulement les ressources disponibles
        ->setParameter('excluded', $excludedIds)
        ->setParameter('type', $referenceResource->getType())
        ->setParameter('etat', 'disponible') // Filtre sur les ressources disponibles
        ->setMaxResults(5); // Limiter à 5 suggestions
    
    return $qb->getQuery()->getResult();
}



public function findPotentiallySimilar(Ressource $ressource, int $maxResults = 20): array
{
    $qb = $this->createQueryBuilder('r')
        ->where('r.type = :type')
        ->andWhere('r.id != :id')
        ->andWhere('r.prix BETWEEN :minPrice AND :maxPrice')
        ->setParameter('type', $ressource->getType())
        ->setParameter('id', $ressource->getId())
        ->setParameter('minPrice', $ressource->getPrix() * 0.7)
        ->setParameter('maxPrice', $ressource->getPrix() * 1.3)
        ->orderBy('ABS(r.prix - :price)', 'ASC')
        ->setParameter('price', $ressource->getPrix())
        ->setMaxResults($maxResults);

    return $qb->getQuery()->getResult();
}


// Dans App\Repository\RessourceRepository.php
public function findLatestPopular(int $maxResults): array
{
    return $this->createQueryBuilder('r')
        ->orderBy('r.createdAt', 'DESC')
        ->setMaxResults($maxResults)
        ->getQuery()
        ->getResult();
}
    


public function findWithFilters(?string $search, ?string $type, ?float $maxPrice, bool $availableOnly, ?string $sort): array
{
    $qb = $this->createQueryBuilder('r');

    if ($search) {
        $qb->andWhere('LOWER(r.nom) LIKE :search')
            ->setParameter('search', '%' . strtolower($search) . '%');
    }

    if ($type) {
        $qb->andWhere('r.type = :type')
            ->setParameter('type', $type);
    }

    if ($maxPrice) {
        $qb->andWhere('r.prix <= :maxPrice')
            ->setParameter('maxPrice', $maxPrice);
    }

    if ($availableOnly) {
        $qb->andWhere('r.etat = :etat')
            ->setParameter('etat', 'Disponible');
    }

    if ($sort === 'asc') {
        $qb->orderBy('r.prix', 'ASC');
    } elseif ($sort === 'desc') {
        $qb->orderBy('r.prix', 'DESC');
    } else {
        $qb->orderBy('r.nom', 'ASC'); // tri par défaut
    }

    return $qb->getQuery()->getResult();
}




}
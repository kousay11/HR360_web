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

    
}
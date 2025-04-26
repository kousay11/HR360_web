<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findUnavailableResourceIds(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.ressource)')
            ->where('r.datedebut <= :endDate')
            ->andWhere('r.datefin >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return array_column($qb->getQuery()->getResult(), 1); // renvoie les IDs de ressources réservées
    }

}
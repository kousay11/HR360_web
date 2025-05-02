<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Ressource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Utilisateur;
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

// Modifier les déclarations de méthodes pour utiliser l'entité
public function findUpcoming(Utilisateur $user, \DateTimeInterface $now): array
{
    return $this->createQueryBuilder('r')
        ->where('r.utilisateur = :user')
        ->andWhere('r.datedebut > :now')
        ->setParameter('user', $user)
        ->setParameter('now', $now)
        ->orderBy('r.datedebut', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findActive(Utilisateur $user, \DateTimeInterface $now): array
{
    return $this->createQueryBuilder('r')
        ->where('r.utilisateur = :user')
        ->andWhere('r.datedebut <= :now')
        ->andWhere('r.datefin >= :now')
        ->setParameter('user', $user)
        ->setParameter('now', $now)
        ->orderBy('r.datefin', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findCompleted(Utilisateur $user, \DateTimeInterface $now): array
{
    return $this->createQueryBuilder('r')
        ->where('r.utilisateur = :user')
        ->andWhere('r.datefin < :now')
        ->setParameter('user', $user)
        ->setParameter('now', $now)
        ->orderBy('r.datefin', 'DESC')
        ->getQuery()
        ->getResult();
}

public function getUserStats(Utilisateur $user): array
{
    $reservations = $this->findBy(['utilisateur' => $user]);
    
    $stats = [
        'total' => count($reservations),
        'average_duration' => 0,
    ];

    $totalDays = 0;
    foreach ($reservations as $reservation) {
        $totalDays += $reservation->getDatefin()->diff(
            $reservation->getDatedebut()
        )->days;
    }

    if ($stats['total'] > 0) {
        $stats['average_duration'] = round($totalDays / $stats['total'], 1);
    }

    return $stats;
}



public function countReservationsByPeriod(string $period = 'week'): array
{
    $conn = $this->getEntityManager()->getConnection();

    $format = $period === 'month' ? '%Y-%m' : '%Y-%u';

    $sql = "
        SELECT DATE_FORMAT(date_debut, :format) AS period, COUNT(id) AS count
        FROM reservation
        GROUP BY period
        ORDER BY period DESC
    ";

    $stmt = $conn->prepare($sql);
    $result = $stmt->executeQuery(['format' => $format]);

    return $result->fetchAllAssociative();
}



public function getMostRequestedResource(): ?array
{
    return $this->createQueryBuilder('r')
        ->select('res.nom as resourceName, COUNT(r.id) as reservationCount')
        ->join('r.ressource', 'res')
        ->groupBy('res.id')
        ->orderBy('reservationCount', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}


// src/Repository/ReservationRepository.php

public function findDatesByRessourceId(Ressource $ressourceId): string
{
    $results = $this->createQueryBuilder('r')
        ->select('r.datedebut, r.datefin')
        ->where('r.ressource = :id_ressource')
        ->setParameter('id_ressource', $ressourceId)
        ->orderBy('r.datedebut', 'ASC')
        ->getQuery()
        ->getResult();

    $formattedDates = [];

    foreach ($results as $row) {
        $start = $row['datedebut']->format('Y-m-d');
        $end = $row['datefin']->format('Y-m-d');
        $formattedDates[] = "$start vers $end\n";

    }

    return implode(', ', $formattedDates);
}



}
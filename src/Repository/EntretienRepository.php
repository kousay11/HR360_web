<?php

namespace App\Repository;

use App\Entity\Entretien;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntretienRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entretien::class);
    }

    // Add custom methods as needed


    public function findByLocalisation(int $idCandidature, ?string $localisation = null): array
{
    $qb = $this->createQueryBuilder('e')
        ->andWhere('e.candidature = :candidature')
        ->setParameter('candidature', $idCandidature);

    if ($localisation !== null && $localisation !== '') {
        $qb->andWhere('LOWER(e.localisation) LIKE LOWER(:localisation)')
            ->setParameter('localisation', '%' . $localisation . '%');
    }

    return $qb
        ->orderBy('e.date', 'ASC')
        ->addOrderBy('e.heure', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findByType(int $idCandidature, ?string $type = null, string $order = 'ASC'): array
{
    $qb = $this->createQueryBuilder('e')
        ->andWhere('e.candidature = :candidature')
        ->setParameter('candidature', $idCandidature);

    if ($type !== null && $type !== '') {
        $qb->andWhere('e.type = :type')
            ->setParameter('type', $type);
    }

    return $qb
        ->orderBy('e.date', $order)
        ->addOrderBy('e.heure', $order)
        ->getQuery()
        ->getResult();
}

public function findAllOrderedByDateAndTime(int $idCandidature, string $order = 'ASC'): array
{
    return $this->createQueryBuilder('e')
        ->andWhere('e.candidature = :candidature')
        ->setParameter('candidature', $idCandidature)
        ->orderBy('e.date', $order)
        ->addOrderBy('e.heure', $order)
        ->getQuery()
        ->getResult();
}

public function findEntretiensInNext24Hours(): array
{
    $now = new \DateTime();
    $in24Hours = (new \DateTime())->add(new \DateInterval('PT24H'));

    return $this->createQueryBuilder('e')
        ->where('e.date BETWEEN :now AND :in24Hours')
        ->andWhere('e.heure IS NOT NULL')
        ->setParameter('now', $now)
        ->setParameter('in24Hours', $in24Hours)
        ->getQuery()
        ->getResult();
}



}
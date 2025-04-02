<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    // Add custom methods as needed
    public function updateExpiredStatus(): void
{
    $now = new \DateTime();
    $qb = $this->createQueryBuilder('o');
    
    $qb->update()
       ->set('o.statut', ':expiredStatus')
       ->where('o.dateExpiration < :now')
       ->andWhere('o.statut != :expiredStatus')
       ->setParameter('expiredStatus', 'Expirée')
       ->setParameter('now', $now)
       ->getQuery()
       ->execute();
}
}
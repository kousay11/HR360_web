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
// Dans src/Repository/OffreRepository.php

public function findWithSearchAndSort(?string $searchTerm = null, string $sort = 'date_expiration_asc')
{
    $qb = $this->createQueryBuilder('o');

    if ($searchTerm) {
        $qb->andWhere('o.titre LIKE :searchTerm')
           ->setParameter('searchTerm', '%'.$searchTerm.'%');
    }

    // Gestion du tri
    switch ($sort) {
        case 'titre_asc':
            $qb->orderBy('o.titre', 'ASC');
            break;
        case 'titre_desc':
            $qb->orderBy('o.titre', 'DESC');
            break;
        case 'date_expiration_asc':
            $qb->orderBy('o.dateExpiration', 'ASC');
            break;
        case 'date_expiration_desc':
            $qb->orderBy('o.dateExpiration', 'DESC');
            break;
        default:
            $qb->orderBy('o.dateExpiration', 'ASC');
    }

    return $qb->getQuery()->getResult();
}
}
<?php

// FormationRepository.php
namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function searchFormations(array $criteria = [], bool $paginate = true, int $page = 1, int $limit = 5)
    {
        $queryBuilder = $this->createQueryBuilder('f');

        // Filtre par recherche textuelle
        if (!empty($criteria['search'])) {
            $queryBuilder
                ->andWhere('f.titre LIKE :search OR f.description LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        // Filtre par durée minimum
        if (!empty($criteria['min_duration'])) {
            $queryBuilder
                ->andWhere('f.duree >= :min_duration')
                ->setParameter('min_duration', $criteria['min_duration']);
        }

        // Filtre par durée maximum
        if (!empty($criteria['max_duration'])) {
            $queryBuilder
                ->andWhere('f.duree <= :max_duration')
                ->setParameter('max_duration', $criteria['max_duration']);
        }

        // Filtre par date
        if (!empty($criteria['date_from'])) {
            $queryBuilder
                ->andWhere('f.dateFormation >= :date_from')
                ->setParameter('date_from', new \DateTime($criteria['date_from']));
        }

        if (!empty($criteria['date_to'])) {
            $queryBuilder
                ->andWhere('f.dateFormation <= :date_to')
                ->setParameter('date_to', new \DateTime($criteria['date_to']));
        }

        // Tri
        $sortField = $criteria['sort'] ?? 'titre';
        $sortDirection = $criteria['direction'] ?? 'ASC';

        $queryBuilder->orderBy('f.' . $sortField, $sortDirection);

        $query = $queryBuilder->getQuery();

        if ($paginate) {
            $total = count($query->getResult());
            $query
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            return [
                'results' => $query->getResult(),
                'total' => $total
            ];
        }

        return $query->getResult();
    }


    public function searchFrontFormations(array $criteria = [], bool $paginate = true, int $page = 1, int $limit = 3)
    {
        $queryBuilder = $this->createQueryBuilder('f');

        // Filtre par recherche textuelle
        if (!empty($criteria['search'])) {
            $queryBuilder
                ->andWhere('f.titre LIKE :search OR f.description LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        // Filtre par favoris
        if (!empty($criteria['favorites'])) {
            $queryBuilder
                ->andWhere('f.isFavorite = :isFavorite')
                ->setParameter('isFavorite', true);
        }

        // Filtre par durée
        if (!empty($criteria['min_duration'])) {
            $queryBuilder
                ->andWhere('f.duree >= :min_duration')
                ->setParameter('min_duration', $criteria['min_duration']);
        }

        if (!empty($criteria['max_duration'])) {
            $queryBuilder
                ->andWhere('f.duree <= :max_duration')
                ->setParameter('max_duration', $criteria['max_duration']);
        }

        // Options de tri
        $sortField = match ($criteria['sort'] ?? '') {
            'date' => 'f.dateFormation',
            'duration' => 'f.duree',
            default => 'f.titre'
        };

        $sortDirection = $criteria['direction'] ?? 'ASC';

        $queryBuilder->orderBy($sortField, $sortDirection);

        if ($paginate) {
            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder->getQuery());
            $total = count($paginator);

            $results = [];
            foreach ($paginator as $item) {
                $results[] = $item;
            }

            return [
                'results' => $results,
                'total' => $total
            ];
        }

        return $queryBuilder->getQuery()->getResult();
    }


    
}

<?php

namespace App\Repository;

use App\Entity\Tache;
use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Equipe;

class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }
    // src/Repository/TacheRepository.php
    public function findByProject(Projet $projet)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.projet = :projet')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getResult();
    }
    public function search(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function searchByProject(Projet $projet, string $query): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.projet = :projet')
            ->andWhere('(t.nom LIKE :query)')
            ->setParameter('projet', $projet)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
    public function prioritize(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function prioritizeByProject(Projet $projet): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.projet = :projet')
            ->setParameter('projet', $projet)
            ->orderBy('t.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findAllForExport(?Projet $projet = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.statut', 'ASC')
            ->addOrderBy('t.dateFin', 'ASC');

        if ($projet) {
            $qb->andWhere('t.projet = :projet')
                ->setParameter('projet', $projet);
        }

        return $qb->getQuery()->getResult();
    }
    public function findTasksOfTeamWithTrello(Equipe $equipe): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.projet', 'p')
            ->innerJoin('p.projetequipes', 'pe')
            ->where('pe.equipe = :equipe')
            ->andWhere('t.trelloboardid IS NOT NULL')
            ->setParameter('equipe', $equipe)
            ->getQuery()
            ->getResult();
    }
    public function findByStatusAndProject(Projet $projet, string $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.projet = :projet')
            ->andWhere('t.statut = :status')
            ->setParameter('projet', $projet)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }
    public function countTachesParProjet(int $limit = 6): array
    {
        return $this->createQueryBuilder('t')
            ->select('p.nom as projet_nom, COUNT(t.id) as count')
            ->join('t.projet', 'p')
            ->groupBy('p.id')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getDelayedTasksPercentage(Projet $projet): array
    {
        $now = new \DateTime();
        
        $totalTasks = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.projet = :projet')
            ->setParameter('projet', $projet)
            ->getQuery()
            ->getSingleScalarResult();

        $delayedTasks = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.projet = :projet')
            ->andWhere('t.statut != :status')
            ->andWhere('t.dateFin < :now')
            ->setParameter('projet', $projet)
            ->setParameter('status', 'Terminée')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => (int)$totalTasks,
            'delayed' => (int)$delayedTasks,
            'percentage' => $totalTasks > 0 ? round(($delayedTasks / $totalTasks) * 100) : 0
        ];
    }
}

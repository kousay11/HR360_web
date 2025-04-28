<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Equipe;
use Doctrine\ORM\EntityManagerInterface;

class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nom LIKE :query')
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
    public function searchFront(string $query, Utilisateur $userid): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.projetequipes', 'pe')
            ->innerJoin('pe.equipe', 'e')
            ->innerJoin('e.equipeemployes', 'ee')
            ->where('ee.utilisateur = :userId')
            ->Andwhere('p.nom LIKE :query')
            ->setParameter('userId', $userid)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
    public function prioritizeFront(Utilisateur $userid): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.projetequipes', 'pe')
            ->innerJoin('pe.equipe', 'e')
            ->innerJoin('e.equipeemployes', 'ee')
            ->where('ee.utilisateur = :userId')
            ->setParameter('userId', $userid)
            ->orderBy('t.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findEquipeEmails(Projet $projet): array
    {
        $results = $this->createQueryBuilder('p')
            ->innerJoin('p.projetequipes', 'pe')
            ->innerJoin('pe.equipe', 'e')
            ->innerJoin('e.equipeemployes', 'ee')
            ->innerJoin('ee.utilisateur', 'u')
            ->where('p.id = :projetId')
            ->setParameter('projetId', $projet->getId())
            ->select('u.email')
            ->getQuery()
            ->getArrayResult();

        return array_column($results, 'email');
    }
    public function findByEquipe(Equipe $equipe): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.projetequipes', 'pe') // assure-toi que l'entité Projet a une relation ManyToMany avec Equipe
            ->where('pe.equipe = :equipe')
            ->setParameter('equipe', $equipe)
            ->getQuery()
            ->getResult();
    }
    public function countProjetsParEquipe(): array
    {
        return $this->createQueryBuilder('p')
            ->select('e.nom as equipe_nom, COUNT(p.id) as count')
            ->join('p.projetequipes', 'pe')
            ->join('pe.equipe', 'e')
            ->groupBy('e.id')
            ->getQuery()
            ->getResult();
    }

    public function evolutionProjetsParMois(EntityManagerInterface $em): array
    {
        $sql = "
        SELECT 
            DATE_FORMAT(p.dateDebut, '%Y-%m') as mois_debut,
            DATE_FORMAT(p.dateFin, '%Y-%m') as mois_fin,
            COUNT(p.id) as count
        FROM projet p
        GROUP BY mois_debut, mois_fin
        ORDER BY mois_debut
    ";

        $stmt = $em->getConnection()->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }
    public function evolutionProjetsParMoisExcel(EntityManagerInterface $em): array
    {
        // Requête pour les projets démarrés par mois
        $started = $em->getConnection()->executeQuery(
            "SELECT DATE_FORMAT(p.dateDebut, '%Y-%m') as mois, COUNT(p.id) as debut
         FROM projet p
         GROUP BY mois
         ORDER BY mois"
        )->fetchAllAssociative();

        // Requête pour les projets terminés par mois
        $ended = $em->getConnection()->executeQuery(
            "SELECT DATE_FORMAT(p.dateFin, '%Y-%m') as mois, COUNT(p.id) as fin
         FROM projet p
         GROUP BY mois
         ORDER BY mois"
        )->fetchAllAssociative();

        // Fusionner les résultats
        $result = [];

        // Ajouter les mois avec des projets démarrés
        foreach ($started as $item) {
            $result[$item['mois']] = [
                'mois' => $item['mois'],
                'debut' => $item['debut'],
                'fin' => 0 // Initialiser à 0
            ];
        }

        // Ajouter/Mettre à jour les mois avec des projets terminés
        foreach ($ended as $item) {
            if (isset($result[$item['mois']])) {
                $result[$item['mois']]['fin'] = $item['fin'];
            } else {
                $result[$item['mois']] = [
                    'mois' => $item['mois'],
                    'debut' => 0, // Aucun projet démarré ce mois
                    'fin' => $item['fin']
                ];
            }
        }

        return array_values($result);
    }
}

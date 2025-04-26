<?php
namespace App\Service;

use App\Entity\Projet;
use App\Entity\Equipe;
use App\Repository\ProjetRepository;

class WorkloadGenerator
{
    public function __construct(private ProjetRepository $projetRepository) {}

    public function generate(Equipe $equipe): array
    {
        $projects = $this->projetRepository->findByEquipe($equipe);
        $events = [];

        foreach ($projects as $project) {
            $concurrent = $this->countConcurrentProjects($project, $equipe);
            
            $events[] = [
                'title' => $project->getNom(),
                'start' => $project->getDateDebut()->format('Y-m-d'),
                'end' => $project->getDateFin()->modify('+1 day')->format('Y-m-d'),
                'color' => $this->getColor($concurrent),
                'extendedProps' => [
                    'description' => $project->getDescription()
                ]
            ];
        }

        return $events;
    }

    private function countConcurrentProjects(Projet $project, Equipe $equipe): int
    {
        return $this->projetRepository->createQueryBuilder('p')
            ->join('p.projetequipes', 'pe')
            ->where('pe.equipe = :equipe')
            ->andWhere('p.dateDebut <= :end AND p.dateFin >= :start')
            ->setParameter('equipe', $equipe)
            ->setParameter('start', $project->getDateFin())
            ->setParameter('end', $project->getDateDebut())
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getColor(int $count): string
    {
        return match(true) {
            $count >= 3 => '#FF6B6B', // Rouge
            $count == 2 => '#FFA500', // Orange
            default => '#A3E4A3'      // Vert
        };
    }
}
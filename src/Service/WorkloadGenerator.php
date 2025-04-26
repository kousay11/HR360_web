<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\Equipe;
use App\Repository\ProjetRepository;
use DatePeriod;
use DateInterval;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WorkloadGenerator
{
    public function __construct(
        private ProjetRepository $projetRepository,
        private UrlGeneratorInterface $router
    ) {}

    public function getDailyWorkload(Equipe $equipe): array
    {
        $projects = $this->projetRepository->findByEquipe($equipe);
        $workload = [];

        foreach ($projects as $project) {
            $period = new DatePeriod(
                $project->getDateDebut(),
                new DateInterval('P1D'),
                $project->getDateFin()->modify('+1 day')
            );

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $workload[$dateStr][] = [
                    'id' => $project->getId(),
                    'name' => $project->getNom(),
                    'description' => $project->getDescription(),
                    'detailsUrl' => $this->router->generate('app_projet_show', ['id' => $project->getId()])
                ];
            }
        }

        return $workload;
    }

    public function getColorCode(int $projectCount): string
    {
        return match (true) {
            $projectCount >= 3 => '#ff6b6b', // Red
            $projectCount == 2 => '#ffa500', // Orange
            default => '#1dd1a1'            // Green
        };
    }
}
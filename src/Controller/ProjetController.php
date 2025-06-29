<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projetequipe;
use App\Entity\Equipe;
use App\Repository\ProjetequipeRepository;
use App\Repository\EquipeRepository;
use App\Entity\Tache;
use App\Service\TrelloApiService;
use App\Service\WorkloadGenerator;

#[Route('/projet')]
final class ProjetController extends AbstractController
{
    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
    public function index(Request $request, ProjetRepository $projetRepository): Response
    {
        return $this->search($request, $projetRepository);
    }

    #[Route('/search', name: 'app_projet_search', methods: ['GET'])]
    public function search(Request $request, ProjetRepository $projetRepository): Response
    {
        $query = $request->query->get('q', '');
        $projets = $projetRepository->search($query);

        if ($request->isXmlHttpRequest()) {
            return $this->render('projet/_project_grid.html.twig', [
                'projets' => $projets,
            ]);
        }

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }
    #[Route('/prioritize', name: 'app_projet_prioritize', methods: ['GET'])]
    public function prioritize(Request $request, ProjetRepository $projetRepository): Response
    {
        $projets = $projetRepository->prioritize();

        if ($request->isXmlHttpRequest()) {
            return $this->render('projet/_project_grid.html.twig', [
                'projets' => $projets,
            ]);
        }

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/projet/{id}/associate-team', name: 'app_projet_associate_team', methods: ['GET', 'POST'])]
    public function associateTeam(Request $request, Projet $projet, EntityManagerInterface $entityManager, WorkloadGenerator $workloadGenerator): Response
    {
        $equipes = $entityManager->getRepository(Equipe::class)->findAll();

        if ($request->isMethod('POST')) {
            $equipeId = $request->request->get('equipe');
            $equipe = $entityManager->getRepository(Equipe::class)->find($equipeId);

            // Create new project-team association
            $projetEquipe = new Projetequipe();
            $projetEquipe->setProjet($projet);
            $projetEquipe->setEquipe($equipe);

            $entityManager->persist($projetEquipe);
            $entityManager->flush();

            $this->addFlash('success', 'Team successfully associated with project.');
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        // Calculate workload for each team using the existing WorkloadGenerator
        $workloads = [];
        foreach ($equipes as $equipe) {
            $workloadStats = $workloadGenerator->calculateWorkloadDuringPeriod($equipe, $projet->getDateDebut(), $projet->getDateFin());
            $workloads[$equipe->getId()] = [
                'totalProjects' => $workloadStats['totalProjects'],
                'averageProjectsPerDay' => $workloadStats['averageProjectsPerDay']
            ];
        }

        return $this->render('projet/associate_team.html.twig', [
            'projet' => $projet,
            'equipes' => $equipes,
            'workloads' => $workloads
        ]);
    }

    #[Route('/projet/{id}/disassociate-team/{equipeId}', name: 'app_projet_disassociate_team', methods: ['POST'])]
    public function disassociateTeam(Request $request, Projet $projet, int $equipeId, EntityManagerInterface $entityManager, TrelloApiService $trello): Response
    {
        $projetEquipe = $entityManager->getRepository(Projetequipe::class)->findOneBy([
            'projet' => $projet,
            'equipe' => $equipeId
        ]);

        if ($projetEquipe) {
            $entityManager->remove($projetEquipe);
            $entityManager->flush();
        }

        $taches = $projet->getTaches();
        foreach ($taches as $tache) {
            if ($tache->getTrelloboardid() != null) {
                $trello->deleteBoard($tache->getTrelloboardid());
                $tache->setTrelloboardid(null);
                $entityManager->persist($tache);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
    }

    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet, TacheRepository $tacheRepository): Response
    {
        $taches = $tacheRepository->findBy(['projet' => $projet]);

        // Initialize counters
        $counts = [
            'total' => count($taches),
            'a_faire' => 0,
            'en_cours' => 0,
            'terminee' => 0
        ];

        // Count tasks by status
        foreach ($taches as $tache) {
            switch ($tache->getStatut()->value) {
                case 'A faire':
                    $counts['a_faire']++;
                    break;
                case 'En cours':
                    $counts['en_cours']++;
                    break;
                case 'Terminée':
                    $counts['terminee']++;
                    break;
            }
        }

        // Calculate completion percentage
        $completionPercentage = $counts['total'] > 0
            ? (($counts['a_faire'] * 0 + $counts['en_cours'] * 0.5 + $counts['terminee'] * 1) / $counts['total'] * 100)
            : 0;
        $completionPercentage = round($completionPercentage, 2);

        // Get delayed tasks percentage
        $delayedTasks = $tacheRepository->getDelayedTasksPercentage($projet);

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'taches' => $taches,
            'counts' => $counts,
            'completionPercentage' => $completionPercentage,
            'delayedTasks' => $delayedTasks
        ]);
    }

    #[Route('/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager, TrelloApiService $trello): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->getPayload()->getString('_token'))) {
            $taches = $projet->getTaches();
            foreach ($taches as $tache) {
                if ($tache->getTrelloboardid() != null) {
                    $trello->deleteBoard($tache->getTrelloboardid());
                    $tache->setTrelloboardid(null);
                    $entityManager->persist($tache);
                }
            }
            $entityManager->remove($projet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projet;
use App\Enum\StatusTache;
use App\Repository\ProjetRepository;

#[Route('/tache')]
final class TacheController extends AbstractController
{
    #[Route('/', name: 'app_tache_index', methods: ['GET'])]
    #[Route('/project/{id}', name: 'app_tache_by_project', methods: ['GET'])]
    public function index(?Projet $projet, TacheRepository $tacheRepository): Response
    {
        $taches = $projet 
            ? $tacheRepository->findBy(['projet' => $projet])
            : $tacheRepository->findAll();

        return $this->render('tache/index.html.twig', [
            'taches' => $taches,
            'projet' => $projet
        ]);
    }

    #[Route('/new/{projetId}', name: 'app_tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProjetRepository $projetRepository, ?int $projetId = null): Response
    {
        $tache = new Tache();
        $tache->setStatut(StatusTache::AFAIRE);
        $projet = $projetId ? $projetRepository->find($projetId) : null;
        // If projetId is provided, set it on the new task
        if ($projetId) {
            $projet = $entityManager->getRepository(Projet::class)->find($projetId);
            if ($projet) {
                $tache->setProjet($projet);
            }
        }

        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $projet,
            'is_edit' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            // Redirect back to project's tasks if coming from project
            $redirectRoute = $projetId 
                ? 'app_tache_by_project' 
                : 'app_tache_index';
                
            return $this->redirectToRoute($redirectRoute, [
                'id' => $projetId
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/new.html.twig', [
            'tache' => $tache,
            'form' => $form,
            'projetId' => $projetId
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        
        $form = $this->createForm(TacheType::class, $tache, [
            'is_edit' => true,  // Explicitly set to true for edit
            'projet' => $tache->getProjet()  // Pass the associated project
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect back to project's tasks if task belongs to project
            $redirectRoute = $projetId 
                ? 'app_tache_by_project' 
                : 'app_tache_index';
                
            return $this->redirectToRoute($redirectRoute, [
                'id' => $projetId
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/edit.html.twig', [
            'tache' => $tache,
            'form' => $form,
            'projetId' => $projetId
        ]);
    }

    #[Route('/{id}', name: 'app_tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        
        if ($this->isCsrfTokenValid('delete'.$tache->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tache);
            $entityManager->flush();
        }

        // Redirect back to project's tasks if task belonged to project
        $redirectRoute = $projetId 
            ? 'app_tache_by_project' 
            : 'app_tache_index';
            
        return $this->redirectToRoute($redirectRoute, [
            'id' => $projetId
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        return $this->render('tache/show.html.twig', [
            'tache' => $tache,
            'projetId' => $projetId
        ]);
    }
}

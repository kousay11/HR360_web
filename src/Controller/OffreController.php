<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Service\GrammarCheckerService;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse; // Ajoutez cette ligne
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offre')]
final class OffreController extends AbstractController
{
    #[Route('/', name: 'app_offre_index', methods: ['GET'])]
    public function index(Request $request, OffreRepository $offreRepository): Response
    {
        // Met à jour les statuts avant d'afficher
        $offreRepository->updateExpiredStatus();

        // Récupère les paramètres de recherche et de tri
        $searchTerm = $request->query->get('search');
        $sort = $request->query->get('sort', 'date_expiration_asc');

        // Trouve les offres avec les critères
        $offres = $offreRepository->findWithSearchAndSort($searchTerm, $sort);

        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
            'searchTerm' => $searchTerm,
            'currentSort' => $sort,
        ]);
    }


    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offre->setStatut('Publiée'); // Définit le statut par défaut
            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idoffre}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{idoffre}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }
    #[Route('/{idoffre}/candidatures', name: 'app_offre_candidatures', methods: ['GET'])]
    public function candidaturesByOffre(Offre $offre): Response
    {
        return $this->render('offre/candidatures.html.twig', [
            'offre' => $offre,
            'candidatures' => $offre->getCandidatures(),
        ]);
    }

    #[Route('/{idoffre}', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getIdoffre(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offre_index', [], Response::HTTP_SEE_OTHER);
    }
   
    #[Route('/validate-grammar', name: 'app_offre_validate_grammar', methods: ['POST'])]
    public function validateGrammar(Request $request, GrammarCheckerService $grammarCheckerService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        
        if (empty($text)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Le texte à vérifier est vide'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $result = $grammarCheckerService->checkGrammar($text);
        
        return new JsonResponse([
            'status' => 'success',
            'error' => $result['errors']['error'] ?? null,
            'correction' => $result['errors']['correction'] ?? null
        ]);
    }
    #[Route('/test-grammar', name: 'test_grammar')]
public function testGrammar(GrammarCheckerService $grammarChecker): Response
{
    $testText = "Je suis une phrase avec une erreur gramatique.";
    $result = $grammarChecker->checkGrammar($testText);
    
    dd($result); // Affiche le résultat brut
}
}

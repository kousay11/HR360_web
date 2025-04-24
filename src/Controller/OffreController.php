<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Service\DeepTranslateService;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
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
    #[Route('/api/generate-description', name: 'app_offre_generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['error' => 'Requête non autorisée'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $titre = $data['titre'] ?? '';
        
        if (empty(trim($titre))) {
            return $this->json(['error' => 'Le titre est requis et ne peut pas être vide'], 400);
        }
        
        try {
            $description = $this->generateDescriptionFromAI(trim($titre));
            return $this->json([
                'description' => $description,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur de génération',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Méthode privée pour l'appel API
    private function generateDescriptionFromAI(string $titre): string
    {
        $apiUrl = 'https://api.together.xyz/v1/chat/completions';
        $apiKey = $this->getParameter('app.together_api_key');
        
        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'verify' => false // À n'utiliser qu'en développement
        ]);
        
        try {
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'model' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un assistant qui génère des descriptions concises pour des offres d\'emploi.'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Crée une description professionnelle pour le poste: $titre"
                        ]
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Réponse API invalide');
            }
            
            return $this->cleanGeneratedText($data['choices'][0]['message']['content']);
            
        } catch (\Exception $e) {
            error_log('API Error: ' . $e->getMessage());
            throw new \Exception('Service temporairement indisponible');
        }
    }
    
    private function cleanGeneratedText(string $text): string
    {
        // Supprime les numérotations et les caractères spéciaux
        $text = preg_replace('/^\d+\.\s*/m', '', $text);
        // Supprime les sauts de ligne multiples
        $text = preg_replace('/\n{2,}/', "\n\n", $text);
        return trim($text);
    }
   #[Route('/api/translate', name: 'app_offre_translate', methods: ['POST'])]
public function translateDescription(Request $request, DeepTranslateService $translator): JsonResponse
{
    if (!$request->isXmlHttpRequest()) {
        return $this->json(['error' => 'Requête non autorisée'], 403);
    }

    try {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['text'] ?? '')) {
            return $this->json(['error' => 'Le texte à traduire est requis'], 400);
        }

        $translatedText = $translator->translate($data['text']);
        
        return $this->json([
            'translatedText' => $translatedText,
            'status' => 'success'
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'error' => 'service_unavailable',
            'message' => $e->getMessage()
        ], 503);
    }
}
}

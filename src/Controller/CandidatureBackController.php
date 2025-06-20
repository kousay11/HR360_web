<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Offre;
use App\Form\CandidatureOtherType;
use App\Service\CvParserService;
use App\Repository\CandidatureRepository;
use App\Repository\OffreRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route; // ou Symfony\Component\Routing\Attribute\Route si tu utilises Symfony 6+
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\LoggerInterface;




#[Route('/candidatureBack')]
final class CandidatureBackController extends AbstractController
{
    // src/Controller/CandidatureBackController.php

#[Route(name: 'app_candidatureBack_index', methods: ['GET'])]
public function index(Request $request, CandidatureRepository $candidatureRepository): Response
{
    $statut = $request->query->get('statut');
    
    $candidatures = $statut 
        ? $candidatureRepository->findByStatut($statut)
        : $candidatureRepository->findAllWithOffre();

    return $this->render('candidatureBack/index.html.twig', [
        'candidatures' => $candidatures,
    ]);
}

#[Route('/export-pdf/{offreId}', name: 'app_candidatureBack_export_pdf', defaults: ['offreId' => null], methods: ['GET'])]
public function exportPdf(
    CandidatureRepository $candidatureRepository, 
    Request $request,
    ?int $offreId = null,
    ?OffreRepository $offreRepository = null
): Response {
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    $pdfOptions->set('isRemoteEnabled', true); // Autorise le chargement des images externes
    $pdfOptions->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($pdfOptions);

    // Récupérer les candidatures selon le filtre
    if ($offreId) {
        $offre = $offreRepository->find($offreId);
        $candidatures = $candidatureRepository->findBy(['offre' => $offreId]);
        $title = "Candidatures pour l'offre : " . $offre->getTitre();
    } else {
        $candidatures = $candidatureRepository->findAllWithOffre();
        $title = "Liste de toutes les candidatures";
    }

    // Chemin physique du logo
    $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/logoRH360.png';

    // Vérification de l'existence et encodage en Base64
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoUrl = 'data:image/png;base64,' . $logoData;
        $logoExists = true;
    } else {
        $logoUrl = null;
        $logoExists = false;
    }

    // Passage au template
    $html = $this->renderView('candidatureBack/pdf.html.twig', [
        'candidatures' => $candidatures,
        'title' => $title,
        'logo_path' => $logoUrl,
        'logo_exists' => $logoExists,
        'offre' => $offreId ? $offre : null
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $output = $dompdf->output();

    $filename = $offreId 
        ? sprintf('candidatures_offre_%d.pdf', $offreId)
        : 'liste_toutes_candidatures.pdf';

    $response = new Response($output);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

    return $response;
}
    
    #[Route('/{idCandidature}', name: 'app_candidatureBack_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidatureBack/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{idCandidature}/edit', name: 'app_candidatureBack_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Candidature $candidature, 
        EntityManagerInterface $entityManager,
        EmailService $emailService,
        LoggerInterface $logger
    ): Response {
        $ancienStatut = $candidature->getStatut();
        $logger->info("Début de modification - Ancien statut: {$ancienStatut}");
    
        $form = $this->createForm(CandidatureOtherType::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauStatut = $candidature->getStatut();
            $logger->info("Formulaire soumis - Nouveau statut: {$nouveauStatut}");
    
            $candidature->setDateModification(new \DateTime());
            $entityManager->flush();
            $logger->info("Candidature mise à jour en base de données");
    
            if ($ancienStatut !== $nouveauStatut) {
                $logger->info("Statut modifié - Début du processus d'envoi d'email");
                
                $iduser = $candidature->getiduser();
                $email = $iduser?->getEmail();
                
                if ($email) {
                    $logger->info("Email du candidat trouvé: {$email}");
                    $emailSent = $emailService->sendStatusUpdateEmail($email, $nouveauStatut);
                    
                    if ($emailSent) {
                        $logger->info("Email envoyé avec succès à {$email}");
                    } else {
                        $logger->error("Échec de l'envoi d'email à {$email}");
                    }
                    
                    $this->addFlash(
                        $emailSent ? 'success' : 'warning',
                        $emailSent 
                            ? 'Statut mis à jour et email envoyé'
                            : 'Statut mis à jour mais échec d\'envoi d\'email'
                    );
                } else {
                    $logger->warning("Aucun email trouvé pour l'utilisateur ID: {$iduser?->getId()}");
                    $this->addFlash('warning', 'Statut mis à jour mais utilisateur sans email');
                }
            } else {
                $logger->info("Statut inchangé - Pas d'email à envoyer");
            }
    
            return $this->redirectToRoute('app_candidatureBack_index');
        }
    
        return $this->render('candidatureBack/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form->createView(),
        ]);
    }
#[Route('/analyse', name: 'app_analyse_candidature', methods: ['POST'])]
public function analyseCandidature(Request $request, LoggerInterface $logger): JsonResponse
{
    try {
        $cvPath = $request->request->get('cvPath');
        $jobOfferFile = $request->files->get('jobOfferFile');

        $logger->info("CV Path: " . $cvPath);
        $logger->info("Job Offer File: " . ($jobOfferFile ? $jobOfferFile->getClientOriginalName() : 'null'));

        if (!$cvPath || !$jobOfferFile) {
            throw new \Exception('Fichiers manquants: CV ou offre d\'emploi non reçus');
        }

        $fullCvPath = $this->getParameter('kernel.project_dir') . '/public' . parse_url($cvPath, PHP_URL_PATH);
        $logger->info("Full CV Path: " . $fullCvPath);

        if (!file_exists($fullCvPath)) {
            throw new \Exception('Fichier CV introuvable: ' . $fullCvPath);
        }

        // Correction ici - ajout du 3ème argument ($logger)
        $apiResponse = $this->callAnalysisApi($fullCvPath, $jobOfferFile->getPathname(), $logger);
        $logger->info("API Response: " . json_encode($apiResponse));

        $score = $this->extractScoreFromApiResponse($apiResponse);
        $message = $this->getResultMessage($score);
        $details = $this->formatAnalysisDetails($apiResponse);
        $pdfPath = $this->generateAnalysisPdf([
            'score' => $score,
            'message' => $message,
            'details' => $details
        ], $request);

        return $this->json([
            'success' => true,
            'score' => $score,
            'message' => $message,
            'details' => $details,
            'pdfPath' => $pdfPath
        ]);
    } catch (\Exception $e) {
        $logger->error("Analysis error: " . $e->getMessage());
        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de l\'analyse',
            'error' => $e->getMessage(),
            'cvPath' => $cvPath ?? 'null',
            'jobOfferFile' => $jobOfferFile ? $jobOfferFile->getClientOriginalName() : 'null'
        ]);
    }
}



private function getResultMessage(int $score): string
{
    if ($score >= 80) {
        return "Excellente correspondance entre le CV et l'offre.";
    } elseif ($score >= 60) {
        return "Bonne correspondance, mais des améliorations sont possibles.";
    } elseif ($score >= 40) {
        return "Correspondance moyenne. Il y a des écarts importants.";
    } else {
        return "Faible correspondance. Le CV ne semble pas adapté à l'offre.";
    }
}

private function formatAnalysisDetails(array $apiResponse): string
{
    // Si l'API retourne des détails spécifiques
    if (isset($apiResponse['analysis_details'])) {
        $details = '';
        foreach ($apiResponse['analysis_details'] as $key => $value) {
            $details .= '<strong>' . ucfirst($key) . ':</strong> ' . $value . '<br>';
        }
        return $details;
    }
    
    // Si l'API retourne un message d'erreur
    if (isset($apiResponse['error'])) {
        return '<div class="alert alert-warning">' . $apiResponse['error'] . '</div>';
    }
    
    // Si l'API retourne d'autres données
    $details = '';
    foreach ($apiResponse as $key => $value) {
        if (!in_array($key, ['score', 'success'])) {
            $details .= '<strong>' . ucfirst($key) . ':</strong> ' . json_encode($value) . '<br>';
        }
    }
    
    return $details ?: 'Les détails de l\'analyse ne sont pas disponibles dans le format attendu.';
}


private function callAnalysisApi(string $cvPath, string $jobOfferPath, LoggerInterface $logger): array
{
    $client = HttpClient::create([
        'timeout' => 30,
        'verify_peer' => false,
    ]);

    try {
        $response = $client->request('POST', 'https://cv-resume-to-job-match-analysis-api.p.rapidapi.com/api:QQ6fvSXH/good_fit_external_API', [
            'headers' => [
                'X-RapidAPI-Host' => 'cv-resume-to-job-match-analysis-api.p.rapidapi.com',
                'X-RapidAPI-Key' => '58781479a8msh50ee0c2c7fb876ap1b2da2jsnaf8f922475b2',
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'cv_file' => fopen($cvPath, 'r'),
                'job_offer_file' => fopen($jobOfferPath, 'r')
            ]
        ]);

        $content = $response->getContent(false);
        
        // First try to decode as JSON
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // If not JSON, handle as text response
        return [
            'analysis' => $content,
            'score' => $this->extractScoreFromText($content),
            'raw_response' => $content
        ];
        
    } catch (\Exception $e) {
        $logger->error("API Error: ".$e->getMessage());
        return [
            'error' => 'API Error',
            'message' => $e->getMessage()
        ];
    }
}

private function extractScoreFromText(string $text): int
{
    // Look for the "Fit Score" section in the text
    if (preg_match('/Fit Score.*?(\d+)/', $text, $matches)) {
        return (int)$matches[1];
    }
    
    // Try to estimate score based on keywords
    $score = 50; // default neutral score
    
    if (strpos($text, 'APPLICATION LITTLE CORRESPONDING') !== false) {
        $score = 30;
    } elseif (strpos($text, 'APPLICATION CORRESPONDING') !== false) {
        $score = 70;
    } elseif (strpos($text, 'APPLICATION WELL CORRESPONDING') !== false) {
        $score = 90;
    }
    
    return $score;
}

private function extractScoreFromApiResponse(array $apiResponse): int
{
    if (isset($apiResponse['score'])) {
        return (int) $apiResponse['score'];
    }

    // Valeur par défaut si le score n'existe pas
    return 0;
}


private function generateAnalysisPdf(array $analysisData, Request $request): ?string
{
    try {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('candidatureBack/analysis_pdf.html.twig', [
            'analysis' => $analysisData,
            'date' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        // Créer le répertoire s'il n'existe pas
        $pdfDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdfs/';
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        $pdfFileName = 'analysis_' . uniqid() . '.pdf';
        $pdfFilePath = $pdfDir . $pdfFileName;

        file_put_contents($pdfFilePath, $output);

        // Retourner le chemin relatif pour le web
        return '/uploads/pdfs/' . $pdfFileName;

    } catch (\Exception $e) {
        // Logger l'erreur si nécessaire
        return null;
    }
}
#[Route('/parse-cv/{idCandidature}', name: 'app_candidature_parse_cv', methods: ['GET'])]
public function parseCv(Candidature $candidature, CvParserService $cvParserService): JsonResponse
{
    $result = $cvParserService->parseCv($candidature->getCv());

    // Si le service a retourné une erreur
    if (isset($result['error'])) {
        return $this->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    try {
        // Générer le PDF d'analyse
        $pdfPath = $this->generateCvAnalysisPdf($result, $candidature);

        return $this->json([
            'success' => true,
            'data' => $result,
            'pdfPath' => $pdfPath
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => 'Erreur lors de la génération du PDF: ' . $e->getMessage()
        ], 500);
    }
}

private function generateCvAnalysisPdf(array $analysisData, Candidature $candidature): string
{
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    
    $dompdf = new Dompdf($pdfOptions);
    
    // Préparer les données pour le template
    $templateData = [
        'analysis' => [
            'personal_info' => $analysisData['personal_info'] ?? null,
            'work_experience' => $analysisData['work_experience'] ?? null,
            // Ajoutez d'autres champs si nécessaire
        ],
        'candidature' => $candidature,
        'date' => new \DateTime()
    ];
    
    $html = $this->renderView('candidatureBack/cv_analysis_pdf.html.twig', $templateData);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    $pdfDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pdfs/';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    
    $filename = 'cv_analysis_' . $candidature->getIdCandidature() . '_' . uniqid() . '.pdf';
    $pdfPath = $pdfDir . $filename;
    
    file_put_contents($pdfPath, $dompdf->output());
    
    return '/uploads/pdfs/' . $filename;
}

}
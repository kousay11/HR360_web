<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Service\EmailService;
use App\Entity\Offre;
use App\Form\CandidatureOtherType;
use App\Repository\CandidatureRepository;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Psr\Log\LoggerInterface;
use Dompdf\Options;

#[Route('/candidatureBack')]
final class CandidatureBackController extends AbstractController
{
    #[Route(name: 'app_candidatureBack_index', methods: ['GET'])]
    public function index(CandidatureRepository $candidatureRepository): Response
    {
        return $this->render('candidatureBack/index.html.twig', [
            'candidatures' => $candidatureRepository->findAllWithOffre(),
        ]);
    }

    #[Route('/export-pdf', name: 'app_candidatureBack_export_pdf', methods: ['GET'])]
    public function exportPdf(CandidatureRepository $candidatureRepository): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Chemin absolu vers l'image
        $logoPath = $this->getParameter('kernel.project_dir').'/public/images/logoRH360.png';
        $logoExists = file_exists($logoPath);
        
        $html = $this->renderView('candidatureBack/pdf.html.twig', [
            'candidatures' => $candidatureRepository->findAllWithOffre(),
            'title' => "Liste des candidatures",
            'logo_path' => $logoExists ? $logoPath : null,
            'logo_exists' => $logoExists
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        $output = $dompdf->output();
        
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="liste_candidatures.pdf"');
        
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
    EmailService $emailService
): Response {
    // Avant de traiter le formulaire, on garde le statut initial
    $ancienStatut = $candidature->getStatut();

    // Création et traitement du formulaire
    $form = $this->createForm(CandidatureOtherType::class, $candidature);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Nouveau statut après soumission
        $nouveauStatut = $candidature->getStatut();

        // Mettre à jour la date de modification
        $candidature->setDateModification(new \DateTime());

        // Sauvegarder dans la base de données
        $entityManager->flush();

        // Si le statut a changé, on envoie l'email
        if ($ancienStatut !== $nouveauStatut) {
            $emailSent = $emailService->sendStatusUpdateEmail(
                'kousay.najar@esprit.tn',  // À remplacer plus tard par $candidature->getEmail()
                $nouveauStatut
            );

            // Afficher une notification dans l'interface
            $this->addFlash(
                $emailSent ? 'success' : 'warning',
                $emailSent 
                    ? 'Statut mis à jour et email envoyé'
                    : 'Statut mis à jour mais échec d\'envoi d\'email'
            );
        }

        // Redirection après traitement
        return $this->redirectToRoute('app_candidatureBack_index');
    }

    // Affichage du formulaire si non soumis ou invalide
    return $this->render('candidatureBack/edit.html.twig', [
        'candidature' => $candidature,
        'form' => $form->createView(),
    ]);
}
}
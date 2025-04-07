<?php

namespace App\Controller;

use App\Entity\Candidature;
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
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidatureOtherType::class, $candidature);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $candidature->setDateModification(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Le statut de la candidature a été mis à jour.');
        return $this->redirectToRoute('app_candidatureBack_index', ['idCandidature' => $candidature->getIdCandidature()]);
    }

    return $this->render('candidatureBack/edit.html.twig', [
        'candidature' => $candidature,
        'form' => $form->createView(),
    ]);
}
}
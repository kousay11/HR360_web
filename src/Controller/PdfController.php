<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    #[Route('/pdf', name: 'generate_pdf')]
    public function generatePdf(Pdf $pdf, ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser(); // utilisateur connecté
        $reservations = $reservationRepository->findBy(['utilisateur' => $user]);

        $html = $this->renderView('pdf/template.html.twig', [
            'reservations' => $reservations,
            'employe' => $user, // <<< ajouté ici
        ]);

        $pdfContent = $pdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }
}

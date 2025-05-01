<?php

namespace App\Controller;

use App\Service\OffreExpirationNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OffreExpirationController extends AbstractController
{
    #[Route('/check-expiring-offres', name: 'app_check_expiring_offres', methods: ['POST'])]
    public function checkExpiringOffres(Request $request, OffreExpirationNotifier $notifier): Response
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('check_expiring_offres', $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $notifier->checkExpiringOffres();

        $this->addFlash('success', 'Vérification des offres expirantes effectuée avec succès.');

        return $this->redirectToRoute('app_offre_index');
    }
}

<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Entity\Reservation;
use App\Repository\RessourceRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Checkout\SessionCreateParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;

class StripeController extends AbstractController
{
    #[Route('/stripe/create-checkout-session/{id}', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(
        int $id,
        Request $request,
        RessourceRepository $ressourceRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        $ressource = $ressourceRepository->find($id);

        $data = json_decode($request->getContent(), true);
        $dateDebut = new \DateTime($data['dateDebut']);
        $dateFin = new \DateTime($data['dateFin']);

        Stripe::setApiKey('sk_test_...');

        $montant = $ressource->getPrix() * 100;

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $montant,
                    'product_data' => [
                        'name' => 'Réservation - ' . $ressource->getNom(),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('stripe_success', [], 0),
            'cancel_url' => $this->generateUrl('stripe_cancel', [], 0),
        ]);

        // Optionnel : tu peux stocker la réservation temporairement ici

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/stripe/success', name: 'stripe_success')]
    public function success(): Response
    {
        return $this->render('stripe/success.html.twig');
    }

    #[Route('/stripe/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}

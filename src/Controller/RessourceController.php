<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\RessourceSimilarityService;
use App\Entity\Notification;
use App\Entity\Reservation;
use App\Service\QRCodeService;
#[Route('/ressource')]
final class RessourceController extends AbstractController
{
    #[Route(name: 'app_ressource_index', methods: ['GET'])]
    public function index(RessourceRepository $ressourceRepository): Response
    {
        $ressources = $ressourceRepository->findAll();
        return $this->render('ressource/index.html.twig', [
            'ressources' => $ressources,
        ]);
    }

    

    #[Route('/employee', name: 'app_ressource_index_employee', methods: ['GET'])]
public function indexForEmployees(Request $request, RessourceRepository $ressourceRepository, ReservationRepository $reservationRepository, QRCodeService $qrCodeService): Response
{
    $search = $request->query->get('search');
    $type = $request->query->get('type');
    $maxPrice = $request->query->get('maxPrice');
    $availableOnly = $request->query->getBoolean('availableOnly');
    $sort = $request->query->get('sort');

    $ressources = $ressourceRepository->findWithFilters($search, $type, $maxPrice, $availableOnly, $sort);
    $types = array_unique(array_map(fn($r) => $r->getType(), $ressources));

    // Ajout du code pour récupérer les dates réservées
    $reservedDates = [];

    foreach ($ressources as $ressource) {
        // Remove existing QR code file if it exists
        $qrCodePath = 'public/uploads/qrcodes/qr_ressource_' . $ressource->getId() . '.png';
        if (file_exists($qrCodePath)) {
            unlink($qrCodePath);
        }
        $qrCodeService->generateQRCodeForRessource($ressource);
        $reservations = $reservationRepository->findBy(['ressource' => $ressource]);
        $dates = [];

        foreach ($reservations as $reservation) {
            $dates[] = [
                'start' => $reservation->getDatedebut(),
                'end' => $reservation->getDatefin(),
            ];
            
            
        }

        $reservedDates[$ressource->getId()] = $dates;
    }

    return $this->render('ressource/index_employee.html.twig', [
        'ressources' => $ressources,
        'types' => $types,
        'reservedDates' => $reservedDates, // ➔ on envoie aussi les dates réservées à Twig
    ]);
}



#[Route('/new', name: 'app_ressource_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ?int $ressourceId = null, QRCodeService $qrCodeService): Response
{
    $ressource = new Ressource();
    $form = $this->createForm(RessourceType::class, $ressource);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            // Traitement du fichier image
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                // Déplacer l'image vers le répertoire de destination
                $imageFile->move(
                    $this->getParameter('images_directory'), // Paramètre images_directory
                    $newFilename
                );
                $ressource->setImage($newFilename); // Assigner le nom du fichier à la ressource
            } catch (FileException $e) {
                // Gérer l'exception si nécessaire
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
            }
        }

        // Sauvegarder la ressource dans la base de données
        $entityManager->persist($ressource);
        $entityManager->flush();


                // Génération QR Code après avoir obtenu l'ID
        $qrCodeService->generateQRCodeForRessource($ressource);

        // Redirection après la soumission du formulaire
        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('ressource/new.html.twig', [
        'ressource' => $ressource,
        'form' => $form->createView(),
    ]);
}


#[Route('/{id}', name: 'app_ressource_show', methods: ['GET'])]
public function show(Ressource $ressource, RessourceRepository $ressourceRepository): Response
{
    $allResources = $ressourceRepository->findAll();
    $similarResourcesEntries = $this->findSimilarResources($ressource, $allResources);
    $similarResources = array_column($similarResourcesEntries, 'ressource');

    return $this->render('ressource/show.html.twig', [
        'ressource' => $ressource,
        'reservations' => $ressource->getReservations(),
        'similarResources' => $similarResources,
    ]);
}


#[Route('/{id}/edit', name: 'app_ressource_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Ressource $ressource, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(RessourceType::class, $ressource);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifier si la case "Supprimer l'image" est cochée
        if ($request->request->get('delete_image') === 'on') {
            $imagePath = $this->getParameter('images_directory') . '/' . $ressource->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath); // Supprimer le fichier image
            }
            $ressource->setImage(null); // Réinitialiser l'image
        }

        // Gérer l'upload de la nouvelle image
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $ressource->setImage($newFilename); // Assigner la nouvelle image
            } catch (FileException $e) {
                // Gérer l'erreur si nécessaire
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
            }
        }

        // Sauvegarder les modifications
        $entityManager->flush();

        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('ressource/edit.html.twig', [
        'ressource' => $ressource,
        'form' => $form->createView(),
    ]);
}


#[Route('/{id}', name: 'app_ressource_delete', methods: ['POST'])]
public function delete(Request $request, Ressource $ressource, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
{
    // Vérification du token CSRF
    if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
        
        // Récupérer toutes les réservations liées à cette ressource
        $reservations = $reservationRepository->findBy(['ressource' => $ressource]);

        // Créer des notifications pour chaque utilisateur ayant réservé la ressource
        foreach ($reservations as $reservation) {
            $notification = new Notification();
            $notification->setReservationid($reservation->getId());
            $notification->setUtilisateur($reservation->getUtilisateur()); // Associer l'utilisateur
            $notification->setMessage('La ressource "' . $ressource->getNom() . '" que vous avez réservée a été supprimée.');
            $notification->setDate(new \DateTime());

            // Persister la notification
            $entityManager->persist($notification);
        }

        // Supprimer la ressource
        $entityManager->remove($ressource);
        $entityManager->flush();

        // Rediriger après la suppression
        $this->addFlash('success', 'La ressource a été supprimée avec succès, et les utilisateurs ont été notifiés.');
    }

    return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
}



    private function findSimilarResources(Ressource $ressource, array $allResources, int $limit = 5): array
    {
        $similarities = [];
        $referencePrice = (float)$ressource->getPrix();
    
        foreach ($allResources as $otherRessource) {
            // Exclure la ressource actuelle de la comparaison
            if ($otherRessource->getId() === $ressource->getId()) {
                continue;
            }
    
            $similarity = 0;
            $reasons = [];
    
            // 1. Similarité par type (40% du score max)
            if ($ressource->getType() === $otherRessource->getType()) {
                $similarity += 40;
                $reasons[] = [
                    'icon' => 'fa-tag',
                    'text' => "Même catégorie: " . $ressource->getType(),
                    'type' => 'category'
                ];
            }
    
            // 2. Similarité par prix (35% du score max)
            if ($referencePrice > 0) { // Éviter la division par zéro
                $otherPrice = (float)$otherRessource->getPrix();
                $priceDifference = abs($referencePrice - $otherPrice);
                $priceRatio = $otherPrice / $referencePrice;
    
                if ($priceDifference < 0.01) { // Prix identiques
                    $similarity += 35;
                    $reasons[] = [
                        'icon' => 'fa-money-bill-wave',
                        'text' => "Prix identique",
                        'type' => 'price'
                    ];
                } elseif ($priceRatio < 0.95) { // Prix inférieur
                    $discount = round((1 - $priceRatio) * 100);
                    $similarity += 30 + (5 * (1 - min($priceRatio, 0.95)));
                    $reasons[] = [
                        'icon' => 'fa-percentage',
                        'text' => "Économie de $discount%",
                        'type' => 'price'
                    ];
                } elseif ($priceRatio <= 1.05) { // Prix similaire (±5%)
                    $similarity += 30;
                    $reasons[] = [
                        'icon' => 'fa-balance-scale',
                        'text' => "Prix similaire (±5%)",
                        'type' => 'price'
                    ];
                } elseif ($priceRatio <= 1.20) { // Prix légèrement supérieur
                    $similarity += 20;
                    $reasons[] = [
                        'icon' => 'fa-coins',
                        'text' => "Prix légèrement supérieur",
                        'type' => 'price'
                    ];
                }
            }
    
            // 3. Similarité par état (15% du score max)
            $stateScore = match($otherRessource->getEtat()) {
                'Neuf' => 15,
                'Comme neuf' => 12,
                'Bon état' => 8,
                'Usagé' => 5,
                default => 0
            };
            $similarity += $stateScore;
            $reasons[] = [
                'icon' => 'fa-star',
                'text' => "État: " . $otherRessource->getEtat(),
                'type' => 'condition'
            ];
    
            // 4. Bonus disponibilité (10% du score max)
            if ($otherRessource->isAvailable()) {
                $similarity += 10;
                $reasons[] = [
                    'icon' => 'fa-check-circle',
                    'text' => "Disponible maintenant",
                    'type' => 'availability'
                ];
            }
    
            // Normalisation du score entre 0-100
            $similarity = min(100, $similarity);
    
            $similarities[] = [
                'ressource' => $otherRessource,
                'similarity' => $similarity,
                'reasons' => $reasons
            ];
        }
    
        // Tri par similarité décroissante
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
    
        // Retourne les $limit meilleures suggestions
        return array_slice($similarities, 0, $limit);
    }

#[Route('/{id}/suggestions', name: 'show_suggestions', methods: ['GET'])]
public function showSuggestions(Ressource $ressource, RessourceRepository $ressourceRepository): Response
{
    $allResources = $ressourceRepository->findAll();
    $mainSuggestions = $this->findSimilarResources($ressource, $allResources);

    // Ajout de suggestions alternatives si nécessaire
    if (count($mainSuggestions) < 3) {
        $fallbacks = $ressourceRepository->findLatestPopular(3 - count($mainSuggestions));
        $mainSuggestions = array_merge($mainSuggestions, $fallbacks);
    }

    return $this->render('ressource/show_suggestions.html.twig', [
        'ressource' => $ressource,
        'similarResources' => array_slice($mainSuggestions, 0, 5),
    ]);
}



}
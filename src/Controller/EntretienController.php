<?php

namespace App\Controller;

use App\Entity\Entretien;
use App\Form\EntretienType;
use App\Repository\EntretienRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Enum\TypeEnt; // Add this
use App\Service\GoogleMeetService; // Add this
use App\Entity\Candidature;
use App\Repository\CandidatureRepository;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/entretien')]
final class EntretienController extends AbstractController
{
    /*#[Route('/candidature/{idCandidature}', name: 'app_entretien_index', methods: ['GET'])]
    public function index(EntretienRepository $entretienRepository, int $idCandidature, CandidatureRepository $candidatureRepository): Response
    {
        $candidature = $candidatureRepository->find($idCandidature);
        return $this->render('entretien/index.html.twig', [
            'candidature' => $candidature,
            'entretiens' => $candidature->getEntretiens()
        ]);
    }*/


    #[Route('/candidature/{idCandidature}', name: 'app_entretien_index', methods: ['GET'])]
    public function index(
        EntretienRepository $entretienRepository, 
        int $idCandidature, 
        CandidatureRepository $candidatureRepository,
        Request $request
    ): Response {
        $candidature = $candidatureRepository->find($idCandidature);
        $localisation = $request->query->get('localisation', '');
        $order = $request->query->get('order', 'ASC');
        $type = $request->query->get('type', '');
        
        $entretiens = $entretienRepository->findByType($idCandidature, $type, $order);
        
        if ($localisation !== '') {
            $entretiens = array_filter($entretiens, function($entretien) use ($localisation) {
                return stripos($entretien->getLocalisation() ?? '', $localisation) !== false;
            });
        }
        
        return $this->render('entretien/index.html.twig', [
            'candidature' => $candidature,
            'entretiens' => $entretiens,
            'localisation' => $localisation,
            'order' => $order,
            'selectedType' => $type
        ]);
    }

    /*#[Route('/entfront', name: 'app_entretien_front', methods: ['GET'])]
public function indexfront(EntretienRepository $entretienRepository): Response
{
    return $this->render('entretien/entfront.html.twig', [
        'entretiens' => $entretienRepository->findAll(),
    ]);
}*/
/*
#[Route('/entfront/{idCandidature}/{page}', name: 'app_entretien_front', defaults: ['page' => 1], methods: ['GET'])]
public function indexfront(
    EntretienRepository $entretienRepository, 
    CandidatureRepository $candidatureRepository,
    int $idCandidature,
    int $page = 1
): Response
{
    // Vérifier que la candidature existe
    $candidature = $candidatureRepository->find($idCandidature);
    if (!$candidature) {
        throw $this->createNotFoundException('Candidature non trouvée');
    }

    $limit = 3; // Nombre d'entretiens par page
    $query = $entretienRepository->createQueryBuilder('e')
        ->andWhere('e.candidature = :candidature')
        ->setParameter('candidature', $candidature)
        ->orderBy('e.date', 'ASC')
        ->getQuery();

    $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    $totalItems = count($paginator);
    $totalPages = ceil($totalItems / $limit);

    $entretiens = $paginator
        ->getQuery()
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit)
        ->getResult();

    return $this->render('entretien/entfront.html.twig', [
        'entretiens' => $entretiens,
        'candidature' => $candidature,
        'page' => $page,
        'totalPages' => $totalPages,
        'idCandidature' => $idCandidature
    ]);
}*/



#[Route('/entfront/{idCandidature}', name: 'app_entretien_front', methods: ['GET'])]
public function indexfront(
    EntretienRepository $entretienRepository,
    CandidatureRepository $candidatureRepository,
    PaginatorInterface $paginator,
    int $idCandidature,
    Request $request
): Response {
    $candidature = $candidatureRepository->find($idCandidature);
    if (!$candidature) {
        throw $this->createNotFoundException('Candidature non trouvée');
    }

    $query = $entretienRepository->createQueryBuilder('e')
        ->andWhere('e.candidature = :candidature')
        ->setParameter('candidature', $candidature)
        ->orderBy('e.date', 'ASC')
        ->getQuery();

    $entretiens = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Numéro de page
        3 // Limite par page
    );

    return $this->render('entretien/entfront.html.twig', [
        'entretiens' => $entretiens,
        'candidature' => $candidature,
    ]);
}

/*#[Route('/new', name: 'app_entretien_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $entretien = new Entretien();
    $form = $this->createForm(EntretienType::class, $entretien);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Validation manuelle avant $form->isValid()
        $errors = $validator->validate($entretien);
        
        if (count($errors) === 0 && $form->isValid()) {
            $entityManager->persist($entretien);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entretien a été créé avec succès.');
            return $this->redirectToRoute('app_entretien_index', [], Response::HTTP_SEE_OTHER);
        } else {
            // Afficher les erreurs de validation
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
    }

    return $this->render('entretien/new.html.twig', [
        'entretien' => $entretien,
        'form' => $form,
    ]);
}*/
/*
#[Route('/new', name: 'app_entretien_new', methods: ['GET', 'POST'])]
public function new(
    Request $request, 
    EntityManagerInterface $entityManager, 
    ValidatorInterface $validator,
    GoogleMeetService $googleMeetService
): Response {
    $entretien = new Entretien();
    $form = $this->createForm(EntretienType::class, $entretien);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        $errors = $validator->validate($entretien);
        
        if (count($errors) === 0 && $form->isValid()) {
            // Si c'est un entretien en ligne, générer le lien Meet
            if ($entretien->getType() === TypeEnt::En_ligne) {
                try {
                    // Convertir la date et l'heure en DateTime pour Google Meet
                    $date = $entretien->getDate();
                    $heure = \DateTime::createFromFormat('H:i', $entretien->getHeure());
                    
                    $startDateTime = new \DateTime(
                        $date->format('Y-m-d') . ' ' . $heure->format('H:i:s')
                    );
                    $endDateTime = clone $startDateTime;
                    $endDateTime->modify('+1 hour');
                    
                    
                    
                    $meetLink = $googleMeetService->createMeetLink(
                        $startDateTime, 
                        $endDateTime,
                     
                    );
                    
                    $entretien->setLienmeet($meetLink);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la création du lien Meet: ' . $e->getMessage());
                    return $this->redirectToRoute('app_entretien_new');
                }
            }

            $entityManager->persist($entretien);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entretien a été créé avec succès.');
            return $this->redirectToRoute('app_entretien_index', [], Response::HTTP_SEE_OTHER);
        } else {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
    }

    return $this->render('entretien/new.html.twig', [
        'entretien' => $entretien,
        'form' => $form,
    ]);
}*/

#[Route('/new/{idCandidature}', name: 'app_entretien_new_for_candidature', methods: ['GET', 'POST'])]
    public function newFromCandidature(
        int $idCandidature,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        GoogleMeetService $googleMeetService
    ): Response {
        $candidature = $em->getRepository(Candidature::class)->find($idCandidature);
        if (!$candidature) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }

        $entretien = new Entretien();
        $entretien->setCandidature($candidature);

        $form = $this->createForm(EntretienType::class, $entretien);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $validator->validate($entretien);
            
            if (count($errors) === 0 && $form->isValid()) {
                if ($entretien->getType() === TypeEnt::En_ligne) {
                    try {
                        $entretien->setLocalisation('null'); // Efface la localisation si en ligne
                        $date = $entretien->getDate();
                        $heure = \DateTime::createFromFormat('H:i', $entretien->getHeure());
                        $startDateTime = new \DateTime($date->format('Y-m-d') . ' ' . $heure->format('H:i:s'));
                        $endDateTime = clone $startDateTime;
                        $endDateTime->modify('+1 hour');

                        $meetLink = $googleMeetService->createMeetLink($startDateTime, $endDateTime);
                        $entretien->setLienmeet($meetLink);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur Meet: ' . $e->getMessage());
                        return $this->redirectToRoute('app_entretien_new_for_candidature', ['idCandidature' => $idCandidature]);
                    }
                }else {
                    $entretien->setLienmeet('null'); // Efface le lien Meet si présentiel
                }

                $em->persist($entretien);
                $em->flush();

                $this->addFlash('success', 'Entretien ajouté à la candidature.');
                return $this->redirectToRoute('app_entretien_index', ['idCandidature' => $idCandidature]);
            }
        }

        return $this->render('entretien/new.html.twig', [
            'form' => $form->createView(),
            'entretien' => $entretien,
            'idCandidature' => $idCandidature,
        ]);
    }


#[Route('/{idEntretien}', name: 'app_entretien_show', methods: ['GET'])]
public function show(Entretien $entretien): Response
{
    $idCandidature = $entretien->getCandidature()->getIdCandidature();
    return $this->render('entretien/show.html.twig', [
        'entretien' => $entretien,
        'idCandidature' => $idCandidature,
    ]);
}

    
    #[Route('/detailfront/{idEntretien}', name: 'app_entretien_show_front', methods: ['GET'])]
    public function showfront(Entretien $entretien): Response
    {
        $idCandidature = $entretien->getCandidature()->getIdCandidature();
        return $this->render('entretien/detailsfr.html.twig', [
            'entretien' => $entretien,
            'idCandidature' => $idCandidature,

        ]);
    }
    #[Route('/{idEntretien}/edit', name: 'app_entretien_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
    {
        $idCandidature = $entretien->getCandidature()->getIdCandidature();
        
        // Formatage de l'heure avant affichage
        if ($entretien->getHeure()) {
            $entretien->setHeure(substr($entretien->getHeure(), 0, 5));
        }

        $form = $this->createForm(EntretienType::class, $entretien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Formatage de l'heure avant enregistrement
            if ($entretien->getHeure()) {
                $entretien->setHeure(substr($entretien->getHeure(), 0, 5));
            }
            
            $entityManager->flush();
            return $this->redirectToRoute('app_entretien_index', ['idCandidature' => $idCandidature]);
        }

        return $this->render('entretien/edit.html.twig', [
            'entretien' => $entretien,
            'form' => $form->createView(),
            'idCandidature' => $idCandidature,
        ]);
    }

#[Route('/{idEntretien}', name: 'app_entretien_delete', methods: ['POST'])]
public function delete(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
{
    $idCandidature = $entretien->getCandidature()->getIdCandidature();
    
    if ($this->isCsrfTokenValid('delete'.$entretien->getIdEntretien(), $request->getPayload()->getString('_token'))) {
        $entityManager->remove($entretien);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_entretien_index', ['idCandidature' => $idCandidature], Response::HTTP_SEE_OTHER);
}


}

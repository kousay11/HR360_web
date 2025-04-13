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

#[Route('/entretien')]
final class EntretienController extends AbstractController
{
    #[Route(name: 'app_entretien_index', methods: ['GET'])]
    public function index(EntretienRepository $entretienRepository): Response
    {
        return $this->render('entretien/index.html.twig', [
            'entretiens' => $entretienRepository->findAll(),
        ]);
    }

    /*#[Route('/entfront', name: 'app_entretien_front', methods: ['GET'])]
public function indexfront(EntretienRepository $entretienRepository): Response
{
    return $this->render('entretien/entfront.html.twig', [
        'entretiens' => $entretienRepository->findAll(),
    ]);
}*/

#[Route('/entfront/{page}', name: 'app_entretien_front', defaults: ['page' => 1], methods: ['GET'])]
public function indexfront(EntretienRepository $entretienRepository, int $page = 1): Response
{
    $limit = 4; // 4 entretiens par page
    $query = $entretienRepository->createQueryBuilder('e')
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
        'page' => $page,
        'totalPages' => $totalPages
    ]);
}

#[Route('/new', name: 'app_entretien_new', methods: ['GET', 'POST'])]
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
}

    #[Route('/{idEntretien}', name: 'app_entretien_show', methods: ['GET'])]
    public function show(Entretien $entretien): Response
    {
        return $this->render('entretien/show.html.twig', [
            'entretien' => $entretien,
        ]);
    }

    
    #[Route('/detailfront/{idEntretien}', name: 'app_entretien_show_front', methods: ['GET'])]
    public function showfront(Entretien $entretien): Response
    {
        return $this->render('entretien/detailsfr.html.twig', [
            'entretien' => $entretien,
        ]);
    }
    #[Route('/{idEntretien}/edit', name: 'app_entretien_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
{
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
        return $this->redirectToRoute('app_entretien_index');
    }

    return $this->render('entretien/edit.html.twig', [
        'entretien' => $entretien,
        'form' => $form->createView(),
    ]);
}

    #[Route('/{idEntretien}', name: 'app_entretien_delete', methods: ['POST'])]
    public function delete(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entretien->getIdEntretien(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($entretien);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_entretien_index', [], Response::HTTP_SEE_OTHER);
    }



}

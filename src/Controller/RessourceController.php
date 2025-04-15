<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

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
public function indexForEmployees(RessourceRepository $ressourceRepository): Response
{
    return $this->render('ressource/index_employee.html.twig', [
        'ressources' => $ressourceRepository->findAll(),
    ]);
}

#[Route('/new', name: 'app_ressource_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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

        // Redirection après la soumission du formulaire
        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('ressource/new.html.twig', [
        'ressource' => $ressource,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_ressource_show', methods: ['GET'])]
public function show(Ressource $ressource): Response
{
    return $this->render('ressource/show.html.twig', [
        'ressource' => $ressource,
        'reservations' => $ressource->getReservations(),
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
    public function delete(Request $request, Ressource $ressource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ressource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ressource_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\UtilisateurEditType;
use App\Form\RegistrationFormType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Route('/employe')]
final class EmployeeController extends AbstractController
{
    // EmployeeController.php (juste la méthode index)
    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository, Request $request): Response
    {
        $criteria = [
            'search' => $request->query->get('search'),
            'min_salary' => $request->query->get('min_salary'),
            'max_salary' => $request->query->get('max_salary'),
            'poste' => $request->query->get('poste'),
            'sort' => $request->query->get('sort', 'nom'),
            'direction' => $request->query->get('direction', 'ASC'),
        ];

        $utilisateurs = $utilisateurRepository->searchEmployees($criteria);
        $postes = $utilisateurRepository->findDistinctPostes();

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'postes' => $postes,
            'search_params' => $criteria,
        ]);
    }




    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ParameterBagInterface $params,
        SluggerInterface $slugger
    ): Response {
        //$this->denyAccessUnlessGranted('ROLE_RESPONSABLE_RH');

        $utilisateur = new Utilisateur();
        $utilisateur->setRole('Employe'); // ✅ on définit le rôle par défaut à "Employe"
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $utilisateur->setPassword(
                    $passwordHasher->hashPassword($utilisateur, $plainPassword)
                );
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                dump([
                    'name' => $imageFile->getClientOriginalName(),
                    'mime' => $imageFile->getMimeType(),
                    'size' => $imageFile->getSize(),
                    'extension' => $imageFile->guessExtension()
                ]);
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($params->get('images_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                }

                $utilisateur->setImage($newFilename);
            } else {
                $utilisateur->setImage('default.jpg');
            }

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Employé créé avec succès');
            return $this->redirectToRoute('app_utilisateur_index');
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ParameterBagInterface $params,
        SluggerInterface $slugger
    ): Response {


        $form = $this->createForm(UtilisateurEditType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($utilisateur->getImage() && $utilisateur->getImage() !== 'default.jpg') {
                    $oldImagePath = $params->get('images_directory') . '/' . $utilisateur->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $params->get('images_directory'),
                        $newFilename
                    );
                    $utilisateur->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_utilisateur_edit', ['id' => $utilisateur->getId()]);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Employé mis à jour avec succès');
            return $this->redirectToRoute('app_utilisateur_index');
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/upload-image', name: 'app_utilisateur_upload_image', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        SluggerInterface $slugger
    ): JsonResponse {
        $imageFile = $request->files->get('image');

        if (!$imageFile) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé'], 400);
        }

        // Vérification du type MIME
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Format d\'image non valide'], 400);
        }

        // Supprimer l'ancienne image si elle existe
        if ($utilisateur->getImage() && $utilisateur->getImage() !== 'default.jpg') {
            $oldImagePath = $params->get('images_directory') . '/' . $utilisateur->getImage();
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $params->get('images_directory'),
                $newFilename
            );
            $utilisateur->setImage($newFilename);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'imageUrl' => $this->generateUrl('app_images', ['filename' => $newFilename])
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Erreur lors du téléchargement'], 500);
        }
    }


    #[Route('/uploads/images/{filename}', name: 'app_images')]
    public function displayImage($filename, ParameterBagInterface $params): Response
    {
        $path = $params->get('images_directory') . '/' . $filename;

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Image non trouvée');
        }

        return new BinaryFileResponse($path);
    }


    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            if ($utilisateur->getImage() && $utilisateur->getImage() !== 'default.jpg') {
                $imagePath = $params->get('images_directory') . '/' . $utilisateur->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Employé supprimé avec succès');
        }

        return $this->redirectToRoute('app_utilisateur_index');
    }
}






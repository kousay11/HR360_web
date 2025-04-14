<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
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

#[Route('/employe')]
final class EmployeeController extends AbstractController
{
    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository, Request $request): Response
    {
        $search = $request->query->get('search');

        $queryBuilder = $utilisateurRepository->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'Employe'); // ✅ on filtre que les employés

        if ($search) {
            $queryBuilder
                ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $utilisateurs = $queryBuilder
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'search' => $search,
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


        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $utilisateur->setPassword(
                    $passwordHasher->hashPassword($utilisateur, $plainPassword)
                );
            }

            // Gestion de l'image
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

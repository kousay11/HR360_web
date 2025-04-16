<?php



namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProfilType;
use App\Form\ProfilEmpType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CandidatController extends AbstractController
{
    #[Route('/profilCan', name: 'app_candidat_profil', methods: ['GET', 'POST'])]
    public function profilCan(): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie qu'un utilisateur est bien connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('utilisateur/profilCan.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/profilEmp', name: 'app_employe_profil', methods: ['GET', 'POST'])]
    public function profilEmp(): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie qu'un utilisateur est bien connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('utilisateur/profilEmp.html.twig', [
            'user' => $user,
        ]);
    }



    #[Route('/profil/{id}/editCan', name: 'app_candidat_edit', methods: ['GET', 'POST'])]
    public function editCan(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ParameterBagInterface $params,
        Utilisateur $utilisateur
    ): Response {


        $form = $this->createForm(ProfilType::class, $utilisateur);
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
                    return $this->redirectToRoute('app_candidat_edit', ['id' => $utilisateur->getId()]);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès');
            return $this->redirectToRoute('app_candidat_profil');
        }

        return $this->render('utilisateur/editProfil.html.twig', [
            'form' => $form->createView(),
            'user' => $utilisateur
        ]);
    }


    #[Route('/profil/{id}/editEmp', name: 'app_employe_edit', methods: ['GET', 'POST'])]
    public function editEmp(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ParameterBagInterface $params,
        Utilisateur $utilisateur
    ): Response {


        $form = $this->createForm(ProfilEmpType::class, $utilisateur);
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
                    return $this->redirectToRoute('app_employe_edit', ['id' => $utilisateur->getId()]);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès');
            return $this->redirectToRoute('app_employe_profil');
        }

        return $this->render('utilisateur/editProfilEmp.html.twig', [
            'form' => $form->createView(),
            'user' => $utilisateur
        ]);
    }



}

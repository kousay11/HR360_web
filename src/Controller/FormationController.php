<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/formation')]
final class FormationController extends AbstractController
{
    // ----------------- BACK -----------------
    #[Route('/back', name: 'app_formation_index', methods: ['GET'])]
    public function index(Request $request, FormationRepository $formationRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 5;

        $criteria = [
            'search' => $request->query->get('search'),
            'min_duration' => $request->query->get('min_duration'),
            'max_duration' => $request->query->get('max_duration'),
            'date_from' => $request->query->get('date_from'),
            'date_to' => $request->query->get('date_to'),
            'sort' => $request->query->get('sort', 'titre'),
            'direction' => $request->query->get('direction', 'ASC'),
        ];

        $result = $formationRepository->searchFormations($criteria, true, $page, $limit);

        return $this->render('formation/back/index.html.twig', [
            'formations' => $result['results'],
            'search_params' => $criteria,
            'page' => $page,
            'total' => $result['total'],
            'limit' => $limit,
        ]);
    }

    #[Route('/back/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/back/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/back/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/back/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/back/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/back/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/back/{id}/delete', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($formation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }

    
    // ----------------- FRONT -----------------
    #[Route('/front', name: 'app_formation_front_index', methods: ['GET'])]
    public function frontIndex(Request $request, FormationRepository $formationRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 3;

        $criteria = [
            'search' => $request->query->get('search'),
            'favorites' => $request->query->getBoolean('favorites'),
            'min_duration' => $request->query->get('min_duration'),
            'max_duration' => $request->query->get('max_duration'),
            'sort' => $request->query->get('sort', 'titre'),
            'direction' => $request->query->get('direction', 'ASC'),
        ];

        $result = $formationRepository->searchFrontFormations($criteria, true, $page, $limit);

        return $this->render('formation/front/index.html.twig', [
            'formations' => $result['results'],
            'search_params' => $criteria,
            'page' => $page,
            'total' => $result['total'],
            'limit' => $limit,
        ]);
    }


    #[Route('/front/favorites', name: 'app_formation_favorites', methods: ['GET'])]
    public function favorites(Request $request, FormationRepository $formationRepository): Response
    {
        $search = $request->query->get('search');
        $page = $request->query->getInt('page', 1);
        $limit = 3;

        // Debug: Vérifiez ce que la requête retourne
        $queryBuilder = $formationRepository->createQueryBuilder('f')
            ->where('f.isFavorite = :isFavorite')
            ->setParameter('isFavorite', true);

        if ($search) {
            $queryBuilder->andWhere('f.titre LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();

        // Debug: Affichez la requête SQL générée
        // dd($query->getSQL(), $query->getParameters());

        $paginator = new Paginator($query);
        $total = count($paginator);

        $formations = $query
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getResult();

        // Debug: Vérifiez les formations retournées
        // dd($formations);

        return $this->render('formation/front/favorites.html.twig', [
            'formations' => $formations,
            'search' => $search,
            'page' => $page,
            'total' => $total,
            'limit' => $limit
        ]);
    }

    #[Route('/front/{id}/toggle-favorite', name: 'app_formation_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-favorite' . $formation->getId(), $request->request->get('_token'))) {
            $formation->setIsFavorite(!$formation->isIsFavorite());
            $entityManager->flush();

            $this->addFlash('success', $formation->isIsFavorite() ?
                'Formation ajoutée aux favoris' : 'Formation retirée des favoris');
        }

        // Redirection vers la page précédente ou vers les favoris
        $redirectTo = $request->request->get('redirect_to', $this->generateUrl('app_formation_favorites'));
        return $this->redirect($redirectTo);
    }

    #[Route('/frontnew', name: 'app_formation_front_new', methods: ['GET', 'POST'])]
    public function frontNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump($form->isValid()); // Vérifiez si le formulaire est valide
            dump($form->getErrors(true)); // Affichez les erreurs éventuelles

            if ($form->isValid()) {
                $entityManager->persist($formation);
                $entityManager->flush();
                $this->addFlash('success', 'Formation créée avec succès');
                return $this->redirectToRoute('app_formation_front_index');
            }

            $this->addFlash('error', 'Il y a des erreurs dans le formulaire');
        }

        return $this->render('formation/front/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/{id}', name: 'app_formation_front_show', methods: ['GET'])]
    public function frontShow(Formation $formation): Response
    {
        return $this->render('formation/front/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/front/{id}/edit', name: 'app_formation_front_edit', methods: ['GET', 'POST'])]
    public function frontEdit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_front_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/front/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/front/{id}/delete', name: 'app_formation_front_delete', methods: ['GET'])]
    public function frontDelete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        // Note: Ce n'est pas sécurisé sans token CSRF en GET
        $entityManager->remove($formation);
        $entityManager->flush();

        return $this->redirectToRoute('app_formation_front_index', [], Response::HTTP_SEE_OTHER);
    }
}

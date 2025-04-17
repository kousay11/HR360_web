<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Repository\EquipeRepository;
use App\Repository\ProjetequipeRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/projetFront')]
final class ProjetFrontController extends AbstractController
{
    #[Route('/',name: 'app_projet_front_index', methods: ['GET'])]
    public function index(Request $request, ProjetRepository $projetRepository): Response
    {
        return $this->search($request, $projetRepository);
    }
    #[Route('/search', name: 'app_projet_front_search', methods: ['GET'])]
    public function search(Request $request, ProjetRepository $projetRepository): Response
    {
        $query = $request->query->get('q', '');
        $projets = $projetRepository->searchFront($query,$this->getUser());

        if ($request->isXmlHttpRequest()) {
            return $this->render('projet_front/_project_grid.html.twig', [
                'projets' => $projets,
            ]);
        }

        return $this->render('projet_front/index.html.twig', [
            'projets' => $projets,
        ]);
    }
    #[Route('/prioritize', name: 'app_projet_front_prioritize', methods: ['GET'])]
    public function prioritize(Request $request, ProjetRepository $projetRepository): Response
    {
        $projets = $projetRepository->prioritizeFront($this->getUser());

        if ($request->isXmlHttpRequest()) {
            return $this->render('projet_front/_project_grid.html.twig', [
                'projets' => $projets,
            ]);
        }

        return $this->render('projet_front/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/{id}', name: 'app_projet_front_show', methods: ['GET'])]
    public function show(Projet $projet,ProjetequipeRepository $projetequipeRepository): Response
    {
        // Initialize counts
        $counts = [
            'a_faire' => 0,
            'en_cours' => 0,
            'terminee' => 0,
            'total' => 0,
        ];

        foreach ($projet->getTaches() as $tache) {
            $counts['total']++;
            switch ($tache->getStatut()->value) {
                case 'A faire': $counts['a_faire']++; break;
                case 'En cours': $counts['en_cours']++; break;
                case 'Terminée': $counts['terminee']++; break;
            }
        }

        // Calculate completion percentage
        $completionPercentage = $counts['total'] > 0 
            ? (($counts['a_faire'] * 0 + $counts['en_cours'] * 0.5 + $counts['terminee'] * 1) / $counts['total'] * 100)
            : 0;
        $completionPercentage = round($completionPercentage, 2);
        $projetequipe = $projetequipeRepository->findBy(['projet' => $projet]);
        $equipe = null;
        if (!empty($projetequipe)) {
            $equipe = $projetequipe[0]->getEquipe(); // Access the first element
        }
        return $this->render('projet_front/show.html.twig', [
            'projet' => $projet,
            'taches' => $projet->getTaches(),
            'counts' => $counts,
            'completionPercentage' => $completionPercentage,
            'equipe' => $equipe,
        ]);
    }
}


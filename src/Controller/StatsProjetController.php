<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/statsProjet')]
final class StatsProjetController extends AbstractController
{

    #[Route('/stats', name: 'app_projet_stats', methods: ['GET'])]
    public function stats(ProjetRepository $projetRepository, TacheRepository $tacheRepository, EntityManagerInterface $em): Response
    {
        // Statistique 1: Nombre de projets par équipe
        $projetsParEquipe = $projetRepository->countProjetsParEquipe();

        // Statistique 2: Top 6 projets par nombre de tâches
        $topProjetsParTaches = $tacheRepository->countTachesParProjet(6);

        // Statistique 3: Évolution des projets dans le temps
        $evolutionProjets = $projetRepository->evolutionProjetsParMois($em);

        return $this->render('projet/stats.html.twig', [
            'projetsParEquipe' => $projetsParEquipe,
            'topProjetsParTaches' => $topProjetsParTaches,
            'evolutionProjets' => $evolutionProjets,
        ]);
    }

    #[Route('/stats/export', name: 'app_projet_stats_export', methods: ['GET'])]
    public function exportStats(ProjetRepository $projetRepository, TacheRepository $tacheRepository, EntityManagerInterface $em): Response
    {
        try {
            // 1. Récupérer les données
            $projetsParEquipe = $projetRepository->countProjetsParEquipe();
            $topProjetsParTaches = $tacheRepository->countTachesParProjet(6);
            $evolutionProjets = $projetRepository->evolutionProjetsParMoisExcel($em);


            // 2. Créer un nouveau Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // 3. Ajouter un titre général
            $sheet->setCellValue('A1', 'Rapport des Statistiques de Projets');
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

            // 4. Ajouter les données pour le graphique "Projets par équipe" (Bar Chart)
            $sheet->setCellValue('A3', 'Projets par Équipe');
            $sheet->getStyle('A3')->getFont()->setBold(true);

            $sheet->setCellValue('A4', 'Équipe');
            $sheet->setCellValue('B4', 'Nombre de Projets');
            $sheet->getStyle('A4:B4')->getFont()->setBold(true);

            $row = 5;
            foreach ($projetsParEquipe as $item) {
                $sheet->setCellValue('A' . $row, $item['equipe_nom']);
                $sheet->setCellValue('B' . $row, $item['count']);
                $row++;
            }
            // Ajouter cette ligne avant de créer le graphique
            $sheet->getStyle('B5:B' . ($row - 1))->getNumberFormat()->setFormatCode('0');
            // Avant de créer les DataSeries
            foreach ($projetsParEquipe as $index => $item) {
                $color = sprintf('%06X', mt_rand(0, 0xFFFFFF));
                $sheet->getStyle('B' . ($index + 5))->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
            }
            // 5. Créer un graphique "Projets par équipe" (Bar Chart)

            $seriesProjets = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_STANDARD,
                [0],
                [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$5:$A$' . ($row - 1), null, count($projetsParEquipe))],
                [],
                [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$B$5:$B$' . ($row - 1), null, count($projetsParEquipe))]
            );

            $plotArea = new PlotArea(null, [$seriesProjets]);
            $chart = new Chart(
                'Projets par Équipe',
                new Title('Projets par Équipe'),
                null,
                $plotArea
            );

            $chart->setTopLeftPosition('D3');
            $chart->setBottomRightPosition('M20');
            $sheet->addChart($chart);

            // 6. Ajouter les données pour le graphique "Tâches par projet" (Horizontal Bar Chart)
            $sheet->setCellValue('A22', 'Tâches par Projet (Top 6)');
            $sheet->getStyle('A22')->getFont()->setBold(true);

            $sheet->setCellValue('A23', 'Projet');
            $sheet->setCellValue('B23', 'Nombre de Tâches');
            $sheet->getStyle('A23:B23')->getFont()->setBold(true);

            $rowTasks = 24;
            foreach ($topProjetsParTaches as $item) {
                $sheet->setCellValue('A' . $rowTasks, $item['projet_nom']);
                $sheet->setCellValue('B' . $rowTasks, $item['count']);
                $rowTasks++;
            }

            // 7. Créer un graphique "Tâches par projet" (Horizontal Bar Chart)

            $seriesTaches = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_STANDARD,
                [0],
                [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$24:$A$' . ($rowTasks - 1), null, count($topProjetsParTaches))],
                [],
                [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$B$24:$B$' . ($rowTasks - 1), null, count($topProjetsParTaches))],
                DataSeries::DIRECTION_HORIZONTAL
            );

            $plotAreaTasks = new PlotArea(null, [$seriesTaches]);
            $chartTasks = new Chart(
                'Tâches par Projet',
                new Title('Tâches par Projet (Top 6)'),
                null,
                $plotAreaTasks
            );

            $chartTasks->setTopLeftPosition('D22');
            $chartTasks->setBottomRightPosition('M40');
            $sheet->addChart($chartTasks);
            // 3. Graphique Évolution des Projets (Line Chart)
            $rowEvol = 5; // Commencer à la ligne 5
            foreach ($evolutionProjets as $item) {
                $sheet->setCellValue('G' . $rowEvol, $item['mois']);
                $sheet->setCellValue('H' . $rowEvol, $item['debut']);
                $sheet->setCellValue('I' . $rowEvol, $item['fin']);
                $rowEvol++;
            }
            
            $seriesEvolution = new DataSeries(
                DataSeries::TYPE_LINECHART,
                DataSeries::GROUPING_STANDARD,
                range(0, 1), // Deux séries (début et fin)
                [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$G$5:$G$' . ($rowEvol - 1), null, count($evolutionProjets))],
                [],
                [
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$H$5:$H$' . ($rowEvol - 1), null, count($evolutionProjets)),
                    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$I$5:$I$' . ($rowEvol - 1), null, count($evolutionProjets))
                ]
            );
            
            $plotAreaEvol = new PlotArea(null, [$seriesEvolution]);
            $chartEvol = new Chart(
                'Évolution des Projets',
                new Title('Évolution des Projets'),
                null,
                $plotAreaEvol
            );
            $chartEvol->setTopLeftPosition('D50');
            $chartEvol->setBottomRightPosition('M80');
            $sheet->addChart($chartEvol);
            // 8. Générer le fichier Excel
            $writer = new Xlsx($spreadsheet);

            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->setIncludeCharts(true);
                    $writer->save('php://output');
                }
            );

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="project_stats.xlsx"');

            return $response;
        } catch (\Exception $e) { // Log l'erreur
            error_log($e->getMessage());

            // Retourne une réponse d'erreur
            return new Response(
                'Une erreur est survenue lors de la génération du fichier Excel: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
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
            
            // Style général
            $sheet->getDefaultColumnDimension()->setWidth(10);
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '2C3E50']],
                'alignment' => ['horizontal' => 'center'],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'ECF0F1']]
            ];
            $titleStyle = [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '3498DB']],
                'alignment' => ['horizontal' => 'center']
            ];

            // 3. Titre général
            $sheet->setCellValue('A1', 'Rapport des Statistiques de Projets');
            $sheet->mergeCells('A1:P1');
            $sheet->getStyle('A1')->applyFromArray($titleStyle);

            // 4. Projets par équipe
            $sheet->setCellValue('A3', 'Projets par Équipe');
            $sheet->getStyle('A3')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);

            $sheet->setCellValue('A4', 'Équipe');
            $sheet->setCellValue('B4', 'Nombre de Projets');
            $sheet->getStyle('A4:B4')->applyFromArray($headerStyle);

            // Générer des couleurs distinctes
            $colors = [
                '3498DB', 'E74C3C', '2ECC71', 'F1C40F', '9B59B6', 
                '1ABC9C', 'E67E22', '34495E', '7F8C8D', '16A085'
            ];

            $row = 5;
            foreach ($projetsParEquipe as $index => $item) {
                $sheet->setCellValue('A' . $row, $item['equipe_nom']);
                $sheet->setCellValue('B' . $row, $item['count']);
                $color = $colors[$index % count($colors)];
                $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
                $row++;
            }

            // Graphique Projets par équipe
            $dataSeriesLabels1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$B$4', null, 1)
            ];
            $xAxisTickValues1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$5:$A$' . ($row - 1), null, count($projetsParEquipe))
            ];
            $dataSeriesValues1 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$B$5:$B$' . ($row - 1), null, count($projetsParEquipe))
            ];
            
            foreach ($dataSeriesValues1 as $i => $series) {
                $series->setFillColor($colors);
            }

            $series1 = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                range(0, count($dataSeriesValues1) - 1),
                $dataSeriesLabels1,
                $xAxisTickValues1,
                $dataSeriesValues1
            );

            $layout1 = new Layout();
            $layout1->setShowVal(true);
            
            $plotArea1 = new PlotArea($layout1, [$series1]);
            $legend1 = new Legend(Legend::POSITION_RIGHT, null, false);
            
            $chart1 = new Chart(
                'Projets par Équipe',
                new Title('Projets par Équipe'),
                $legend1,
                $plotArea1
            );

            $chart1->setTopLeftPosition('D3');
            $chart1->setBottomRightPosition('M20');
            $sheet->addChart($chart1);

            // 5. Tâches par projet
            $sheet->setCellValue('A22', 'Tâches par Projet (Top 6)');
            $sheet->getStyle('A22')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);

            $sheet->setCellValue('A23', 'Projet');
            $sheet->setCellValue('B23', 'Nombre de Tâches');
            $sheet->getStyle('A23:B23')->applyFromArray($headerStyle);

            $rowTasks = 24;
            foreach ($topProjetsParTaches as $index => $item) {
                $sheet->setCellValue('A' . $rowTasks, $item['projet_nom']);
                $sheet->setCellValue('B' . $rowTasks, $item['count']);
                $color = $colors[$index % count($colors)];
                $sheet->getStyle('A' . $rowTasks . ':B' . $rowTasks)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
                $rowTasks++;
            }

            // Graphique Tâches par projet
            $dataSeriesLabels2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$B$23', null, 1)
            ];
            $xAxisTickValues2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$24:$A$' . ($rowTasks - 1), null, count($topProjetsParTaches))
            ];
            $dataSeriesValues2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$B$24:$B$' . ($rowTasks - 1), null, count($topProjetsParTaches))
            ];

            foreach ($dataSeriesValues2 as $series) {
                $series->setFillColor(array_slice($colors, 0, count($topProjetsParTaches)));
            }

            $series2 = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                range(0, count($dataSeriesValues2) - 1),
                $dataSeriesLabels2,
                $xAxisTickValues2,
                $dataSeriesValues2,
                DataSeries::DIRECTION_HORIZONTAL
            );

            $layout2 = new Layout();
            $layout2->setShowVal(true);
            
            $plotArea2 = new PlotArea($layout2, [$series2]);
            $legend2 = new Legend(Legend::POSITION_RIGHT, null, false);
            
            $chart2 = new Chart(
                'Tâches par Projet',
                new Title('Tâches par Projet (Top 6)'),
                $legend2,
                $plotArea2
            );

            $chart2->setTopLeftPosition('D22');
            $chart2->setBottomRightPosition('M40');
            $sheet->addChart($chart2);

            // 6. Évolution des projets
            $sheet->setCellValue('A42', 'Évolution des Projets');
            $sheet->getStyle('A42')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);

            $sheet->setCellValue('A43', 'Mois');
            $sheet->setCellValue('B43', 'Projets Débutés');
            $sheet->setCellValue('C43', 'Projets Terminés');
            $sheet->getStyle('A43:C43')->applyFromArray($headerStyle);

            $rowEvol = 44;
            foreach ($evolutionProjets as $item) {
                $sheet->setCellValue('A' . $rowEvol, $item['mois']);
                $sheet->setCellValue('B' . $rowEvol, $item['debut']);
                $sheet->setCellValue('C' . $rowEvol, $item['fin']);
                $rowEvol++;
            }

            // Graphique Évolution des projets
            $dataSeriesLabels3 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$B$43', null, 1),
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$C$43', null, 1),
            ];
            $xAxisTickValues3 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$44:$A$' . ($rowEvol - 1), null, count($evolutionProjets))
            ];
            $dataSeriesValues3 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$B$44:$B$' . ($rowEvol - 1), null, count($evolutionProjets)),
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$C$44:$C$' . ($rowEvol - 1), null, count($evolutionProjets))
            ];

            $dataSeriesValues3[0]->setFillColor(['3498DB']);
            $dataSeriesValues3[1]->setFillColor(['E74C3C']);

            $series3 = new DataSeries(
                DataSeries::TYPE_LINECHART,
                DataSeries::GROUPING_STANDARD,
                range(0, count($dataSeriesValues3) - 1),
                $dataSeriesLabels3,
                $xAxisTickValues3,
                $dataSeriesValues3
            );

            $layout3 = new Layout();
            $layout3->setShowVal(true);
            
            $plotArea3 = new PlotArea($layout3, [$series3]);
            $legend3 = new Legend(Legend::POSITION_RIGHT, null, false);
            
            $chart3 = new Chart(
                'Évolution des Projets',
                new Title('Évolution des Projets par Mois'),
                $legend3,
                $plotArea3
            );

            $chart3->setTopLeftPosition('D42');
            $chart3->setBottomRightPosition('M60');
            $sheet->addChart($chart3);

            // Ajustement automatique des colonnes
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Générer le fichier Excel
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);

            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="project_stats.xlsx"');

            return $response;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new Response(
                'Une erreur est survenue lors de la génération du fichier Excel: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

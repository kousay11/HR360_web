<?php

namespace App\Service;

use App\Entity\Ressource;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\ReservationRepository;

class QRCodeService
{
    private $params;
    private $reservationRepository;
    public function __construct(ParameterBagInterface $params, ReservationRepository $reservationRepository)
    {
        $this->params = $params;
        $this->reservationRepository = $reservationRepository;
    }

    public function generateQRCodeForRessource(Ressource $ressource): void
    {

        $dates = $this->reservationRepository->findDatesByRessourceId($ressource);


        $data = "📌 RESSOURCE RÉSERVÉE 📌\n".
        "📛 Nom : ".$ressource->getNom()."\n".
        "📂 Type : ".$ressource->getType()."\n".
        "📌 État : ".$ressource->getEtat()."\n".
        "💰 Prix : ".$ressource->getPrix()." DT\n".
        "📅 Réservations :\n".$dates;







        
        $projectDir = $this->params->get('kernel.project_dir');
        $qrDirectory = $projectDir.'/public/uploads/qrcodes/';
        $filename = "qr_ressource_" . $ressource->getId() . ".png";
        $filesystem = new Filesystem();
        if (!$filesystem->exists($qrDirectory)) {
            $filesystem->mkdir($qrDirectory, 0755);
        }

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->build();

        $result->saveToFile($qrDirectory.$filename);

    }
}
<?php

namespace App\Service;

use App\Entity\Ressource;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class QRCodeService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function generateQRCodeForRessource(Ressource $ressource): void
    {
        $data = "Ressource: ".$ressource->getNom()." | ID: ".$ressource->getId();
        
        $projectDir = $this->params->get('kernel.project_dir');
        $qrDirectory = $projectDir.'/public/qrcodes/';
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
<?php
namespace App\Service;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;

class OffreExpirationNotifier
{
    private $offreRepository;
    private $entityManager;
    private $twilioService;

    public function __construct(
        OffreRepository $offreRepository,
        EntityManagerInterface $entityManager,
        TwilioService $twilioService
    ) {
        $this->offreRepository = $offreRepository;
        $this->entityManager = $entityManager;
        $this->twilioService = $twilioService;
    }

    public function checkExpiringOffres(): void
    {
        $offres = $this->offreRepository->findOffresExpiringIn(1); // 1 jour
        
        foreach ($offres as $offre) {
            $this->sendExpirationNotification($offre);
        }
    }

    private function sendExpirationNotification(Offre $offre): void
    {
        $message = sprintf(
            "L'offre '%s' expire demain (%s). Dernière chance pour postuler!",
            $offre->getTitre(),
            $offre->getDateExpiration()->format('d/m/Y')
        );

        $this->twilioService->sendSms('+21625350650', $message);
    }
}
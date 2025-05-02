<?php



// src/Command/EntretienReminderCommand.php
namespace App\Command;

use App\Entity\Entretien;
use App\Repository\EntretienRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntretienReminderCommand extends Command
{
    protected static $defaultName = 'app:entretien-reminder';

    public function __construct(
        private EntretienRepository $entretienRepository,
        private ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
{
    $entretiens = $this->entretienRepository->findEntretiensInNext24Hours();
    $storageDir = $this->params->get('kernel.project_dir').'/var/storage';
    
    if (!file_exists($storageDir)) {
        mkdir($storageDir, 0777, true);
    }

    $notifications = [];
    foreach ($entretiens as $entretien) {
        // Utiliser uniquement les informations de l'entretien lui-même
        $notifications[] = [
            'message' => sprintf(
                'Entretien %s prévu le %s à %s',
                $entretien->getType() ? $entretien->getType()->value : '',
                $entretien->getDate()->format('d/m/Y'),
                $entretien->getHeure()
            ),
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'entretien_id' => $entretien->getIdEntretien()
        ];

        $output->writeln(sprintf(
            'Notification pour entretien %s prévu le %s à %s',
            $entretien->getType() ? $entretien->getType()->value : '',
            $entretien->getDate()->format('d/m/Y'),
            $entretien->getHeure()
        ));
    }

    file_put_contents(
        $this->params->get('kernel.project_dir').'/var/storage/entretien_notifications.json',
        json_encode($notifications, JSON_PRETTY_PRINT)
    );

    return Command::SUCCESS;
}
}
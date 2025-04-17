<?php
// src/Command/CheckOffreExpirationCommand.php
namespace App\Command;

use App\Service\OffreExpirationNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckOffreExpirationCommand extends Command
{
    protected static $defaultName = 'app:check-offre-expiration';
    
    private $notifier;

    public function __construct(OffreExpirationNotifier $notifier)
    {
        $this->notifier = $notifier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notifier->checkExpiringOffres();
        $output->writeln('Vérification des offres expirantes terminée.');
        
        return Command::SUCCESS;
    }
}
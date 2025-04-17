<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use App\Repository\OffreRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-offer-status',
    description: 'Update expired offers status',
)]
class UpdateOfferStatusCommand extends Command
{
    public function __construct(
        private OffreRepository $offreRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->offreRepository->updateExpiredStatus();
        $output->writeln('Offers status updated successfully!');
        return Command::SUCCESS;
    }
}

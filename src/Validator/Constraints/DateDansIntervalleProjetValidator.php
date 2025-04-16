<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Entity\Tache;

class DateDansIntervalleProjetValidator extends ConstraintValidator
{
    public function validate($tache, Constraint $constraint): void
    {
        if (!$tache instanceof Tache || !$constraint instanceof DateDansIntervalleProjet) {
            return;
        }

        $projet = $tache->getProjet();
        
        if (!$projet || !$projet->getDateDebut() || !$projet->getDateFin()) {
            return;
        }

        if ($tache->getDateDebut() < $projet->getDateDebut()) {
            $this->context->buildViolation($constraint->messageDebut)
                ->setParameter('%date_debut_projet%', $projet->getDateDebut()->format('d/m/Y'))
                ->setParameter('%date_fin_projet%', $projet->getDateFin()->format('d/m/Y'))
                ->atPath('dateDebut')
                ->addViolation();
        }

        if ($tache->getDateFin() > $projet->getDateFin()) {
            $this->context->buildViolation($constraint->messageFin)
                ->setParameter('%date_debut_projet%', $projet->getDateDebut()->format('d/m/Y'))
                ->setParameter('%date_fin_projet%', $projet->getDateFin()->format('d/m/Y'))
                ->atPath('dateFin')
                ->addViolation();
        }
    }
}
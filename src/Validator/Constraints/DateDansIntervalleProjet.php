<?php
namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class DateDansIntervalleProjet extends Constraint
{
    public string $messageDebut = 'La date de début doit être comprise entre %date_debut_projet% et %date_fin_projet%';
    public string $messageFin = 'La date de fin doit être comprise entre %date_debut_projet% et %date_fin_projet%';
    
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
<?php
 namespace App\Enum;

enum Statut: string
{
    case Planifié = 'Planifié';
    case Terminé = 'Terminé';
    case Annulé = 'Annulé';
    case Reporté = 'Reporté';
    
    public function getLabel(): string
    {
        return match($this) {
            self::Planifié => 'Planifié',
            self::Terminé => 'Terminé',
            self::Annulé => 'Annulé',
            self::Reporté => 'Reporté',

        };
    }

    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        return null;
    }
}
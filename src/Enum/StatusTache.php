<?php
 namespace App\Enum;

enum StatusTache: string
{
    case AFAIRE = 'A faire';
    case ENCOURS = 'En cours';
    case TERMINEE = 'Terminée';
    
    public function getLabel(): string
    {
        return match($this) {
            self::AFAIRE => 'A faire',
            self::ENCOURS => 'En cours',
            self::TERMINEE => 'Terminée'
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
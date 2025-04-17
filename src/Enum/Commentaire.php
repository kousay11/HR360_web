<?php
 namespace App\Enum;

enum Commentaire: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case ACCEPTE = 'ACCEPTE';
    case REFUSE = 'REFUSE';
    
    public function getLabel(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'EN_ATTENTE',
            self::ACCEPTE => 'ACCEPTE',
            self::REFUSE => 'REFUSE',

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
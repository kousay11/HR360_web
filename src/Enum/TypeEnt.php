<?php
 namespace App\Enum;

enum TypeEnt: string
{
    case En_ligne = 'En_ligne';
    case Presentiel = 'Presentiel';

    public function getLabel(): string
    {
        return match($this) {
            self::En_ligne => 'En_ligne',
            self::Presentiel => 'Presentiel',
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
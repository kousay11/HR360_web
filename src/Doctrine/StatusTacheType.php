<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use App\Enum\StatusTache; // Assure-toi que l'énumération TypeInterview est bien définie.
use InvalidArgumentException;

class StatusTacheType extends Type // Modifie le nom de la classe en TypeInterviewType pour respecter la convention.
{
    const NAME = 'statustache'; // Le nom du type personnalisé.

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('A faire', 'En cours', 'Terminée')"; // Valeurs de l'énumération.
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?StatusTache
    {
        // Vérifie si la valeur est non nulle et retourne une instance de l'énumération.
        return $value !== null ? StatusTache::tryFrom($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        // Assure-toi que la valeur est une instance de l'énumération avant de la convertir.
        if (!$value instanceof StatusTache) {
            throw new InvalidArgumentException("Invalid enum value.");
        }

        return $value->value; // Retourne la valeur de l'énumération pour stocker dans la base.
    }

    public function getName(): string
    {
        return self::NAME; // Le nom du type, utilisé dans la configuration Doctrine.
    }
}
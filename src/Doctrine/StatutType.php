<?php

namespace App\Doctrine;

use App\Enum\Statut;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class StatutType extends Type
{
    const NAME = 'statut'; // The name of your custom type

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('Planifié', 'Terminé','Annulé', 'Reporté')"; // Enum values
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Statut
    {
        // Use TypeEnt::tryFrom() instead of Type::tryFrom()
        return $value !== null ? Statut::tryFrom($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // Check if the value is an instance of TypeEnt
        if (!$value instanceof Statut) {
            throw new InvalidArgumentException("Invalid enum value.");
        }

        // Access the value property of the enum
        return $value->value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
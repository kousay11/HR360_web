<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use App\Enum\TypeEnt; // Make sure this is the correct namespace for your enum
use InvalidArgumentException;

class TypeType extends Type
{
    const NAME = 'type'; // The name of your custom type

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('En_ligne', 'Presentiel')"; // Enum values
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TypeEnt
    {
        // Use TypeEnt::tryFrom() instead of Type::tryFrom()
        return $value !== null ? TypeEnt::tryFrom($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // Check if the value is an instance of TypeEnt
        if (!$value instanceof TypeEnt) {
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
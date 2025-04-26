<?php

namespace App\Doctrine;

use App\Enum\Commentaire;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class CommentaireType extends Type
{
    const NAME = 'commentaire'; // The name of your custom type

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('EN_ATTENTE', 'ACCEPTE', 'REFUSE')"; // Enum values
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Commentaire
    {
        // Use TypeEnt::tryFrom() instead of Type::tryFrom()
        return $value !== null ? Commentaire::tryFrom($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // Check if the value is an instance of TypeEnt
        if (!$value instanceof Commentaire) {
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
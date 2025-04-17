<?php

// src/Form/DataTransformer/DateToStringTransformer.php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateToStringTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        return $value ?? '';
    }

    public function reverseTransform($value): ?string
    {
        // Vérifie format JJ-MM-AAAA
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            return null;
        }

        return $value;
    }
}

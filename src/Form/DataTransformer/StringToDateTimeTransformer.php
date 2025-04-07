<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StringToDateTimeTransformer implements DataTransformerInterface
{
    public function transform($value): ?\DateTimeInterface
    {
        if (!$value || !is_string($value)) {
            return null;
        }
    
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        
        return $date instanceof \DateTimeInterface ? $date : null;
    }
    
    public function reverseTransform($value)
    {
        // Transform the DateTime from the form to string for the database
        if (null === $value) {
            return '';
        }

        if (!$value instanceof \DateTimeInterface) {
            throw new TransformationFailedException('Expected a DateTimeInterface.');
        }

        return $value->format('Y-m-d');
    }
}

<?php

namespace App\Service;

use App\Entity\Ressource;

class RessourceSimilarityService
{
    public function calculateSimilarity(Ressource $r1, Ressource $r2): int
    {
        $similarity = 0;
        if ($r1->getType() === $r2->getType()) {
            $similarity += 5;
        }
        if (abs($r1->getPrix() - $r2->getPrix()) < 500) {
            $similarity += 3;
        }
        if ($r1->getEtat() === $r2->getEtat()) {
            $similarity += 2;
        }

        return $similarity;
    }
}

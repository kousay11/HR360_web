<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_icon_for_type', [$this, 'getIconForType']),
        ];
    }

    public function getIconForType(string $type): string
    {
        $type = mb_strtolower($type);
        return match($type) {
            'salle'     => 'building',
            'véhicule'  => 'truck',
            'matériel'  => 'tools',
            'ordinateur' => 'pc-display',
            default     => 'box'
        };
    }
}

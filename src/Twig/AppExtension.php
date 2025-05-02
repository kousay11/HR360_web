<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private string $projectDir;

    // Le constructeur doit exactement correspondre à ceci
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileExists']),
        ];
    }

    public function fileExists(string $path): bool
    {
        $fullPath = $this->projectDir . '/public/' . ltrim($path, '/');
        return file_exists($fullPath) && is_file($fullPath);
    }
}
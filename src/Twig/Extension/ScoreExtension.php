<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ScoreExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ScoreExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canBook', [ScoreExtensionRuntime::class, 'canBook']),
        ];
    }
}

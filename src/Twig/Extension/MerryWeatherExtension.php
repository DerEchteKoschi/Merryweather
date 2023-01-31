<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\MerryWeatherExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MerryWeatherExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('canBook', [MerryWeatherExtensionRuntime::class, 'canBook']),
            new TwigFunction('canCancel', [MerryWeatherExtensionRuntime::class, 'canCancel']),
            new TwigFunction('bootstrapClassForLog', [MerryWeatherExtensionRuntime::class, 'bootstrapClassForLog']),
        ];
    }
}

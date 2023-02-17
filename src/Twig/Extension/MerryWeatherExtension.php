<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\MerryweatherExtensionRuntime;
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
            new TwigFunction('canBook', [MerryweatherExtensionRuntime::class, 'canBook']),
            new TwigFunction('canCancel', [MerryweatherExtensionRuntime::class, 'canCancel']),
            new TwigFunction('slotCost', [MerryweatherExtensionRuntime::class, 'slotCost']),
            new TwigFunction('userScore', [MerryweatherExtensionRuntime::class, 'userScore']),
            new TwigFunction('bootstrapClassForLog', [MerryweatherExtensionRuntime::class, 'bootstrapClassForLog']),
        ];
    }
}

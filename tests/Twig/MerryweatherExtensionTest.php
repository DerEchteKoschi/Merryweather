<?php

namespace tests\Twig;

use App\Twig\Extension\MerryWeatherExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class MerryweatherExtensionTest extends TestCase

{
    public function testGetFilters()
    {
        $met = new MerryWeatherExtension();
        $this->assertCount(0, $met->getFilters());
    }

    public function testGetFunctions()
    {
        $met = new MerryWeatherExtension();
        $functions = $met->getFunctions();
        $this->assertCount(4,$functions);
        $names = [];
        foreach ($functions as $function){
            $this->assertInstanceOf(TwigFunction::class, $function);
            $names[] = $function->getName();
        }
        sort($names);
        $this->assertEquals(['bootstrapClassForLog','canBook','canCancel','slotCost'], $names);
    }

    public function testGetNodeVisitors()
    {
        $met = new MerryWeatherExtension();
        $this->assertCount(0, $met->getNodeVisitors());
    }

    public function testGetOperators()
    {
        $met = new MerryWeatherExtension();
        $this->assertCount(0, $met->getOperators());
    }

    public function testGetTests()
    {
        $met = new MerryWeatherExtension();
        $this->assertCount(0, $met->getTests());
    }

    public function testGetTokenParsers()
    {
        $met = new MerryWeatherExtension();
        $this->assertCount(0, $met->getTokenParsers());
    }
}

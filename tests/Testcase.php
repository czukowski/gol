<?php
namespace Cz\GoL;

use Exception,
    PHPUnit_Framework_TestCase,
    ReflectionClass,
    ReflectionMethod,
    ReflectionProperty;

/**
 * Testcase
 * 
 * @author  czukowski
 */
abstract class Testcase extends PHPUnit_Framework_TestCase
{
    /**
     * @var  array
     */
    protected static $smallWorld = [
        [2, 2, 0, 0, 1],
        [2, 0, 1, 1, 0],
        [0, 1, 1, 1, 0],
        [0, 1, 1, 0, 3],
        [1, 0, 0, 3, 3],
    ];

    /**
     * @param  array  $expected
     * @param  array  $actual
     */
    protected function assertOrganismsList($expected, $actual)
    {
        $this->assertSame(count($expected), count($actual), 'Organisms count');
        for ($i = 0; $i < count($expected); $i++) {
            list ($x, $y, $type) = $expected[$i];
            $num = $i + 1;
            $this->assertInstanceOf(Organism::class, $actual[$i]);
            $this->assertSame($x, $actual[$i]->x, "Organism #$num x position");
            $this->assertSame($y, $actual[$i]->y, "Organism #$num y position");
            $this->assertSame($type, $actual[$i]->type, "Organism #$num species type");
        }
    }

    /**
     * @param   array  $cells
     * @return  WorldSpace
     */
    protected function createReadOnlyWorld($cells)
    {
        $world = $this->createMock(WorldSpace::class);
        $world->expects($this->any())
            ->method('getAt')
            ->will($this->returnCallback(function ($x, $y) use ($cells) {
                return $cells[$y][$x];
            }));
        $world->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue($cells ? count($cells[0]) : 0));
        $world->expects($this->any())
            ->method('getHeight')
            ->will($this->returnValue(count($cells)));
        return $world;
    }

    /**
     * @param   string  $filename
     * @return  string
     */
    protected function getFixturePath($filename)
    {
        return $this->getFixturesDir().$filename;
    }

    /**
     * @return  string
     */
    protected function getFixturesDir()
    {
        $class = new ReflectionClass($this);
        $path = $class->getFileName();
        return dirname($path).'/'.basename($path, '.php').'/';
    }

    /**
     * @param   object  $object
     * @param   string  $name
     * @return  ReflectionMethod
     */
    protected function getObjectMethod($object, $name)
    {
        $method = new ReflectionMethod($object, $name);
        $method->setAccessible(TRUE);
        return $method;
    }

    /**
     * @param   object  $object
     * @param   string  $name
     * @return  ReflectionProperty
     */
    protected function getObjectProperty($object, $name)
    {
        $property = new ReflectionProperty($object, $name);
        $property->setAccessible(TRUE);
        return $property;
    }

    /**
     * @param  Exception  $expected
     */
    protected function expectExceptionFromArgument($expected)
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
        }
    }
}

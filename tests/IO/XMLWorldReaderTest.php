<?php
namespace Cz\GoL\IO;
use Cz\GoL\Organism,
    Cz\GoL\Testcase;

/**
 * Description of XMLWorldReaderTest
 *
 * @author czukowski
 */
class XMLWorldReaderTest extends Testcase
{
    /**
     * @dataProvider  provideGetNumberOfIterations
     */
    public function testGetNumberOfIterations($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $object->getNumberOfIterations();
        $this->assertSame($expected, $actual);
    }

    public function provideGetNumberOfIterations()
    {
        return [
            ['GoodWorld.xml', 4000000],
            ['BadWorld.xml', 0],
        ];
    }

    /**
     * @dataProvider  provideGetNumberOfSpecies
     */
    public function testGetNumberOfSpecies($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $object->getNumberOfSpecies();
        $this->assertSame($expected, $actual);
    }

    public function provideGetNumberOfSpecies()
    {
        return [
            ['GoodWorld.xml', 5],
            ['BadWorld.xml', 0],
        ];
    }

    /**
     * @dataProvider  provideGetOrganismsList
     */
    public function testGetOrganismsList($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $object->getOrganismsList();
        $this->assertSame(count($expected), count($actual), 'Organisms count');
        for ($i = 0; $i < count($expected); $i++) {
            list ($x, $y, $type) = $expected[$i];
            $this->assertInstanceOf(Organism::class, $actual[$i]);
            $this->assertSame($x, $actual[$i]->x, "Organism #$i x position");
            $this->assertSame($y, $actual[$i]->y, "Organism #$i y position");
            $this->assertSame($type, $actual[$i]->type, "Organism #$i species type");
        }
    }

    public function provideGetOrganismsList()
    {
        return [
            [
                'GoodWorld.xml',
                [
                    [0, 0, 1], [0, 1, 1], [1, 0, 1], [10, 0, 2], [10, 1, 3],
                ],
            ],
            ['BadWorld.xml', []],
        ];
    }

    /**
     * @dataProvider  provideGetWorldDimension
     */
    public function testGetWorldDimension($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $object->getWorldDimension();
        $this->assertSame($expected, $actual);
    }

    public function provideGetWorldDimension()
    {
        return [
            ['GoodWorld.xml', 100],
            ['BadWorld.xml', 3],
        ];
    }

    /**
     * @param   string  $file
     * @return  XMLWorldReader
     */
    private function createObject($file)
    {
        $xmlPath = $this->getFixturePath($file);
        return new XMLWorldReader($xmlPath);
    }
}

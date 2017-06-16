<?php
namespace Cz\GoL\IO;

use Cz\GoL\Testcase;

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
        $this->assertOrganismsList($expected, $actual);
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
     * @dataProvider  provideGetWorldDimensions
     */
    public function testGetWorldDimensions($file, $expected)
    {
        $object = $this->createObject($file);
        $actualWidth = $object->getWorldWidth();
        $actualHeight = $object->getWorldHeight();
        $this->assertSame($expected, $actualWidth);
        $this->assertSame($expected, $actualHeight);
    }

    public function provideGetWorldDimensions()
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

<?php
namespace Cz\GoL\IO;
use Cz\GoL\Testcase;

/**
 * RLEWorldReaderTest
 * 
 * @author  czukowski
 */
class RLEWorldReaderTest extends Testcase
{
    /**
     * @dataProvider  provideParseData
     */
    public function testParseData($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $this->getObjectProperty($object, 'organisms')
            ->getValue($object);
        $this->assertOrganismsList($expected, $actual);
    }

    public function provideParseData()
    {
        return [
            [
                'Glider.dat',
                [
                    [1, 0, 1], [2, 1, 1], [0, 2, 1], [1, 2, 1], [2, 2, 1],
                ],
            ],
            [
                'GosperGliderGun.dat',
                [
                    [24, 0, 1],
                    [22, 1, 1], [24, 1, 1],
                    [12, 2, 1], [13, 2, 1], [20, 2, 1], [21, 2, 1], [34, 2, 1], [35, 2, 1],
                    [11, 3, 1], [15, 3, 1], [20, 3, 1], [21, 3, 1], [34, 3, 1], [35, 3, 1],
                    [0, 4, 1], [1, 4, 1], [10, 4, 1], [16, 4, 1], [20, 4, 1], [21, 4, 1],
                    [0, 5, 1], [1, 5, 1], [10, 5, 1], [14, 5, 1], [16, 5, 1], [17, 5, 1], [22, 5, 1], [24, 5, 1],
                    [10, 6, 1], [16, 6, 1], [24, 6, 1],
                    [11, 7, 1], [15, 7, 1],
                    [12, 8, 1], [13, 8, 1],
                ],
            ],
        ];
    }

    /**
     * @dataProvider  provideParseParameters
     */
    public function testParseParameters($file, $expected)
    {
        $object = $this->createObject($file);
        $actual = $this->getObjectProperty($object, 'parameters')
            ->getValue($object);
        $this->assertEquals($expected, $actual);
    }

    public function provideParseParameters()
    {
        return [
            [
                'Glider.dat',
                ['x' => '3', 'y' => '3'],
            ],
            [
                'GosperGliderGun.dat',
                ['x' => '36', 'y' => '9', 'rule' => 'B3/S23'],
            ],
        ];
    }

    /**
     * @param   string  $file
     * @return  RLEWorldReader
     */
    private function createObject($file)
    {
        $rlePath = $this->getFixturePath($file);
        return new RLEWorldReader($rlePath);
    }
}

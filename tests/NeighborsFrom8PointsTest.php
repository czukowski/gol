<?php
namespace Cz\GoL;

/**
 * NeighborsFrom8Points
 * 
 * @author  czukowski
 */
class NeighborsFrom8PointsTest extends Testcase
{
    /**
     * @dataProvider  provideGetNeighborsOf
     */
    public function testGetNeighborsOf($cells, $x, $y, $expected)
    {
        $object = new NeighborsFrom8Points;
        $world = $this->createReadOnlyWorld($cells);
        $actual = $object->getNeighborsOf($world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideGetNeighborsOf()
    {
        return [
            [self::$smallWorld, 0, 0, [2, 2, 0]],
            [self::$smallWorld, 1, 1, [2, 1, 2, 1, 2, 0, 0, 1]],
            [self::$smallWorld, 2, 2, [1, 1, 1, 1, 0, 1, 1, 0]],
            [self::$smallWorld, 0, 3, [1, 0, 1, 1, 0]],
            [self::$smallWorld, 4, 4, [3, 3, 0]],
        ];
    }
}

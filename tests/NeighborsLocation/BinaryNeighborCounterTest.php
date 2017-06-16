<?php
namespace Cz\GoL\NeighborsLocation;

use Cz\GoL\Testcase,
    Cz\GoL\WorldSpace;

/**
 * BinaryNeighborCounterTest
 * 
 * @author  czukowski
 */
class BinaryNeighborCounterTest extends Testcase
{
    /**
     * @dataProvider  provideGetNeighborCountsOf
     */
    public function testGetNeighborCountsOf($x, $y, $neighbors, $expected)
    {
        $world = $this->createMock(WorldSpace::class);
        $object = $this->getMockBuilder(BinaryNeighborCounter::class)
            ->setMethods(['getNeighborsOf'])
            ->getMock();
        $object->expects($this->once())
            ->method('getNeighborsOf')
            ->with($world, $x, $y)
            ->will($this->returnValue($neighbors));
        $actual = $object->getNeighborCountsOf($world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideGetNeighborCountsOf()
    {
        return [
            [0, 0, [2, 2, 0], [2 => 2, 0 => 1]],
            [1, 1, [2, 1, 2, 1, 2, 0, 0, 1], [2 => 3, 1 => 3, 0 => 2]],
            [2, 2, [1, 1, 1, 1, 0, 1, 1, 0], [1 => 6, 0 => 2]],
            [0, 3, [1, 0, 1, 1, 0], [1 => 3, 0 => 2]],
            [4, 4, [3, 3, 0], [3 => 2, 0 => 1]],
        ];
    }
}

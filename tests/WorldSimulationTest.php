<?php
namespace Cz\GoL;
use InvalidArgumentException;

/**
 * WorldSimulationTest
 * 
 * @author  czukowski
 */
class WorldSimulationTest extends Testcase
{
    /**
     * @var  array
     */
    private $cells;
    /**
     * @var  array
     */
    private static $smallWorld = [
        [2, 2, 0, 0, 1],
        [2, 0, 1, 1, 0],
        [0, 1, 1, 1, 0],
        [0, 1, 1, 0, 3],
        [1, 0, 0, 3, 3],
    ];

    /**
     * @dataProvider  provideIterateIntegration
     */
    public function testIterateIntegration($cells, $numberOfIterations, $expected)
    {
        $world = $this->createWorldFromCells($cells);
        $object = $this->createObject();
        $object->iterateWorld($world, $numberOfIterations);
        $this->assertSame($expected, $this->cells);
    }

    public function provideIterateIntegration()
    {
        return [
            [
                self::$smallWorld, 1,
                [
                    [2, 0, 0, 0, 0],
                    [0, 0, 1, 1, 0],
                    [0, 1, 0, 1, 0],
                    [0, 1, 1, 0, 0],
                    [0, 0, 0, 0, 3],
                ],
            ],
            [
                self::$smallWorld, 2,
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 1, 0],
                    [0, 0, 0, 0, 0],
                    [0, 1, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                ],
            ],
            [
                self::$smallWorld, 3,
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                ],
            ],
        ];
    }

    /**
     * @dataProvider  provideIterateWorld
     */
    public function testIterateWorld($numberOfIterations)
    {
        $world = $this->createMock(WorldSpace::class);
        $object = $this->getMockBuilder(WorldSimulation::class)
            ->setMethodsExcept(['iterateWorld'])
            ->getMock();
        $object->expects($this->exactly($numberOfIterations))
            ->method('iterateWorldOnce')
            ->with($world);
        $object->iterateWorld($world, $numberOfIterations);
    }

    public function provideIterateWorld()
    {
        return [
            [100],
        ];
    }

    /**
     * @dataProvider  provideIterateWorldInvalidNumber
     */
    public function testIterateWorldInvalidNumber($numberOfIterations)
    {
        $object = $this->createObject();
        $world = $this->createMock(WorldSpace::class);
        $this->expectException(InvalidArgumentException::class);
        $object->iterateWorld($world, $numberOfIterations);
    }

    public function provideIterateWorldInvalidNumber()
    {
        return [
            [0],
            [-1],
            [3.14],
            ['fifty five'],
        ];
    }

    /**
     * @dataProvider  provideIterateWorldOnce
     */
    public function testIterateWorldOnce($dimension, $evolveCalls, $setAtCalls)
    {
        $world = $this->createMock(WorldSpace::class);
        $world->expects($this->any())
            ->method('getDimension')
            ->will($this->returnValue($dimension));
        $object = $this->getMockBuilder(WorldSimulation::class)
            ->setMethods(['evolveWorldAt'])
            ->getMock();
        for ($i = 0; $i < count($evolveCalls); $i++) {
            list ($x, $y, $type) = $evolveCalls[$i];
            $object->expects($this->at($i))
                ->method('evolveWorldAt')
                ->with($world, $x, $y)
                ->will($this->returnValue($type));
        }
        for ($i = 0; $i < count($setAtCalls); $i++) {
            list ($x, $y, $type) = $setAtCalls[$i];
            $world->expects($this->at($i + 1))
                ->method('setAt')
                ->with($x, $y, $type);
        }
        $object->iterateWorldOnce($world);
    }

    public function provideIterateWorldOnce()
    {
        return [
            [
                3,
                [
                    [0, 0, NULL], [1, 0, 0], [2, 0, 1],
                    [0, 1, 0], [1, 1, 2], [2, 1, NULL],
                    [0, 2, NULL], [1, 2, 1], [2, 2, NULL],
                ],
                [
                    [1, 0, 0], [2, 0, 1], [0, 1, 0], [1, 1, 2], [1, 2, 1],
                ],
            ],
        ];
    }

    /**
     * @dataProvider  provideEvolveWorldAt
     */
    public function testEvolveWorldAt($x, $y, $type, $neighborCounts, $expected)
    {
        $world = $this->createMock(WorldSpace::class);
        $world->expects($this->any())
            ->method('getAt')
            ->with($x, $y)
            ->will($this->returnValue($type));
        $object = $this->getMockBuilder(WorldSimulation::class)
            ->setMethods(['getNeighborCountsOf', 'resolveBirthRights'])
            ->getMock();
        $object->expects($this->once())
            ->method('getNeighborCountsOf')
            ->with($world, $x, $y)
            ->will($this->returnValue($neighborCounts));
        $object->expects($type === 0 && $expected !== NULL ? $this->once() : $this->never())
            ->method('resolveBirthRights')
            ->will($this->returnCallback(function (array $elements) {
                return reset($elements);
            }));
        $actual = $this->getObjectMethod($object, 'evolveWorldAt')
            ->invoke($object, $world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideEvolveWorldAt()
    {
        return [
            [0, 0, 1, [0 => 2], 0],             // 1 dies out.
            [0, 1, 0, [0 => 1, 1 => 2], NULL],  // Nothing changes.
            [0, 1, 1, [0 => 1, 1 => 2], NULL],  // 1 survives.
            [0, 1, 2, [0 => 1, 1 => 2], 0],     // 2 dies out.
            [1, 1, 1, [1 => 4], 0],             // 1 dies out.
            [1, 1, 1, [1 => 1], 0],             // 1 dies out.
            [1, 1, 1, [1 => 2], NULL],          // 1 survives.
            [1, 1, 1, [1 => 3], NULL],          // 1 survives.
            [1, 1, 0, [0 => 4], NULL],          // Still emptiness.
            [1, 1, 0, [1 => 3], 1],             // 1 is born.
            [1, 1, 0, [1 => 2, 2 => 2], NULL],  // Not enough for birth.
            [1, 1, 0, [1 => 3, 2 => 3], 1],     // 1 is born (for tests the 1st eligible always used).
            [1, 1, 0, [1 => 4], NULL],          // Too many for birth.
        ];
    }

    /**
     * @dataProvider  provideGetNeighborCountsOf
     */
    public function testGetNeighborCountsOf($cells, $x, $y, $expected)
    {
        $object = $this->createObject();
        $world = $this->createWorldFromCells($cells);
        $actual = $this->getObjectMethod($object, 'getNeighborCountsOf')
            ->invoke($object, $world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideGetNeighborCountsOf()
    {
        return [
            [self::$smallWorld, 0, 0, [2 => 2]],
            [self::$smallWorld, 1, 1, [2 => 2, 1 => 2]],
            [self::$smallWorld, 2, 2, [1 => 4]],
            [self::$smallWorld, 0, 3, [1 => 2, 0 => 1]],
            [self::$smallWorld, 4, 4, [3 => 2]],
        ];
    }

    /**
     * @dataProvider  provideGetNeighborsOf
     */
    public function testGetNeighborsOf($cells, $x, $y, $expected)
    {
        $object = $this->createObject();
        $world = $this->createWorldFromCells($cells);
        $actual = $this->getObjectMethod($object, 'getNeighborsOf')
            ->invoke($object, $world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideGetNeighborsOf()
    {
        return [
            [self::$smallWorld, 0, 0, [2, 2]],
            [self::$smallWorld, 1, 1, [2, 1, 2, 1]],
            [self::$smallWorld, 2, 2, [1, 1, 1, 1]],
            [self::$smallWorld, 0, 3, [1, 0, 1]],
            [self::$smallWorld, 4, 4, [3, 3]],
        ];
    }

    /**
     * @return  WorldSimulation
     */
    private function createObject()
    {
        return new WorldSimulation;
    }

    /**
     * @param   array  $cells
     * @return  WorldSpace
     */
    private function createWorldFromCells($cells)
    {
        $this->cells = $cells;
        $world = $this->createMock(WorldSpace::class);
        $world->expects($this->any())
            ->method('getAt')
            ->will($this->returnCallback(function ($x, $y) {
                return $this->cells[$y][$x];
            }));
        $world->expects($this->any())
            ->method('setAt')
            ->will($this->returnCallback(function ($x, $y, $value) {
                $this->cells[$y][$x] = $value;
            }));
        $world->expects($this->any())
            ->method('getDimension')
            ->will($this->returnValue(count($cells)));
        return $world;
    }

    protected function tearDown()
    {
        $this->cells = NULL;
    }
}

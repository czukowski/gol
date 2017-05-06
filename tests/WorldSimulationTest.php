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
     * @dataProvider  provideIterateIntegration
     */
    public function testIterateIntegration($cells, $numberOfIterations, $expected)
    {
        $world = new WorldSpace;
        $this->getObjectProperty($world, 'cells')
            ->setValue($world, $cells);
        $this->getObjectProperty($world, 'worldDimension')
            ->setValue($world, count($cells));
        $this->getObjectProperty($world, 'numberOfSpecies')
            ->setValue($world, max(array_map(function ($row) { return max($row); }, $cells)));
        $this->getObjectProperty($world, 'initialized')
            ->setValue($world, TRUE);
        $evolutionRules = $this->createDefaultEvolutionRules($this->any());
        $object = $this->createObject(new NeighborsFrom8Points, $evolutionRules);
        $object->iterateWorld($world, $numberOfIterations);
        $actual = $this->getObjectProperty($world, 'cells')
            ->getValue($world);
        $this->assertEquals($expected, $actual);
    }

    public function provideIterateIntegration()
    {
        return [
            [
                self::$smallWorld, 1,
                [
                    [2, 2, 0, 1, 0],
                    [2, 2, 0, 0, 1],
                    [0, 0, 0, 0, 0],
                    [1, 0, 0, 1, 3],
                    [0, 1, 0, 3, 3],
                ],
            ],
            [
                self::$smallWorld, 2,
                [
                    [2, 2, 0, 0, 0],
                    [2, 2, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 3],
                    [0, 0, 0, 3, 3],
                ],
            ],
            [
                self::$smallWorld, 3,
                [
                    [2, 2, 0, 0, 0],
                    [2, 2, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 3, 3],
                    [0, 0, 0, 3, 3],
                ],
            ],
        ];
    }

    /**
     * @dataProvider  provideIterateWorld
     */
    public function testIterateWorld($numberOfIterations, $withCallback)
    {
        $world = $this->createMock(WorldSpace::class);
        $object = $this->getMockBuilder(WorldSimulation::class)
            ->setMethodsExcept(['iterateWorld'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->exactly($numberOfIterations))
            ->method('iterateWorldOnce')
            ->with($world);
        $callback = NULL;
        if ($withCallback) {
            $onIterationSpy = $this->getMockBuilder('stdClass')
                ->setMethods(['onIteration'])
                ->getMock();
            $onIterationSpy->expects($this->exactly($numberOfIterations))
                ->method('onIteration')
                ->with($world);
            $callback = [$onIterationSpy, 'onIteration'];
        }
        $object->iterateWorld($world, $numberOfIterations, $callback);
    }

    public function provideIterateWorld()
    {
        return [
            [100, FALSE],
            [100, TRUE],
        ];
    }

    /**
     * @dataProvider  provideIterateWorldInvalidNumber
     */
    public function testIterateWorldInvalidNumber($numberOfIterations)
    {
        $object = $this->createObject($this->createMock(NeighborsInterface::class));
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
     * @dataProvider  provideIterateWorldInvalidCallback
     */
    public function testIterateWorldInvalidCallback($onIteration)
    {
        $object = $this->createObject($this->createMock(NeighborsInterface::class));
        $world = $this->createMock(WorldSpace::class);
        $this->expectException(InvalidArgumentException::class);
        $object->iterateWorld($world, 100, $onIteration);
    }

    public function provideIterateWorldInvalidCallback()
    {
        return [
            [FALSE],
            [-1],
            [3.14],
            ['lets_hope_this_function_does_not_exist'],
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
            ->disableOriginalConstructor()
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
     * @dataProvider  provideEvolveWorldAtIntegration
     */
    public function testEvolveWorldAtIntegration($x, $y, $type, $neighborCounts, $expected)
    {
        $world = $this->createMock(WorldSpace::class);
        $world->expects($this->any())
            ->method('getAt')
            ->with($x, $y)
            ->will($this->returnValue($type));
        $neighborsLocator = $this->createMock(NeighborsInterface::class);
        $neighborsLocator->expects($this->once())
            ->method('getNeighborCountsOf')
            ->with($world, $x, $y)
            ->will($this->returnValue($neighborCounts));
        $evolutionRules = $this->createDefaultEvolutionRules(
            $type === 0 && $expected !== NULL ? $this->once() : $this->never()
        );
        $object = $this->createObject($neighborsLocator, $evolutionRules);
        $actual = $this->getObjectMethod($object, 'evolveWorldAt')
            ->invoke($object, $world, $x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideEvolveWorldAtIntegration()
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
     * @return  array
     */
    private function createDefaultEvolutionRules($giveBirthExpectation)
    {
        // One rule is mocked to avoid probability function in tests.
        // The respective tests will set the correct return values.
        $giveBirthRule = $this->getMockBuilder(GiveBirthRule::class)
            ->setMethods(['resolveBirthRights'])
            ->getMock();
        $giveBirthRule->expects($giveBirthExpectation)
            ->method('resolveBirthRights')
            ->will($this->returnCallback(function (array $elements) {
                return reset($elements);
            }));
        return [
            new DieFromStarvationRule,
            new DieFromOvercrowdingRule,
            new SurviveRule,
            $giveBirthRule,
        ];
    }

    /**
     * @param   NeighborsInterface  $neighborsLocator
     * @param   array               $evolutionRules
     * @return  WorldSimulation
     */
    private function createObject(NeighborsInterface $neighborsLocator, array $evolutionRules = [])
    {
        return new WorldSimulation($neighborsLocator, $evolutionRules);
    }
}

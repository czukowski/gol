<?php
namespace Cz\GoL;
use Exception,
    InvalidArgumentException,
    LogicException;

/**
 * WorldSimulationTest
 * 
 * @author  czukowski
 */
class WorldSimulationTest extends Testcase
{
    /**
     * @dataProvider  provideInitialize
     */
    public function testInitialize($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies, $expectedError)
    {
        $object = $this->createObject();
        $this->expectExceptionFromArgument($expectedError);
        $object->initialize($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $actualCells = $this->getObjectProperty($object, 'cells')
            ->getValue($object);
        $actualNumberOfIterations = $this->getObjectProperty($object, 'numberOfIterations')
            ->getValue($object);
        $actualNumberOfSpecies = $this->getObjectProperty($object, 'numberOfSpecies')
            ->getValue($object);
        $actualInitialized = $this->getObjectProperty($object, 'initialized')
            ->getValue($object);
        $this->assertSame(count($actualCells), $worldDimension, "World dimensions");
        $this->assertSame($numberOfIterations, $actualNumberOfIterations, "Number of iterations");
        $this->assertSame($numberOfSpecies, $actualNumberOfSpecies, "Number of species types");
        $this->assertTrue($actualInitialized, "Initialized state");
        $getExpectedOrganismType = function ($x, $y) use ($organisms) {
            foreach ($organisms as $organism) {
                if ($organism->x === $x && $organism->y === $y) {
                    return $organism->type;
                }
            }
            return 0;
        };
        foreach ($actualCells as $y => $rowOfCells) {
            foreach ($rowOfCells as $x => $actual) {
                $expected = $getExpectedOrganismType($x, $y);
                $this->assertSame($expected, $actual, "Cell ($x, $y) value");
            }
        }
    }

    public function provideInitialize()
    {
        return [
            'NoIssue' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 1),
                    new Organism(1, 0, 1),
                    new Organism(10, 0, 2),
                    new Organism(10, 1, 3),
                ],
                100, 5000000, 3,
                NULL,
            ],
            'ZeroWorldDimension' => [
                [], 0, 5000000, 3, new InvalidArgumentException,
            ],
            'IterationsNotInteger' => [
                [], 100, '5000000.0', 3, new InvalidArgumentException,
            ],
            'NumberOfSpeciesNotInteger' => [
                [], 100, 5000000, 3.14, new InvalidArgumentException,
            ],
            'TooLowXPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(-1, 0, 1),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'TooLowYPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(10, -1, 1),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'TooLowSpeciesNumber' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(1, 1, 0),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'TooHighXPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(100, 0, 1),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'TooHighYPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 100, 1),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'TooHighSpeciesNumber' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 4),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
            'PositionAlreadyOccupied' => [
                [
                    new Organism(10, 10, 1),
                    new Organism(10, 10, 1),
                ],
                100, 5000000, 3,
                new InvalidArgumentException,
            ],
        ];
    }

    /**
     * @dataProvider  provideLoad
     */
    public function testLoad($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $object = $this->getMockBuilder(WorldSimulation::class)
            ->setMethodsExcept(['load'])
            ->getMock();
        $object->expects($this->once())
            ->method('initialize')
            ->with($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $reader = $this->createWorldReader($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $object->load($reader);
    }

    public function provideLoad()
    {
        return [
            [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 1),
                    new Organism(1, 0, 1),
                    new Organism(10, 0, 2),
                    new Organism(10, 1, 3),
                ],
                100, 5000000, 3,
            ],
        ];
    }

    /**
     * @dataProvider  provideSave
     */
    public function testSave($initialized, $numberOfIterations, $numberOfSpecies, $cells, $expected)
    {
        $callbackOrganisms = function ($organisms) use ($expected) {
            if (count($organisms) !== count($expected)) {
                return FALSE;
            }
            for ($i = 0; $i < count($organisms); $i++) {
                if ( ! $organisms[$i] instanceof Organism
                    || $organisms[$i]->x !== $expected[$i][0]
                    || $organisms[$i]->y !== $expected[$i][1]
                    || $organisms[$i]->type !== $expected[$i][2]
                ) {
                    return FALSE;
                }
            }
            return TRUE;
        };
        $worldDimension = count($cells);
        $writer = $this->createMock(WorldWriterInterface::class);
        $writer->expects($expected instanceof Exception ? $this->never() : $this->once())
            ->method('write')
            ->with($this->callback($callbackOrganisms), $worldDimension, $numberOfIterations, $numberOfSpecies);
        $object = $this->createObject();
        $this->getObjectProperty($object, 'initialized')
            ->setValue($object, $initialized);
        $this->getObjectProperty($object, 'cells')
            ->setValue($object, $cells);
        $this->getObjectProperty($object, 'numberOfIterations')
            ->setValue($object, $numberOfIterations);
        $this->getObjectProperty($object, 'numberOfSpecies')
            ->setValue($object, $numberOfSpecies);
        $this->expectExceptionFromArgument($expected);
        $object->save($writer);
    }

    public function provideSave()
    {
        return [
            'InitializedWorld' => [
                TRUE, 100000, 1,
                [
                    [0, 0, 0, 0, 1, 2, 2, 1],
                    [0, 0, 0, 1, 2, 2, 1, 0],
                    [0, 0, 1, 2, 2, 1, 0, 0],
                    [0, 1, 2, 2, 1, 0, 0, 0],
                    [1, 2, 2, 1, 0, 0, 0, 0],
                    [2, 2, 1, 0, 0, 0, 0, 0],
                    [2, 1, 0, 0, 0, 0, 0, 0],
                    [1, 0, 0, 0, 0, 0, 0, 0],
                ],
                [
                    [4, 0, 1], [5, 0, 2], [6, 0, 2], [7, 0, 1],
                    [3, 1, 1], [4, 1, 2], [5, 1, 2], [6, 1, 1],
                    [2, 2, 1], [3, 2, 2], [4, 2, 2], [5, 2, 1],
                    [1, 3, 1], [2, 3, 2], [3, 3, 2], [4, 3, 1],
                    [0, 4, 1], [1, 4, 2], [2, 4, 2], [3, 4, 1],
                    [0, 5, 2], [1, 5, 2], [2, 5, 1],
                    [0, 6, 2], [1, 6, 1],
                    [0, 7, 1],
                ],
            ],
            'UninitializedWorld' => [FALSE, 1000, 1, [], new LogicException],
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
     * @return  WorldReaderInterface
     */
    private function createWorldReader($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $reader = $this->createMock(WorldReaderInterface::class);
        $reader->expects($this->once())
            ->method('getOrganismsList')
            ->will($this->returnValue($organisms));
        $reader->expects($this->once())
            ->method('getWorldDimension')
            ->will($this->returnValue($worldDimension));
        $reader->expects($this->once())
            ->method('getNumberOfIterations')
            ->will($this->returnValue($numberOfIterations));
        $reader->expects($this->once())
            ->method('getNumberOfSpecies')
            ->will($this->returnValue($numberOfSpecies));
        return $reader;
    }
}

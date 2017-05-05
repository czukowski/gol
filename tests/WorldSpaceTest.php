<?php
namespace Cz\GoL;
use Exception,
    InvalidArgumentException,
    LogicException;

/**
 * WorldSpaceTest
 * 
 * @author  czukowski
 */
class WorldSpaceTest extends Testcase
{
    /**
     * @var  array
     */
    private static $smallWorld = [
        [0, 0, 0, 0, 1],
        [0, 0, 0, 1, 0],
        [0, 0, 1, 0, 0],
        [0, 1, 0, 0, 0],
        [1, 0, 0, 0, 0],
    ];

    /**
     * @dataProvider  provideGetAt
     */
    public function testGetAt($cells, $initialized, $x, $y, $expected)
    {
        $object = $this->createObject($cells, NULL, $initialized);
        $this->expectExceptionFromArgument($expected);
        $actual = $object->getAt($x, $y);
        $this->assertSame($expected, $actual);
    }

    public function provideGetAt()
    {
        return [
            '0,0' => [self::$smallWorld, TRUE, 0, 0, 0],
            '0,4' => [self::$smallWorld, TRUE, 0, 4, 1],
            '2,2' => [self::$smallWorld, TRUE, 2, 2, 1],
            'UninitializedWorld' => [self::$smallWorld, FALSE, 0, 0, new LogicException],
            'TooLowXPosition' => [self::$smallWorld, TRUE, -1, 0, new InvalidArgumentException],
            'TooHighXPosition' => [self::$smallWorld, TRUE, 5, 0, new InvalidArgumentException],
            'TooLowYPosition' => [self::$smallWorld, TRUE, 0, -1, new InvalidArgumentException],
            'TooHighYPosition' => [self::$smallWorld, TRUE, 0, 5, new InvalidArgumentException],
        ];
    }

    /**
     * @dataProvider  provideSetAt
     */
    public function testSetAt($cells, $numberOfSpecies, $initialized, $x, $y, $value, $expected)
    {
        $object = $this->createObject($cells, $numberOfSpecies, $initialized);
        $this->expectExceptionFromArgument($expected);
        $object->setAt($x, $y, $value);
        $actualCells = $this->getObjectProperty($object, 'cells')
            ->getValue($object);
        $this->assertSame($expected, $actualCells[$y][$x]);
    }

    public function provideSetAt()
    {
        return [
            '0,0' => [self::$smallWorld, 1, TRUE, 0, 0, 1, 1],
            '0,4' => [self::$smallWorld, 1, TRUE, 0, 4, 1, 1],
            '2,2' => [self::$smallWorld, 1, TRUE, 2, 2, 0, 0],
            'UninitializedWorld' => [self::$smallWorld, 1, FALSE, 0, 0, 1, new LogicException],
            'TooLowXPosition' => [self::$smallWorld, 1, TRUE, -1, 0, 1, new InvalidArgumentException],
            'TooHighXPosition' => [self::$smallWorld, 1, TRUE, 5, 0, 1, new InvalidArgumentException],
            'TooLowYPosition' => [self::$smallWorld, 1, TRUE, 0, -1, 1, new InvalidArgumentException],
            'TooHighYPosition' => [self::$smallWorld, 1, TRUE, 0, 5, 1, new InvalidArgumentException],
            'TooLowSpeciesNumber' => [self::$smallWorld, 1, TRUE, 2, 2, -1, new InvalidArgumentException],
            'TooHighSpeciesNumber' => [self::$smallWorld, 1, TRUE, 2, 2, 2, new InvalidArgumentException],
        ];
    }

    /**
     * @dataProvider  provideDimension
     */
    public function getDimension($worldDimension) {
        $object = $this->createObject();
        $this->getObjectProperty($object, 'worldDimension')
            ->setValue($object, $worldDimension);
        $actual = $object->getDimension();
        $this->assertSame($worldDimension, $actual);
    }

    public function provideDimension() {
        return [
            [100],
        ];
    }

    /**
     * @dataProvider  provideInitialize
     */
    public function testInitialize($organisms, $worldDimension, $numberOfSpecies, $expectedError)
    {
        $object = $this->createObject();
        $this->expectExceptionFromArgument($expectedError);
        $object->initialize($organisms, $worldDimension, $numberOfSpecies);
        $actualCells = $this->getObjectProperty($object, 'cells')
            ->getValue($object);
        $actualWorldDimension = $this->getObjectProperty($object, 'worldDimension')
            ->getValue($object);
        $actualNumberOfSpecies = $this->getObjectProperty($object, 'numberOfSpecies')
            ->getValue($object);
        $actualInitialized = $this->getObjectProperty($object, 'initialized')
            ->getValue($object);
        $this->assertSame($worldDimension, count($actualCells), "World dimensions by cell rows count");
        $this->assertSame($worldDimension, $actualWorldDimension, "World dimensions cached value");
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
                100, 3,
                NULL,
            ],
            'ZeroWorldDimension' => [
                [], 0, 3, new InvalidArgumentException,
            ],
            'NumberOfSpeciesNotInteger' => [
                [], 100, 3.14, new InvalidArgumentException,
            ],
            'TooLowXPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(-1, 0, 1),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'TooLowYPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(10, -1, 1),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'TooLowSpeciesNumber' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(1, 1, 0),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'TooHighXPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(100, 0, 1),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'TooHighYPosition' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 100, 1),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'TooHighSpeciesNumber' => [
                [
                    new Organism(0, 0, 1),
                    new Organism(0, 1, 4),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
            'PositionAlreadyOccupied' => [
                [
                    new Organism(10, 10, 1),
                    new Organism(10, 10, 1),
                ],
                100, 3,
                new InvalidArgumentException,
            ],
        ];
    }

    /**
     * @dataProvider  provideLoad
     */
    public function testLoad($organisms, $worldDimension, $numberOfSpecies)
    {
        $object = $this->getMockBuilder(WorldSpace::class)
            ->setMethodsExcept(['load'])
            ->getMock();
        $object->expects($this->once())
            ->method('initialize')
            ->with($organisms, $worldDimension, $numberOfSpecies);
        $reader = $this->createWorldReader($organisms, $worldDimension, $numberOfSpecies);
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
                100, 3,
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
        $object = $this->createObject($cells, $numberOfSpecies, $initialized);
        $this->expectExceptionFromArgument($expected);
        $object->save($writer, $numberOfIterations);
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
     * @param   array    $cells
     * @param   integer  $numberOfIterations
     * @param   integer  $numberOfSpecies
     * @param   boolean  $initialized
     * @return  WorldSpace
     */
    private function createObject($cells = NULL, $numberOfSpecies = NULL, $initialized = NULL)
    {
        $object = new WorldSpace;
        if ($cells !== NULL) {
            $this->getObjectProperty($object, 'cells')
                ->setValue($object, $cells);
            $this->getObjectProperty($object, 'worldDimension')
                ->setValue($object, count($cells));
        }
        if ($numberOfSpecies !== NULL) {
            $this->getObjectProperty($object, 'numberOfSpecies')
                ->setValue($object, $numberOfSpecies);
        }
        if ($initialized !== NULL) {
            $this->getObjectProperty($object, 'initialized')
                ->setValue($object, $initialized);
        }
        return $object;
    }

    /**
     * @return  WorldReaderInterface
     */
    private function createWorldReader($organisms, $worldDimension, $numberOfSpecies)
    {
        $reader = $this->createMock(WorldReaderInterface::class);
        $reader->expects($this->once())
            ->method('getOrganismsList')
            ->will($this->returnValue($organisms));
        $reader->expects($this->once())
            ->method('getWorldDimension')
            ->will($this->returnValue($worldDimension));
        $reader->expects($this->once())
            ->method('getNumberOfSpecies')
            ->will($this->returnValue($numberOfSpecies));
        return $reader;
    }
}

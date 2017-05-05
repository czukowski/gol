<?php
namespace Cz\GoL;
use InvalidArgumentException,
    LogicException;

/**
 * WorldSimulation
 * 
 * @author  czukowski
 */
class WorldSimulation
{
    /**
     * @var  array
     */
    private $cells;
    /**
     * @var  boolean
     */
    private $initialized = FALSE;
    /**
     * @var  integer
     */
    private $numberOfSpecies;
    /**
     * @var  integer
     */
    private $numberOfIterations;

    /**
     * @param   array    $organisms
     * @param   integer  $worldDimension
     * @param   integer  $numberOfIterations
     * @param   integer  $numberOfSpecies
     * @throws  InvalidArgumentException
     */
    public function initialize(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        foreach ([$worldDimension, $numberOfIterations, $numberOfSpecies] as $value) {
            if ( ! is_int($value) || $value <= 0) {
                throw new InvalidArgumentException("Arguments 2 to 4 must be positive integers");
            }
        }
        $this->numberOfIterations = $numberOfIterations;
        $this->numberOfSpecies = $numberOfSpecies;
        $this->cells = array_fill(0, $worldDimension, array_fill(0, $worldDimension, 0));
        foreach ($organisms as $i => $organism) {
            if ( ! $organism instanceof Organism) {
                throw new InvalidArgumentException("Argument 1 must be array of Organism objects");
            }
            foreach ([$organism->x, $organism->y] as $position) {
                if ($position < 0 || $position >= $worldDimension) {
                    $width = $worldDimension - 1;
                    throw new InvalidArgumentException("Organism #$i has invalid position, allowed range is [0..$width]");
                }
            }
            if ($organism->type <= 0 || $organism->type > $numberOfSpecies) {
                throw new InvalidArgumentException("Organism #$i has invalid species type, allowed range is [1..$numberOfSpecies]");
            }
            if ($this->cells[$organism->y][$organism->x] !== 0) {
                throw new InvalidArgumentException("Organism #$i has a position that is already occupied");
            }
            $this->cells[$organism->y][$organism->x] = $organism->type;
        }
        $this->initialized = TRUE;
    }

    /**
     * @param  WorldReaderInterface  $source
     */
    public function load(WorldReaderInterface $source)
    {
        $this->initialize(
            $source->getOrganismsList(),
            $source->getWorldDimension(),
            $source->getNumberOfIterations(),
            $source->getNumberOfSpecies()
        );
    }

    /**
     * @param   WorldWriterInterface  $destination
     * @throws  LogicException
     */
    public function save(WorldWriterInterface $destination)
    {
        if ( ! $this->initialized) {
            throw new LogicException("Cannot save uninitialized world");
        }
        $worldDimension = count($this->cells);
        $organisms = [];
        for ($y = 0; $y < $worldDimension; $y++) {
            for ($x = 0; $x < $worldDimension; $x++) {
                if ($this->cells[$y][$x] > 0) {
                    $organisms[] = new Organism($x, $y, $this->cells[$y][$x]);
                }
            }
        }
        $destination->write($organisms, $worldDimension, $this->numberOfIterations, $this->numberOfSpecies);
    }
}

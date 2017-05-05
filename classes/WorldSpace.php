<?php
namespace Cz\GoL;
use InvalidArgumentException,
    LogicException;

/**
 * WorldSpace
 * 
 * @author  czukowski
 */
class WorldSpace
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
     * @param   integer  $x
     * @param   integer  $y
     * @return  integer
     */
    public function getAt($x, $y)
    {
        $this->checkInitialized();
        $this->checkPosition($x, $y);
        return $this->cells[$y][$x];
    }

    /**
     * @param   integer  $x
     * @param   integer  $y
     * @param   integer  $type
     */
    public function setAt($x, $y, $type)
    {
        $this->checkInitialized();
        $this->checkPosition($x, $y);
        $this->checkSpeciesType($type);
        return $this->cells[$y][$x] = $type;
    }

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
            $this->checkPosition($organism->x, $organism->y, "Organism #$i has invalid position");
            if ($organism->type === 0) {
                throw new InvalidArgumentException("Organism #$i species type must be positive integer");
            }
            $this->checkSpeciesType($organism->type, "Organism #$i has invalid species type");
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
     * @param  WorldWriterInterface  $destination
     */
    public function save(WorldWriterInterface $destination)
    {
        $this->checkInitialized("Cannot save uninitialized world");
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

    /**
     * @param   string   $message
     * @throws  LogicException
     */
    private function checkInitialized($message = NULL)
    {
        if ( ! $this->initialized) {
            throw new LogicException($message ? : "Cannot access uninitialized world");
        }
    }

    /**
     * @param   integer  $x
     * @param   integer  $y
     * @param   string   $message
     * @throws  InvalidArgumentException
     */
    private function checkPosition($x, $y, $message = NULL)
    {
        $worldDimension = count($this->cells);
        foreach ([$x, $y] as $i => $position) {
            if ($position < 0 || $position >= $worldDimension) {
                $letter = $i === 0 ? 'X' : 'Y';
                $message = $message ? : "Invalid position";
                $width = $worldDimension - 1;
                throw new InvalidArgumentException("$message, allowed $letter range is [0..$width]");
            }
        }
    }

    /**
     * @param   integer  $type
     * @param   string   $message
     * @throws  InvalidArgumentException
     */
    private function checkSpeciesType($type, $message = NULL)
    {
        if ($type < 0 || $type > $this->numberOfSpecies) {
            $message = $message ? : "Invalid species type";
            $value = $this->numberOfSpecies > 1 ? "range is [1..{$this->numberOfSpecies}]" : "value is 1";
            throw new InvalidArgumentException("$message, allowed $value");
        }
    }
}

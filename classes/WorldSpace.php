<?php
namespace Cz\GoL;
use Cz\GoL\IO\WorldReaderInterface,
    Cz\GoL\IO\WorldWriterInterface,
    InvalidArgumentException,
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
    private $worldHeight;
    /**
     * @var  integer
     */
    private $worldWidth;

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
     * @param  integer  $x
     * @param  integer  $y
     * @param  integer  $type
     */
    public function setAt($x, $y, $type)
    {
        $this->checkInitialized();
        $this->checkPosition($x, $y);
        $this->checkSpeciesType($type);
        $this->cells[$y][$x] = $type;
    }

    /**
     * @return  integer
     */
    public function getHeight()
    {
        $this->checkInitialized();
        return $this->worldHeight;
    }

    /**
     * @return  integer
     */
    public function getWidth()
    {
        $this->checkInitialized();
        return $this->worldWidth;
    }

    /**
     * @param   array    $organisms
     * @param   integer  $worldWidth
     * @param   integer  $worldHeight
     * @param   integer  $numberOfSpecies
     * @throws  InvalidArgumentException
     */
    public function initialize(array $organisms, $worldWidth, $worldHeight, $numberOfSpecies)
    {
        foreach ([$worldWidth, $worldHeight, $numberOfSpecies] as $value) {
            if ( ! is_int($value) || $value <= 0) {
                throw new InvalidArgumentException("Arguments 2 to 4 must be positive integers");
            }
        }
        $this->worldWidth = $worldWidth;
        $this->worldHeight = $worldHeight;
        $this->numberOfSpecies = $numberOfSpecies;
        $this->cells = array_fill(0, $worldHeight, array_fill(0, $worldWidth, 0));
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
            $source->getWorldWidth(),
            $source->getWorldHeight(),
            $source->getNumberOfSpecies()
        );
    }

    /**
     * @param  WorldWriterInterface  $destination
     * @param  integer               $numberOfIterations
     */
    public function save(WorldWriterInterface $destination, $numberOfIterations)
    {
        $this->checkInitialized("Cannot save uninitialized world");
        $organisms = [];
        for ($y = 0; $y < $this->worldHeight; $y++) {
            for ($x = 0; $x < $this->worldWidth; $x++) {
                if ($this->cells[$y][$x] > 0) {
                    $organisms[] = new Organism($x, $y, $this->cells[$y][$x]);
                }
            }
        }
        $destination->write(
            $organisms,
            $this->worldWidth,
            $this->worldHeight,
            $numberOfIterations,
            $this->numberOfSpecies
        );
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
        $axes = ['X', 'Y'];
        $size = [$this->worldWidth, $this->worldHeight];
        $point = [$x, $y];
        for ($i = 0; $i < 2; $i++) {
            $position = $point[$i];
            if ($position < 0 || $position >= $size[$i]) {
                $bound = $size[$i] - 1;
                $letter = $axes[$i];
                $message = $message ? : "Invalid position";
                throw new InvalidArgumentException("$message, allowed $letter range is [0..$bound], got $position");
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

<?php
namespace Cz\GoL;

/**
 * Organism
 * 
 * This class is solely for a description of an organism species and its position in a world.
 * Organisms of the same type are indistinctible from each other.
 * 
 * @author  czukowski
 */
class Organism
{
    /**
     * @var  integer
     */
    public $type;
    /**
     * @var  integer
     */
    public $x;
    /**
     * @var  integer
     */
    public $y;

    /**
     * @param  integer  $x
     * @param  integer  $y
     * @param  integer  $type
     */
    public function __construct($x, $y, $type)
    {
        $this->x = $x;
        $this->y = $y;
        $this->type = $type;
    }
}

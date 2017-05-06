<?php
namespace Cz\GoL\NeighborsLocation;
use Cz\GoL\WorldSpace;

/**
 * BinaryNeighborCounter
 * 
 * @author  czukowski
 */
abstract class BinaryNeighborCounter implements NeighborsInterface
{
    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    public function getNeighborCountsOf(WorldSpace $world, $x, $y)
    {
        $counts = [];
        foreach ($this->getNeighborsOf($world, $x, $y) as $neighbor) {
            if ( ! isset($counts[$neighbor])) {
                $counts[$neighbor] = 0;
            }
            $counts[$neighbor]++;
        }
        return $counts;
    }

    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    abstract public function getNeighborsOf(WorldSpace $world, $x, $y);
}

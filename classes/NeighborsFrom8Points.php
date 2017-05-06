<?php
namespace Cz\GoL;

/**
 * NeighborsFrom8Points
 * 
 * @author  czukowski
 */
class NeighborsFrom8Points extends BinaryNeighborCounter
{
    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    public function getNeighborsOf(WorldSpace $world, $x, $y)
    {
        $neighbors = [];
        // 4-point neighbors.
        if ($x > 0) {
            $neighbors[] = $world->getAt($x - 1, $y);
        }
        if ($x < $world->getDimension() - 1) {
            $neighbors[] = $world->getAt($x + 1, $y);
        }
        if ($y > 0) {
            $neighbors[] = $world->getAt($x, $y - 1);
        }
        if ($y < $world->getDimension() - 1) {
            $neighbors[] = $world->getAt($x, $y + 1);
        }
        // Additional diagonal neighbors to a total of 8-point neighbors set.
        if ($x > 0 && $y > 0) {
            $neighbors[] = $world->getAt($x - 1, $y - 1);
        }
        if ($x < $world->getDimension() - 1 && $y > 0) {
            $neighbors[] = $world->getAt($x + 1, $y - 1);
        }
        if ($x > 0 && $y < $world->getDimension() - 1) {
            $neighbors[] = $world->getAt($x - 1, $y + 1);
        }
        if ($x < $world->getDimension() - 1 && $y < $world->getDimension() - 1) {
            $neighbors[] = $world->getAt($x + 1, $y + 1);
        }
        return $neighbors;
    }
}

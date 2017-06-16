<?php
namespace Cz\GoL\NeighborsLocation;
use Cz\GoL\WorldSpace;

/**
 * From8Points
 * 
 * @author  czukowski
 */
class From8Points extends BinaryNeighborCounter
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
        $width = $world->getWidth();
        $height = $world->getHeight();
        // 4-point neighbors.
        if ($x > 0) {
            $neighbors[] = $world->getAt($x - 1, $y);
        }
        if ($x < $width - 1) {
            $neighbors[] = $world->getAt($x + 1, $y);
        }
        if ($y > 0) {
            $neighbors[] = $world->getAt($x, $y - 1);
        }
        if ($y < $height - 1) {
            $neighbors[] = $world->getAt($x, $y + 1);
        }
        // Additional diagonal neighbors to a total of 8-point neighbors set.
        if ($x > 0 && $y > 0) {
            $neighbors[] = $world->getAt($x - 1, $y - 1);
        }
        if ($x < $width - 1 && $y > 0) {
            $neighbors[] = $world->getAt($x + 1, $y - 1);
        }
        if ($x > 0 && $y < $height - 1) {
            $neighbors[] = $world->getAt($x - 1, $y + 1);
        }
        if ($x < $width - 1 && $y < $height - 1) {
            $neighbors[] = $world->getAt($x + 1, $y + 1);
        }
        return $neighbors;
    }
}

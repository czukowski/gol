<?php
namespace Cz\GoL;

/**
 * NeighborsInterface
 * 
 * @author  czukowski
 */
interface NeighborsInterface
{
    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    function getNeighborCountsOf(WorldSpace $world, $x, $y);

    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    function getNeighborsOf(WorldSpace $world, $x, $y);
}

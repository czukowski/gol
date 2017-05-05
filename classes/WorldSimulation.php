<?php
namespace Cz\GoL;
use InvalidArgumentException;

/**
 * WorldSimulation
 * 
 * @author  czukowski
 */
class WorldSimulation
{
    /**
     * @param   WorldSpace  $world
     * @param   integer     $numberOfIterations
     * @throws  InvalidArgumentException
     */
    public function iterateWorld(WorldSpace $world, $numberOfIterations)
    {
        if ( ! is_int($numberOfIterations) || $numberOfIterations <= 0) {
            throw new InvalidArgumentException('Number of iterations must be positive integer');
        }
        do {
            $this->iterateWorldOnce($world);
        }
        while (--$numberOfIterations > 0);
    }

    /**
     * @param  WorldSpace  $world
     */
    public function iterateWorldOnce(WorldSpace $world)
    {
        $worldDimension = $world->getDimension();
        $evolution = [];
        for ($y = 0; $y < $worldDimension; $y++) {
            for ($x = 0; $x < $worldDimension; $x++) {
                $type = $this->evolveWorldAt($world, $x, $y);
                // `NULL` means no change.
                if ($type !== NULL) {
                    $evolution[] = [$x, $y, $type];
                }
            }
        }
        foreach ($evolution as $change) {
            list ($x, $y, $type) = $change;
            $world->setAt($x, $y, $type);
        }
    }

    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    protected function evolveWorldAt(WorldSpace $world, $x, $y)
    {
        $type = $world->getAt($x, $y);
        $neighborCounts = $this->getNeighborCountsOf($world, $x, $y);
        $sameTypeNeighborsCount = isset($neighborCounts[$type]) ? $neighborCounts[$type] : 0;
        if ($type && ($sameTypeNeighborsCount < 2 || $sameTypeNeighborsCount > 3)) {
            // Will die due to either isolation or overcrowding.
            return 0;
        } elseif ($type) {
            // Will survive.
            return NULL;
        }
        // Currently empty cell, see if we can move in some new child.
        $eligibleToGiveBirth = [];
        foreach ($neighborCounts as $type => $count) {
            if ($type && $count === 3) {
                $eligibleToGiveBirth[] = $type;
            }
        }
        if ($eligibleToGiveBirth) {
            return $this->resolveBirthRights($eligibleToGiveBirth);
        }
        // Otherwise no change.
        return NULL;
    }

    /**
     * @param   WorldSpace  $world
     * @param   integer     $x
     * @param   integer     $y
     * @return  array
     */
    protected function getNeighborCountsOf(WorldSpace $world, $x, $y)
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
    protected function getNeighborsOf(WorldSpace $world, $x, $y)
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
        return $neighbors;
    }

    /**
     * @param   array  $allEligible
     * @return  integer
     */
    protected function resolveBirthRights(array $allEligible)
    {
        shuffle($allEligible);
        return reset($allEligible);
    }
}

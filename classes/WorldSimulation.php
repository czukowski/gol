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
     * @var  NeighborsInterface
     */
    private $neighborsLocator;

    /**
     * @param  NeighborsInterface  $neighborsLocator
     */
    public function __construct(NeighborsInterface $neighborsLocator)
    {
        $this->neighborsLocator = $neighborsLocator;
    }

    /**
     * @param   WorldSpace  $world
     * @param   integer     $numberOfIterations
     * @param   callable    $onIteration
     * @throws  InvalidArgumentException
     */
    public function iterateWorld(WorldSpace $world, $numberOfIterations, $onIteration = NULL)
    {
        if ( ! is_int($numberOfIterations) || $numberOfIterations <= 0) {
            throw new InvalidArgumentException('Number of iterations must be positive integer');
        }
        if ($onIteration !== NULL && ! is_callable($onIteration)) {
            throw new InvalidArgumentException('On iteration must be callback or NULL');
        }
        do {
            $this->iterateWorldOnce($world);
            if ($onIteration !== NULL) {
                call_user_func($onIteration, $world, $numberOfIterations - 1);
            }
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
        // Apply all changes in a separate step so that the earlier change in
        // an iteration will not affect another one in the same iteration.
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
        $neighborCounts = $this->neighborsLocator->getNeighborCountsOf($world, $x, $y);
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
     * @param   array  $allEligible
     * @return  integer
     */
    protected function resolveBirthRights(array $allEligible)
    {
        shuffle($allEligible);
        return reset($allEligible);
    }
}

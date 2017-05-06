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
     * @var  array
     */
    private $evolutionRules;
    /**
     * @var  NeighborsInterface
     */
    private $neighborsLocator;

    /**
     * @param  NeighborsInterface  $neighborsLocator
     * @param  array               $evolutionRules
     */
    public function __construct(NeighborsInterface $neighborsLocator, array $evolutionRules)
    {
        $this->evolutionRules = $evolutionRules;
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
        foreach ($this->evolutionRules as $rule) {
            $outcome = $rule->evolve($type, $neighborCounts);
            if ($outcome !== FALSE) {
                return $outcome;
            }
        }
        // Otherwise no change.
        return NULL;
    }
}

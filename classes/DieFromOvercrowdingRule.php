<?php
namespace Cz\GoL;

/**
 * DieFromOvercrowdingRule
 * 
 * @author  czukowski
 */
class DieFromOvercrowdingRule implements EvolutionRuleInterface
{
    /**
     * @var  integer
     */
    private $maxNeighborsBeforeOvercrowded;

    /**
     * @param  integer  $maxNeighborsBeforeOvercrowded
     */
    public function __construct($maxNeighborsBeforeOvercrowded = 3)
    {
        $this->maxNeighborsBeforeOvercrowded = $maxNeighborsBeforeOvercrowded;
    }

    /**
     * @param   integer  $currentOccupant
     * @param   array    $neighborCounts
     * @return  integer|FALSE
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        $sameTypeNeighborsCount = isset($neighborCounts[$currentOccupant])
            ? $neighborCounts[$currentOccupant]
            : 0;
        if ($currentOccupant && $sameTypeNeighborsCount > $this->maxNeighborsBeforeOvercrowded) {
            return 0;
        }
        return FALSE;
    }
}

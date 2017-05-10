<?php
namespace Cz\GoL\EvolutionRules;

/**
 * DieFromOvercrowding
 * 
 * @author  czukowski
 */
class DieFromOvercrowding implements EvolutionRuleInterface
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
            // Die if there is an occupant and is surrounded by too many neighbors of the same type.
            return 0;
        }
        // Skip to next rule.
        return FALSE;
    }
}

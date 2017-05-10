<?php
namespace Cz\GoL\EvolutionRules;

/**
 * DieFromStarvation
 * 
 * @author  ksustov
 */
class DieFromStarvation implements EvolutionRuleInterface
{
    /**
     * @var  integer
     */
    private $minNeighborsToSurvive;

    /**
     * @param  integer  $minNeighborsToSurvive
     */
    public function __construct($minNeighborsToSurvive = 2)
    {
        $this->minNeighborsToSurvive = $minNeighborsToSurvive;
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
        if ($currentOccupant && $sameTypeNeighborsCount < $this->minNeighborsToSurvive) {
            // Die if there is an occupant and is surrounded by too little neighbors of the same type.
            return 0;
        }
        // Skip to next rule.
        return FALSE;
    }
}

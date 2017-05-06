<?php
namespace Cz\GoL;

/**
 * DieFromStarvationRule
 * 
 * @author  ksustov
 */
class DieFromStarvationRule implements EvolutionRuleInterface
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
            return 0;
        }
        return FALSE;
    }
}

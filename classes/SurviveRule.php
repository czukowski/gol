<?php
namespace Cz\GoL;

/**
 * SurviveRule
 *
 * @author  czukowski
 */
class SurviveRule implements EvolutionRuleInterface
{
    /**
     * @param   integer  $currentOccupant
     * @param   array    $neighborCounts
     * @return  NULL|FALSE
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        if ($currentOccupant) {
            return NULL;
        }
        return FALSE;
    }
}

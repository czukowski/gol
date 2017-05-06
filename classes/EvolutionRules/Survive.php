<?php
namespace Cz\GoL\EvolutionRules;

/**
 * Survive
 *
 * @author  czukowski
 */
class Survive implements EvolutionRuleInterface
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

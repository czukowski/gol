<?php
namespace Cz\GoL;

/**
 * EvolutionRuleInterface
 * 
 * @author  czukowski
 */
interface EvolutionRuleInterface
{
    /**
     * 1st argument is a species type of a current occupant (where 0 means no current occupant).
     * 
     * 2nd argument is associative array where key is a species type and value is count of
     * organisms of that species type.
     * 
     * Return value can be integer to indicate a species type that evolves based on neighbors
     * (where 0 means the organism dies), or `NULL` when no change will occur, or `FALSE` when
     * the rule cannot determine the outcome and the next rule in line should be tried.
     * 
     * @param   integer  $currentOccupant
     * @param   array    $neighborCounts
     * @return  integer|NULL|FALSE
     */
    function evolve($currentOccupant, array $neighborCounts);
}

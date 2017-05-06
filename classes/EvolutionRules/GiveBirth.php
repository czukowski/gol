<?php
namespace Cz\GoL\EvolutionRules;

/**
 * GiveBirth
 *
 * @author  czukowski
 */
class GiveBirth implements EvolutionRuleInterface
{
    /**
     * @var  integer
     */
    private $exactNeighborsToGiveBirth;

    /**
     * @param  integer  $exactNeighborsToGiveBirth
     */
    public function __construct($exactNeighborsToGiveBirth = 3)
    {
        $this->exactNeighborsToGiveBirth = $exactNeighborsToGiveBirth;
    }

    /**
     * @param   integer  $currentOccupant
     * @param   array    $neighborCounts
     * @return  integer|FALSE
     */
    public function evolve($currentOccupant, array $neighborCounts)
    {
        // Currently empty cell, see if we can move in some new child.
        $eligibleToGiveBirth = [];
        foreach ($neighborCounts as $type => $count) {
            if ($type && $count === $this->exactNeighborsToGiveBirth) {
                $eligibleToGiveBirth[] = $type;
            }
        }
        if ($eligibleToGiveBirth) {
            return $this->resolveBirthRights($eligibleToGiveBirth);
        }
        return FALSE;
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

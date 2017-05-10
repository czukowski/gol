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
        // Create a list of species types with that can give birth to this cell.
        $eligibleToGiveBirth = [];
        foreach ($neighborCounts as $type => $count) {
            if ($type && $count === $this->exactNeighborsToGiveBirth) {
                $eligibleToGiveBirth[] = $type;
            }
        }
        if ($eligibleToGiveBirth) {
            // If there are any candidates, select one randomly.
            return $this->resolveBirthRights($eligibleToGiveBirth);
        }
        // Skip to next rule.
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

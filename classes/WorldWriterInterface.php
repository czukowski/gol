<?php
namespace Cz\GoL;

/**
 * WorldWriterInterface
 * 
 * @author  czukowski
 */
interface WorldWriterInterface
{
    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    function write(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
}

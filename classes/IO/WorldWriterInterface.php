<?php
namespace Cz\GoL\IO;

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

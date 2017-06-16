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
     * @param  integer  $worldWidth
     * @param  integer  $worldHeight
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    function write(array $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies);
}

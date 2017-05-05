<?php
namespace Cz\GoL;

/**
 * WorldReaderInterface
 * 
 * @author  czukowski
 */
interface WorldReaderInterface
{
    /**
     * Reads number of iterations to be calculated.
     * 
     * @return  integer
     */
    function getNumberOfIterations();

    /**
     * Reads number of distinct species.
     * 
     * @return  integer
     */
    function getNumberOfSpecies();

    /**
     * Reads organisms in the world. Returns array of `Organism` objects.
     * 
     * @return  array
     */
    function getOrganismsList();

    /**
     * Reads dimension of a square "world".
     * 
     * @return  integer
     */
    function getWorldDimension();
}

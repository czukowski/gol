<?php
namespace Cz\GoL;
use SimpleXMLElement;

/**
 * XMLWorldReader
 *
 * @author  czukowski
 */
class XMLWorldReader implements WorldReaderInterface
{
    /**
     * @var  SimpleXMLElement
     */
    private $xml;

    /**
     * @param  string  $xmlFile  Path to XML file containing a world definition.
     */
    public function __construct($xmlFile)
    {
        $this->xml = simplexml_load_file($xmlFile);
    }

    /**
     * @return  integer
     */
    public function getNumberOfIterations()
    {
        return intval($this->xml->world->iterations);
    }

    /**
     * @return  integer
     */
    public function getNumberOfSpecies()
    {
        return intval($this->xml->world->species);
    }

    /**
     * @return  array
     */
    public function getOrganismsList()
    {
        $organisms = (array) $this->xml->organisms;
        if ($organisms) {
            return array_map(
                function (SimpleXMLElement $element) {
                    return new Organism(
                        intval($element->x_pos),
                        intval($element->y_pos),
                        intval($element->species)
                    );
                },
                $organisms['organism']
            );
        }
        return [];
    }

    /**
     * @return  integer
     */
    public function getWorldDimension()
    {
        return intval($this->xml->world->cells);
    }
}

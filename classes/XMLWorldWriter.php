<?php
namespace Cz\GoL;
use SimpleXMLElement;

/**
 * XMLWorldWriter
 * 
 * @author  czukowski
 */
class XMLWorldWriter implements WorldWriterInterface
{
    /**
     * @var  string
     */
    private $path;

    /**
     * @param  string  $path  Path to where XML file is to be saved.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    public function write(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $this->createXmlDocument($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
            ->asXML($this->path);
    }

    /**
     * @param   array    $organismsList
     * @param   integer  $worldDimension
     * @param   integer  $numberOfIterations
     * @param   integer  $numberOfSpecies
     * @return  SimpleXMLElement
     */
    private function createXmlDocument(array $organismsList, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $xml = simplexml_load_string('<life/>');
        $world = $xml->addChild('world');
        $world->addChild('cells', $worldDimension);
        $world->addChild('species', $numberOfSpecies);
        $world->addChild('iterations', $numberOfIterations);
        $organisms = $xml->addChild('organisms');
        foreach ($organismsList as $organismItem) {
            $organism = $organisms->addChild('organism');
            $organism->addChild('x_pos', $organismItem->x);
            $organism->addChild('y_pos', $organismItem->y);
            $organism->addChild('species', $organismItem->type);
        }
        return $xml;
    }
}

<?php
namespace Cz\GoL\IO;

use InvalidArgumentException,
    SimpleXMLElement;

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
     * @param   array    $organisms
     * @param   integer  $worldWidth
     * @param   integer  $worldHeight
     * @param   integer  $numberOfIterations
     * @param   integer  $numberOfSpecies
     * @throws  InvalidArgumentException
     */
    public function write(array $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies)
    {
        if ($worldWidth !== $worldHeight) {
            throw new InvalidArgumentException('XML format supports only square worlds');
        }
        $this->createXmlDocument($organisms, $worldWidth, $numberOfIterations, $numberOfSpecies)
            ->asXML($this->path);
    }

    /**
     * @param   array    $organismsList
     * @param   integer  $worldWidth
     * @param   integer  $worldHeight
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

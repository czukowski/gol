<?php
namespace Cz\GoL\IO;
use Cz\GoL\Organism,
    RuntimeException;

/**
 * RLEWorldReader
 * 
 * Read files in Run Length Encoded format. Not all features are supported.
 * 
 * @see     http://www.conwaylife.com/wiki/RLE
 * @author  czukowski
 */
class RLEWorldReader implements WorldReaderInterface
{
    /**
     * @var  array
     */
    private $organisms = [];
    /**
     * @var  array
     */
    private $parameters;

    /**
     * @param   string  $rleFile
     * @throws  InvalidArgumentException
     */
    public function __construct($rleFile)
    {
        $headerParsed = FALSE;
        $xParsed = $yParsed = 0;
        $fileRows = preg_split("#(\r\n|\n|\r)#", file_get_contents($rleFile));
        foreach ($fileRows as $i => $row) {
            // Whitespaces are insignificant.
            $row = preg_replace("#\s#", '', $row);
            if ($row[0] === '#') {
                // Line comment, can be skipped.
                continue;
            } elseif ( ! $headerParsed) {
                // First line that is not a comment is a header.
                $this->parseHeaderRow($row);
                $headerParsed = TRUE;
            } else {
                // The rest of the lines are data.
                $endOfDataReached = $this->parseDataRow($row, $i + 1, $xParsed, $yParsed);
                if ($endOfDataReached) {
                    break;
                }
            }
        }
    }

    /**
     * @param   string   $data
     * @param   integer  $rowNumber
     * @param   integer  $y
     * @param   integer  $y
     * @return  boolean
     * @throws  InvalidArgumentException
     */
    private function parseDataRow($data, $rowNumber, & $x, & $y)
    {
        $runCountString = $tagString = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $char = strtolower($data[$i]);
            $code = ord($char);
            if ($char === '$') {
                // End of line end character.
                $x = 0;
                $y++;
                $runCountString = '';
            } elseif (in_array($char, ['b', 'o'])) {
                // Tag character.
                $xd = ($runCountString !== '' ? intval($runCountString) : 1);
                if ($char === 'o') {
                    // Alive cell character.
                    $this->addOrganisms($y, $x, $xd);
                }
                $x += $xd;
                $runCountString = '';
            } elseif ($code >= 48 && $code <= 57) {
                // Numeric character.
                $runCountString .= $char;
            } elseif ($char === '!') {
                // End of data character.
                return TRUE;
            } else {
                // Unknown character.
                throw new InvalidArgumentException('Unexpected character '.$char.' at row '.$rowNumber.' col '.($i + 1));
            }
        }
        return FALSE;
    }

    /**
     * @param  integer  $atY
     * @param  integer  $fromX
     * @param  integer  $xOffset
     */
    private function addOrganisms($atY, $fromX, $xOffset)
    {
        for ($x = $fromX; $x < $fromX + $xOffset; $x++) {
            $this->organisms[] = new Organism($x, $atY, 1);
        }
    }

    /**
     * @param   string  $row
     * @throws  RuntimeException
     */
    private function parseHeaderRow($row)
    {
        $params = [];
        foreach (explode(',', $row) as $tuple) {
            list ($key, $value) = explode('=', $tuple);
            $params[$key] = $value;
        }
        $this->parameters = $params;
    }

    /**
     * @return  integer
     */
    public function getNumberOfIterations()
    {
        // This format doesn't support iterations number, but the patters are often
        // to show some interesting behavior that is visible within relatively short
        // count of itrations. We'll use hardcoded value for now.
        return 100;
    }

    /**
     * @return  integer
     */
    public function getNumberOfSpecies()
    {
        return 1;
    }

    /**
     * @return  integer
     */
    public function getOrganismsList()
    {
        return $this->organisms;
    }

    /**
     * @return  integer
     */
    public function getWorldWidth()
    {
        return $this->getWorldDimension('x');
    }

    /**
     * @return  integer
     */
    public function getWorldHeight()
    {
        return $this->getWorldDimension('y');
    }

    /**
     * @param   string  $dim
     * @return  integer
     */
    private function getWorldDimension($dim)
    {
        if ( ! isset($this->parameters[$dim])) {
            throw new RuntimeException("World '{$dim}' dimension not found in input data");
        }
        return intval($this->parameters[$dim]);
    }
}

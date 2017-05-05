<?php
namespace Cz\GoL;

/**
 * EchoWorldWriter
 * 
 * @author  czukowski
 */
class EchoWorldWriter implements WorldWriterInterface
{
    /**
     * @var  array
     */
    private $charCodes = [176, 177, 178, 219, 244, 245, 184, 185, 206, 225, 64, 35, 36, 37, 38];
    /**
     * @var  integer
     */
    private $charCount;
    /**
     * @var  integer  Microseconds to wait after each print.
     */
    private $sleep;

    /**
     * @param  integer  $sleep
     */
    public function __construct($sleep = 500000)
    {
        $this->charCount = count($this->charCodes);
        $this->sleep = $sleep;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    public function write(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $message = $this->composeMessage($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $this->printMessage($message);
        usleep($this->sleep);
    }

    /**
     * @param   array    $organisms
     * @param   integer  $worldDimension
     * @param   integer  $numberOfIterations
     * @param   integer  $numberOfSpecies
     * @return  string
     */
    private function composeMessage(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $cells = array_fill(0, $worldDimension, array_fill(0, $worldDimension, 0));
        foreach ($organisms as $organism) {
            $cells[$organism->y][$organism->x] = $organism->type;
        }
        $lines = [];
        foreach ($cells as $row) {
            $asciid = array_map(
                function ($type) {
                    return chr($this->charCodes[$type % $this->charCount]);
                },
                $row
            );
            $lines[] = implode('', $asciid);
        }
        array_unshift($lines, "Iteration #$numberOfIterations");
        if ($numberOfSpecies > $this->charCount) {
            array_unshift($lines, "Warning: not enough ASCII chars configured to cover all $numberOfSpecies species types!");
        }
        return implode("\n", $lines);
    }

    /**
     * Based on a Stackoverflow answer.
     * 
     * @see  http://stackoverflow.com/a/27850902
     * 
     * @staticvar  integer  $lastLines
     * @param      string   $message
     * @param      integer  $forceClearLines
     */
    private function printMessage($message, $forceClearLines = NULL)
    {
        static $lastLines = 0;

        if ($forceClearLines !== NULL) {
            $lastLines = $forceClearLines;
        }

        $termWidth = exec('tput cols', $toss, $status);
        if ($status) {
            $termWidth = 64;  // Arbitrary fall-back term width.
        }

        $lineCount = 0;
        foreach (explode("\n", $message) as $line) {
            $lineCount += count(str_split($line, $termWidth));
        }

        // Erasure MAGIC: Clear as many lines as the last output had.
        for ($i = 0; $i < $lastLines; $i++) {
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Move cursor Up a line
            echo "\033[1A";
            // Return to the beginning of the line
            echo "\r";
            // Erase to the end of the line
            echo "\033[K";
            // Return to the beginning of the line
            echo "\r";
            // Can be consolodated into
            // echo "\r\033[K\033[1A\r\033[K\r";
        }

        $lastLines = $lineCount;

        echo $message."\n";
    }
}

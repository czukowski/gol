<?php
namespace Cz\GoL\IO;

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
    private $charCodes = [176, 178, 177, 219, 244, 245, 184, 185, 206, 225, 64, 35, 36, 37, 38];
    /**
     * @var  integer
     */
    private $charCount;
    /**
     * @var  integer  
     */
    private $debounce;
    /**
     * @var  callable
     */
    private $printFunction;
    /**
     * @var  integer
     */
    private $totalIterations;

    /**
     * @param  integer|NULL  $debounce         Print no more frequently than once in this amount of microseconds. Disabled if `NULL`.
     * @param  integer|NULL  $totalIterations
     */
    public function __construct($debounce = NULL, $totalIterations = NULL)
    {
        $this->charCount = count($this->charCodes);
        $this->debounce = $debounce / 1000000;
        $this->printFunction = [$this, $debounce !== NULL ? 'debouncePrint' : 'doPrint'];
        $this->totalIterations = $totalIterations;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    public function write(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        call_user_func($this->printFunction, $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    protected function debouncePrint(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        static $time;
        $now = microtime(TRUE);
        $delta = isset($time) ? $now - $time : 0;
        if ($time === NULL || $delta > $this->debounce || $numberOfIterations === 0) {
            $time = $now;
            $this->doPrint($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        }
        return $delta;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldDimension
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    protected function doPrint(array $organisms, $worldDimension, $numberOfIterations, $numberOfSpecies)
    {
        $message = $this->composeMessage($organisms, $worldDimension, $numberOfIterations, $numberOfSpecies);
        $this->printMessage($message);
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
        $currentIteration = $this->totalIterations - $numberOfIterations;
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
        array_unshift($lines, "Iteration #$currentIteration");
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

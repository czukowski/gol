<?php
namespace Cz\GoL\IO;

use League\CLImate\CLImate;

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
    private $chars = ' █░▒▓■□☺☻♠♣♥♦';
    /**
     * @var  CLImate
     */
    private $climate;
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
     * @param  integer|NULL  $fallbackWidth
     */
    public function __construct($debounce = NULL, $totalIterations = NULL, $fallbackWidth = NULL)
    {
        $this->climate = new CLImate;
        $this->charCount = strlen($this->chars);
        $this->debounce = $debounce / 1000000;
        $this->printFunction = [$this, $debounce !== NULL ? 'debouncePrint' : 'doPrint'];
        $this->totalIterations = $totalIterations;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldWidth
     * @param  integer  $worldHeight
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    public function write(array $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies)
    {
        call_user_func($this->printFunction, $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies);
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldWidth
     * @param  integer  $worldHeight
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    protected function debouncePrint(array $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies)
    {
        static $time;
        $now = microtime(TRUE);
        $delta = isset($time) ? $now - $time : 0;
        if ($time === NULL || $delta > $this->debounce || $numberOfIterations === 0) {
            $time = $now;
            $this->doPrint($organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies);
        }
        return $delta;
    }

    /**
     * @param  array    $organisms
     * @param  integer  $worldWidth
     * @param  integer  $worldHeight
     * @param  integer  $numberOfIterations
     * @param  integer  $numberOfSpecies
     */
    protected function doPrint(array $organisms, $worldWidth, $worldHeight, $numberOfIterations, $numberOfSpecies)
    {
        $buffer = $this->climate->output->get('buffer');

        if ($numberOfSpecies > $this->charCount) {
            $buffer->write("Warning: not enough ASCII chars configured to cover all $numberOfSpecies species types!\n");
        }

        $currentIteration = $this->totalIterations - $numberOfIterations;
        $buffer->write("Iteration #$currentIteration\n");

        $cells = array_fill(0, $worldHeight, array_fill(0, $worldWidth, 0));
        foreach ($organisms as $organism) {
            $cells[$organism->y][$organism->x] = $organism->type;
        }
        foreach ($cells as $row) {
            foreach ($row as $type) {
                $buffer->write(
                    str_repeat($this->chars[$type % $this->charCount], 2)
                );
            }
            $buffer->write("\n");
        }

        $this->climate->clear()
            ->out($buffer->get());
        $buffer->clean();
    }
}

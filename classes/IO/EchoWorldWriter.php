<?php
namespace Cz\GoL\IO;

use Bramus\Ansi\Ansi,
    Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR,
    Bramus\Ansi\Writers\BufferWriter;

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
    private $colors = [
        SGR::COLOR_BG_BLACK,
        SGR::COLOR_BG_WHITE,
        SGR::COLOR_BG_RED,
        SGR::COLOR_BG_GREEN,
        SGR::COLOR_BG_YELLOW,
        SGR::COLOR_BG_BLUE,
        SGR::COLOR_BG_PURPLE,
        SGR::COLOR_BG_CYAN,
        SGR::COLOR_BG_WHITE_BRIGHT,
        SGR::COLOR_BG_RED_BRIGHT,
        SGR::COLOR_BG_GREEN_BRIGHT,
        SGR::COLOR_BG_YELLOW_BRIGHT,
        SGR::COLOR_BG_BLUE_BRIGHT,
        SGR::COLOR_BG_PURPLE_BRIGHT,
        SGR::COLOR_BG_CYAN_BRIGHT,
    ];
    /**
     * @var  integer
     */
    private $colorsCount;
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
        $this->ansi = new Ansi(new BufferWriter);
        $this->colorsCount = count($this->colors);
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
        $this->ansi->eraseDisplay();

        $cells = array_fill(0, $worldHeight, array_fill(0, $worldWidth, 0));
        foreach ($organisms as $organism) {
            $cells[$organism->y][$organism->x] = $organism->type;
        }
        foreach ($cells as $row) {
            foreach ($row as $type) {
                $this->ansi->color($this->colors[$type])
                    ->text('  ')
                    ->nostyle();
            }
            $this->ansi->lf();
        }

        $currentIteration = $this->totalIterations - $numberOfIterations;
        $this->ansi->text("Iteration #$currentIteration")->lf();

        if ($numberOfSpecies > $this->colorsCount) {
            $this->ansi->text("Warning: not enough ASCII chars configured to cover all $numberOfSpecies species types!")->lf();
        }

        $this->ansi->e();
    }
}

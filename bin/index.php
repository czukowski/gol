<?php
namespace Cz\GoL;

require __DIR__.'/../vendor/autoload.php';

// Set up in and out files.
$source = new IO\XMLWorldReader(__DIR__.'/samples/max.xml');
$destination = new IO\XMLWorldWriter(__DIR__.'/out.xml');

// Set up debouncing or pausing for a rough balancing of the echo output timing based on number of iterations.
$debounce = 200000;  // In microseconds.
$mininumTime = 5;    // In seconds.
$pause = intval(($mininumTime * 1000000 - $debounce * $source->getNumberOfIterations()) / $source->getNumberOfIterations());
if ($pause > $debounce) {
    $debounce = NULL;
}
if ($pause <= 0) {
    $pause = NULL;
}

// Create output to screen.
$logger = new IO\EchoWorldWriter($debounce, $source->getNumberOfIterations());

// Set up initial world state.
$world = new WorldSpace;
$world->load($source);

// Set up simulation rules.
$evolutionRules = [
    new EvolutionRules\DieFromStarvation,
    new EvolutionRules\DieFromOvercrowding,
    new EvolutionRules\Survive,
    new EvolutionRules\GiveBirth,
];
$simulation = new WorldSimulation(new NeighborsLocation\From8Points, $evolutionRules);

// Run simulation for a number of iterations.
$simulation->iterateWorld(
    $world,
    $source->getNumberOfIterations(),
    function (WorldSpace $world, $iterationsLeft) use ($logger, $pause) {
        $world->save($logger, $iterationsLeft);
        if ($pause) {
            usleep($pause);
        }
    }
);

// Save the outcome to the destination file.
$world->save($destination, 0);

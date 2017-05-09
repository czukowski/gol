<?php
namespace Cz\GoL;

require __DIR__.'/../vendor/autoload.php';

$source = new IO\XMLWorldReader(__DIR__.'/small-world.xml');
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

$logger = new IO\EchoWorldWriter($debounce, $source->getNumberOfIterations());
$world = new WorldSpace;
$world->load($source);

$evolutionRules = [
    new EvolutionRules\DieFromStarvation,
    new EvolutionRules\DieFromOvercrowding,
    new EvolutionRules\Survive,
    new EvolutionRules\GiveBirth,
];
$simulation = new WorldSimulation(new NeighborsLocation\From8Points, $evolutionRules);
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

$world->save($destination, 0);

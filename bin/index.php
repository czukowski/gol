<?php
namespace Cz\GoL;

require __DIR__.'/../vendor/autoload.php';

$source = new IO\XMLWorldReader(__DIR__.'/small-world.xml');
$destination = new IO\XMLWorldWriter(__DIR__.'/out.xml');
$logger = new IO\EchoWorldWriter;
$world = new WorldSpace;
$world->load($source);
$world->save($logger, 0);

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
    function (WorldSpace $world, $iterationsLeft) use ($logger, $source) {
        $world->save($logger, $source->getNumberOfIterations() - $iterationsLeft);
    }
);

$world->save($destination, 0);

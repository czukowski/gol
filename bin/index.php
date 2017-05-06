<?php
namespace Cz\GoL;

require __DIR__.'/../vendor/autoload.php';

$source = new XMLWorldReader(__DIR__.'/small-world.xml');
$destination = new XMLWorldWriter(__DIR__.'/out.xml');
$logger = new EchoWorldWriter;
$world = new WorldSpace;
$world->load($source);
$world->save($logger, 0);

$evolutionRules = [
    new DieFromStarvationRule,
    new DieFromOvercrowdingRule,
    new SurviveRule,
    new GiveBirthRule,
];
$simulation = new WorldSimulation(new NeighborsFrom8Points, $evolutionRules);
$simulation->iterateWorld(
    $world,
    $source->getNumberOfIterations(),
    function (WorldSpace $world, $iterationsLeft) use ($logger, $source) {
        $world->save($logger, $source->getNumberOfIterations() - $iterationsLeft);
    }
);

$world->save($destination, 0);

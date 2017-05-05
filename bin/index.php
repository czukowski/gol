<?php
namespace Cz\GoL;

require __DIR__.'/../vendor/autoload.php';

$source = new XMLWorldReader(__DIR__.'/small-world.xml');
$destination = new XMLWorldWriter(__DIR__.'/out.xml');
$world = new WorldSpace;
$world->load($source);

$simulation = new WorldSimulation;
$simulation->iterateWorld(
    $world,
    $source->getNumberOfIterations(),
    function () { echo '.'; }
);

$world->save($destination, 0);

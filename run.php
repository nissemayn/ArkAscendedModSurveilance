<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use App\ConfigController;
use App\ModSurveilance;

$modSurveilce = ConfigController::get();

echo "Monitoring " . count($modSurveilce['mods']) . " mods.." . PHP_EOL;
ModSurveilance::CheckAllMods();
$loop = Loop::get();
//Set a timer to check for mod updates every x seconds based on config
$loop->addPeriodicTimer($modSurveilce['interval'], function () {
    echo "Checking for mod updates..." . PHP_EOL;
    ModSurveilance::CheckAllMods();
});
$loop->run();

<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use App\ConfigController;
use App\ModSurveilance;

$modSurveilce = ConfigController::get();

cli_set_process_title($modSurveilce['consoleTitle']);

echo "Monitoring " . count($modSurveilce['mods']) . " mods.." . PHP_EOL;
echo "Checking every " . $modSurveilce['interval'] . " seconds" . PHP_EOL;
ModSurveilance::CheckAllMods();
$loop = Loop::get();
$loop->addPeriodicTimer($modSurveilce['interval'], function () {
    ModSurveilance::CheckAllMods();
});
$loop->run();

<?php

namespace App;

use App\ConfigController;
use React\Http\Browser;
use React\Promise\Deferred;
use React\Promise;
use Throwable;

class ModSurveilance
{
    private static string $curseforgeBaseUri = 'https://api.curseforge.com';

    public static function CheckAllMods()
    {
        $modSurveilce = ConfigController::get();

        $modIds = array_keys($modSurveilce['mods']);

        $requestBody = [
            'modIds' => $modIds,
            'filterPcOnly' => false
        ];

        $client = new Browser();

        $response = $client->post(
            self::$curseforgeBaseUri . '/v1/mods',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $modSurveilce['apiKey']
            ],
            json_encode($requestBody)
        )->then(
            function ($response) use ($modSurveilce) {
                $mods = json_decode($response->getBody())->data;

                $deferreds = [];
                foreach ($mods as $mod) {

                    $modId = $mod->id;
                    $latestFileId = $mod->latestFiles[0]->id;

                    $modInfo = $modSurveilce['mods'][$modId];

                    $changed = false;

                    if (!isset($modInfo['latestFileId'])) {
                        $changed = true;
                    } elseif ($modInfo['latestFileId'] != $latestFileId) {
                        $changed = true;
                    }

                    if ($changed) {
                        $deferred = new Deferred();
                        $deferreds[] = $deferred;
                        $client = new Browser();
                        $response = $client->get(
                            self::$curseforgeBaseUri . '/v1/mods/' . $modId . '/files/' . $latestFileId . '/changelog',
                            [
                                'Content-Type' => 'application/json',
                                'x-api-key' => $modSurveilce['apiKey']
                            ]
                        )->then(
                            function ($response) use ($modId, $mod, $modSurveilce, $latestFileId, $deferred) {
                                $changelog = json_decode($response->getBody())->data;
                                $changelog = str_replace('<br>', "\n", $changelog);
                                $changelog = strip_tags($changelog);
                                $changelog = htmlspecialchars_decode($changelog);

                                $data = [
                                    'content' => "",
                                    'embeds' => [
                                        [
                                            'title' => "Changelog",
                                            'description' => $changelog,
                                            'author' => [
                                                'name' => "New update for $mod->name",
                                            ],
                                            'thumbnail' => [
                                                'url' => $mod->logo->url
                                            ],
                                            'footer' => [
                                                'text' => 'Powered by ArkAscendedModSurveilance'
                                            ],
                                            'timestamp' => date('Y-m-d\TH:i:s\Z'),
                                            "color" => 2354023,
                                        ],
                                    ],
                                    "username" => "ArkAscendedModSurveilance",
                                ];

                                $client = new Browser();
                                $client->post($modSurveilce['discordWebhook'], [
                                    'Content-Type' => 'application/json'
                                ], json_encode($data, JSON_UNESCAPED_SLASHES))->then(
                                    function ($response) use ($modId, $latestFileId, $deferred, $modSurveilce) {
                                        $config = ConfigController::get();
                                        $config['mods'][$modId]['latestFileId'] = $latestFileId;
                                        $deferred->resolve($config);
                                    },
                                    function ($e) use ($deferred, $modSurveilce) {
                                        echo $e->getMessage() . PHP_EOL;
                                        echo $e->getResponse()->getBody() . PHP_EOL;
                                        $deferred->resolve($modSurveilce);
                                    }
                                );
                            },
                        );
                    }
                }

                $promises = array_map(function ($deferred) {
                    return $deferred->promise();
                }, $deferreds);

                if (!empty($promises)) {
                    Promise\all($promises)->then(
                        function ($configs) {
                            $mergedConfig = array_reduce($configs, function ($carry, $config) {
                                return array_replace_recursive($carry, $config);
                            }, []);
                            ConfigController::set($mergedConfig);
                        },
                        function ($e) {
                            echo $e->getMessage();
                        }
                    );
                }
            },
            function ($e) {
                echo $e->getMessage();
            }
        );
    }
}

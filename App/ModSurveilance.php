<?php

namespace App;

use App\ConfigController;
use React\Http\Browser;
use React\Promise\Deferred;
use React\Promise;
use Exception;

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
                    echo "Checking mod: $mod->name" . PHP_EOL;

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
                        echo "New update for $mod->name" . PHP_EOL;
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
                                    "username" => $modSurveilce['discordUsername'],
                                    "modId" => $modId,
                                    "latestFileId" => $latestFileId
                                ];

                                $deferred->resolve($data);
                            },
                        );
                    }
                }

                $promises = array_map(function ($deferred) {
                    return $deferred->promise();
                }, $deferreds);

                if (!empty($promises)) {
                    Promise\all($promises)->then(
                        function ($webhookData) use ($modSurveilce) {
                            $client = new \GuzzleHttp\Client([
                                'verify' => false
                            ]);
                            foreach ($webhookData as $data) {
                                $modId = $data['modId'];
                                $latestFileId = $data['latestFileId'];

                                unset($data['modId']);
                                unset($data['latestFileId']);

                                try {
                                    $response = $client->post($modSurveilce['discordWebhook'], [
                                        'headers' => [
                                            'Content-Type' => 'application/json'
                                        ],
                                        'body' => json_encode($data, JSON_UNESCAPED_SLASHES)
                                    ]);
                                } catch (Exception $e) {
                                    echo $e->getMessage() . PHP_EOL;
                                }

                                sleep(1);
                            }

                            $config = ConfigController::get();
                            foreach ($webhookData as $data) {
                                $modId = $data['modId'];
                                $latestFileId = $data['latestFileId'];
                                $config['mods'][$modId] = [
                                    'latestFileId' => $latestFileId
                                ];
                            }
                            ConfigController::set($config);
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

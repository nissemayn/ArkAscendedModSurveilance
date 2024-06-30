# Ark Ascended Mod Surveilance

## Description

Ark Ascended Mod Surveillance is a PHP-based application designed to monitor updates for mods on CurseForge. When a monitored mod receives an update, the application automatically sends a notification to a specified Discord webhook. This allows users to stay informed about the latest updates to their favorite mods without having to manually check CurseForge.

## Prerequisites

- PHP (https://www.php.net/downloads.php)
- Composer (https://getcomposer.org/download/) - Be sure to add the checkbox `Add PHP to path` during installation.

## Installation

Follow these steps to get the project up and running:

1. Clone the repository or download the source code to your server.

    ```bash
    git clone https://github.com/nissemayn/ArkAscendedModSurveilance.git
    ```

2. Navigate to the project directory.

    ```bash
    cd ArkAscendedModSurveilance
    ```

3. Install the necessary dependencies using Composer.

    ```bash
    composer install
    ```

4. Copy the `example.config.json` to `config.json` and make necessary changes according to your setup.

## Configuration

The application is configured using a JSON file named `config.json`. Here's a breakdown of what each field in the configuration file represents:

- `discordWebhook`: This is the URL of the Discord webhook where notifications about mod updates will be sent. You can create a webhook in your Discord server settings.

- `apiKey`: This is your API key for accessing the CurseForge API. You can obtain this key from your CurseForge account settings.

- `interval`: This is the interval (in seconds) at which the application will check for mod updates. For example, an interval of 1800 means the application will check for updates every 30 minutes.

- `discordUsername`: This is the username that will be displayed in Discord when the application sends a notification.

- `consoleTitle`: This is the title that will be displayed in the console when the application is running.

- `mods`: This is an object where each key is the ID of a mod to monitor, and the value is an object that can contain additional configuration for that mod. Adding new mods to monitor is a simple as adding ```"modid":{}``` 

Here's an example configuration:

```json
{
    "discordWebhook": "https://discord.com/api/webhooks/1234567890/abcdefgh",
    "apiKey": "your-api-key",
    "interval": 1800,
    "discordUsername": "ArkAscendedModSurveilance",
    "consoleTitle": "Ark Ascended Mod Surveilance",
    "mods": {
        "930403": {},
        "940786": {}
    }
}
```

## Usage

To start the service, run the following command:

```bash
php run.php
```
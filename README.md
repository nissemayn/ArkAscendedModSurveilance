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


## Usage

To start the service, run the following command:

```bash
php run.php
```
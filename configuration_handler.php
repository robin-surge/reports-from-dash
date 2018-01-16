<?php
DEFINE("CONFIG_FILE", "./config.ini"); //format: [DOMAIN],[RECIPIENT EMAIL]
$config = null;

function loadConfig()
{
    global $config;
    $configFile = file(CONFIG_FILE);
    foreach ($configFile as $line) {
        $line = explode(',', $line);
        if (count($line) === 1) {
            trigger_error("No email address specified for " . $line[0] . "!");
            $config[$line[0]] = false;
            continue;
        } elseif (count($line) === 2) {
            $line[2] = $line[3] = null;
        }
        $config[$line[0]] = array("email" => $line[1], "freq" => $line[2], "ts" => $line[3]);
    }
}

function updateConfigWithNewTS($domain)
{
    global $config;
    $config[$domain]['ts'] = time();
}

function writeUpdatedConfigAndQuit()
{
    global $config;
    $configFile = fopen(CONFIG_FILE, "w");
    foreach ($config as $domain => $settings) {
        $line = $domain;
        $line .= ',' . implode(",", $settings);
        fwrite($configFile, $line);
    }
    fclose($configFile);
    exit();
}
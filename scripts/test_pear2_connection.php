<?php

// Run this script to verify PEAR2/Net_RouterOS is working on your server.
// Reinstall commands (if needed):
// composer remove pear2/net_routeros && composer require pear2/net_routeros

require __DIR__ . '/../vendor/autoload.php';

use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Request;
use PEAR2\Net\RouterOS\Response;

if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/test_pear2_connection.php <router-ip> <username> <password> [port]\n");
    exit(1);
}

$host = $argv[1];
$username = $argv[2];
$password = $argv[3];
$port = isset($argv[4]) ? (int) $argv[4] : 8728;

try {
    $client = new Client($host, $username, $password, $port);
    $request = new Request('/system/resource/print');
    $responses = $client->sendSync($request);

    $data = [];
    foreach ($responses as $response) {
        if ($response->getType() !== Response::TYPE_DATA) {
            continue;
        }

        foreach ($response->getIterator() as $key => $value) {
            $data[$key] = $value;
        }
    }

    echo "Connection successful.\n";
    echo 'board-name: ' . ($data['board-name'] ?? 'N/A') . "\n";
    echo 'version: ' . ($data['version'] ?? 'N/A') . "\n";
    echo 'uptime: ' . ($data['uptime'] ?? 'N/A') . "\n";
    echo 'cpu-load: ' . ($data['cpu-load'] ?? 'N/A') . "\n";
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, "Connection failed: {$e->getMessage()}\n");
    exit(1);
}

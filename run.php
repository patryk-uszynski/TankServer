<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\TestServer;

$testServer = new TestServer();
$http = new HttpServer(new WsServer($testServer));

$server = IoServer::factory($http, 1337);

$ticksPerSecond = !empty($argv[1]) ? $argv[1] : 15;

$server->loop->addPeriodicTimer(1 / $ticksPerSecond, function () use ($testServer) {
	$testServer->tick();
});

$server->run();
<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\TestServer;

$http = new HttpServer(new WsServer(new TestServer));

$server = IoServer::factory($http, 8080);
$server->run();
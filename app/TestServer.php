<?php

namespace App;

use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Guzzle\Http\Message\RequestInterface;

class TestServer implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        $this->clients = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg);

        foreach ($this->clients as $client) {
            $packet = json_encode(array(
              'id' => $from->resourceId,
              'type' => 'update',
              'x' => $data->x,
              'y' => $data->y
            ));
            if ($from !== $client) $client->send($packet);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $packet = json_encode(array(
            'id' => $conn->resourceId,
            'type' => 'disconnect'
        ));

        $this->clients->detach($conn);

        foreach ($this->clients as $client) {
          $client->send($packet);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

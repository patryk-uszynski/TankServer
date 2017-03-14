<?php

namespace App;

use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Guzzle\Http\Message\RequestInterface;
use App\Player;

class TankServer implements MessageComponentInterface {

    protected $players;
    protected $clients;

    public function __construct() {
        $this->players = array();
        $this->clients = new SplObjectStorage;
    }

    public function tick() {
        $packets = array();

        foreach ($this->players as $player) {
            if(!$player->isUpdateNeeded()) continue;

            $packet = json_encode(array(
                'id' => $player->getConnection()->resourceId,
                'type' => 'update',
                'x' => $player->x,
                'y' => $player->y,
                'velocity' => 0,
                'tankRotation' => $player->tankRotation,
                'turretRotation' => $player->turretRotation
            ));

            $packets[$player->getConnection()->resourceId] = $packet;

            $player->setStatus(Player::STATUS_IDLE);
        }

        
        foreach ($this->clients as $client) {
            foreach ($packets as $from => $packet) {
                if ($from !== $client->resourceId) 
                    $client->send($packet);
            }           
        }
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $this->clients->attach($conn);

        $player = new Player(); 
        $player->setConnection($conn);

        $this->players[$conn->resourceId] = ($player);

        echo "New player connected! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);

        $player = &$this->players[$from->resourceId];

        $player->x = $data->x;
        $player->y = $data->y;
        $player->tankRotation = $data->tankRotation;
        $player->turretRotation = $data->turretRotation;

        $player->setStatus(Player::STATUS_UPDATED);
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Player disconnected! ({$conn->resourceId})\n";

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

<?php

namespace App;

use Ratchet\ConnectionInterface;

class Player {

	const STATUS_IDLE = 0;
	const STATUS_UPDATED = 1;
	
	private $connection;

	private $status;

	public $x, $y;

	public $tankRotation;

	public $turretRotation;

	public function setConnection(ConnectionInterface $connection) {
		$this->connection = $connection;
	}

	public function getConnection() : ConnectionInterface {
		return $this->connection;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	public function isUpdateNeeded() : bool {
		return $this->status == Player::STATUS_UPDATED;
	}
}
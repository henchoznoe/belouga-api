<?php

namespace Ctrl;

use Wrk\WrkPlayers;

class PlayersCtrl {

    private WrkPlayers $wrkPlayers;

    public function __construct() {
        $this->wrkPlayers = new WrkPlayers();
    }

    public function create(array $requestBody): void {
        $this->wrkPlayers->create($requestBody);
    }

    public function read(): void {
        $this->wrkPlayers->read();
    }

    public function getPlayer(array $requestParams): void {
        $this->wrkPlayers->getPlayer($requestParams);
    }

    public function update(array $requestBody): void {
        $this->wrkPlayers->update($requestBody);
    }

    public function delete(array $requestParams): void {
        $this->wrkPlayers->delete($requestParams);
    }

}

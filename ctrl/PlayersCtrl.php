<?php

namespace Ctrl;

use Wrk\WrkPlayers;

/**
 * Class PlayersCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class PlayersCtrl {

    private WrkPlayers $wrkPlayers;

    public function __construct() {
        $this->wrkPlayers = new WrkPlayers();
    }

    /**
     * Read all players
     * @return void nothing is returned
     */
    public function read(): void {
        $this->wrkPlayers->read();
    }

    /**
     * Get a specific player
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getPlayer(array $requestParams): void {
        $this->wrkPlayers->getPlayer($requestParams);
    }

    /**
     * Create a new player
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        $this->wrkPlayers->create($requestBody);
    }

    /**
     * Update a player
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        $this->wrkPlayers->update($requestBody);
    }

    /**
     * Delete a player
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        $this->wrkPlayers->delete($requestParams);
    }

}

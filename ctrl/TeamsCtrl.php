<?php

namespace Ctrl;

use Wrk\WrkTeams;

/**
 * Class TeamsCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class TeamsCtrl {

    private WrkTeams $wrkTeams;

    public function __construct() {
        $this->wrkTeams = new WrkTeams();
    }

    /**
     * Read all teams
     * @return void nothing is returned
     */
    public function read(): void {
        $this->wrkTeams->read();
    }

    /**
     * Get a specific team
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getTeam(array $requestParams): void {
        $this->wrkTeams->getTeam($requestParams);
    }

    /**
     * Create a new team
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        $this->wrkTeams->create($requestBody);
    }

    /**
     * Update a team
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        $this->wrkTeams->update($requestBody);
    }

    /**
     * Delete a team
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        $this->wrkTeams->delete($requestParams);
    }

}

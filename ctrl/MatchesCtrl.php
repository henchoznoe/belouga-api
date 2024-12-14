<?php

namespace Ctrl;

use Wrk\WrkMatches;

class MatchesCtrl {

    private WrkMatches $wrkMatches;

    public function __construct() {
        $this->wrkMatches = new WrkMatches();
    }

    /**
     * Read all matches
     * @return void nothing is returned
     */
    public function read(): void {
        $this->wrkMatches->read();
    }

    /**
     * Get a specific match
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getMatch(array $requestParams): void {
        $this->wrkMatches->getMatch($requestParams);
    }

    /**
     * Create a new match
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        $this->wrkMatches->create($requestBody);
    }

    /**
     * Update a match
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        $this->wrkMatches->update($requestBody);
    }

    /**
     * Delete a match
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        $this->wrkMatches->delete($requestParams);
    }

}

<?php

namespace Ctrl;

use Wrk\WrkMatches;

class MatchesCtrl {

    private WrkMatches $wrkMatches;

    public function __construct() {
        $this->wrkMatches = new WrkMatches();
    }

    public function read(): void {
        $this->wrkMatches->read();
    }

    public function create(array $requestBody): void {
        $this->wrkMatches->create($requestBody);
    }

    public function update(array $requestBody): void {
        $this->wrkMatches->update($requestBody);
    }

    public function delete(array $requestParams): void {
        $this->wrkMatches->delete($requestParams);
    }

}

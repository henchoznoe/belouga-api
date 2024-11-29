<?php

namespace Ctrl;

use Wrk\WrkTeams;

class TeamsCtrl {

    private WrkTeams $wrkTeams;

    public function __construct() {
        $this->wrkTeams = new WrkTeams();
    }

    public function create(array $requestBody): void {
        $this->wrkTeams->create($requestBody);
    }

    public function read(): void {
        $this->wrkTeams->read();
    }

    public function update(array $requestBody): void {
        $this->wrkTeams->update($requestBody);
    }

    public function delete(array $requestParams): void {
        $this->wrkTeams->delete($requestParams);
    }

}

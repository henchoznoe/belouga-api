<?php

namespace Ctrl;

use Wrk\WrkAdmins;

class AdminsCtrl {

    private WrkAdmins $wrkAdmins;

    public function __construct() {
        $this->wrkAdmins = new WrkAdmins();
    }

    public function create(array $requestBody): void {
        $this->wrkAdmins->create($requestBody);
    }

    public function read(): void {
        $this->wrkAdmins->read();
    }

    public function update(array $requestBody): void {
        $this->wrkAdmins->update($requestBody);
    }

    public function delete(array $requestParams): void {
        $this->wrkAdmins->delete($requestParams);
    }

    public function getAdminTypes(): void {
        $this->wrkAdmins->getAdminTypes();
    }

}

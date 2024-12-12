<?php

namespace Ctrl;

use Wrk\WrkAdmins;

/**
 * Class AdminsCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class AdminsCtrl {

    private WrkAdmins $wrkAdmins;

    public function __construct() {
        $this->wrkAdmins = new WrkAdmins();
    }

    /**
     * Read all admins
     * @return void nothing is returned
     */
    public function read(): void {
        $this->wrkAdmins->read();
    }

    /**
     * Get a specific admin
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getAdmin(array $requestParams): void {
        $this->wrkAdmins->getAdmin($requestParams);
    }

    /**
     * Get all admin types
     * @return void nothing is returned
     */
    public function getAdminTypes(): void {
        $this->wrkAdmins->getAdminTypes();
    }

    /**
     * Create a new admin
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        $this->wrkAdmins->create($requestBody);
    }

    /**
     * Update an admin
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        $this->wrkAdmins->update($requestBody);
    }

    /**
     * Delete an admin
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        $this->wrkAdmins->delete($requestParams);
    }

}

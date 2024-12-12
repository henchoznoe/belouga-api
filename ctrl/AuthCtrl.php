<?php

namespace Ctrl;

use Wrk\WrkAuth;

/**
 * Class AuthCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class AuthCtrl {

    private WrkAuth $wrkAuth;

    public function __construct() {
        $this->wrkAuth = new WrkAuth();
    }

    /**
     * Authenticate an admin
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function login(array $requestBody): void {
        $this->wrkAuth->login($requestBody);
    }

    /**
     * Authorize an admin to access a specific resource
     * @param int $permissionRequired The permission required to access the resource
     * @return void nothing is returned
     */
    public function authorize(int $permissionRequired): void {
        $this->wrkAuth->authorize($permissionRequired);
    }

}

<?php

namespace Ctrl;

use Wrk\WrkAuth;

class AuthCtrl {

    private WrkAuth $wrkAuth;

    public function __construct() {
        $this->wrkAuth = new WrkAuth();
    }

    public function login(array $requestBody): void {
        $this->wrkAuth->login($requestBody);
    }

    public function authorize(int $permissionRequired): void {
        $this->wrkAuth->authorize($permissionRequired);
    }

}

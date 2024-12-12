<?php

namespace Ctrl;

use Wrk\WrkRounds;

/**
 * Class RoundsCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class RoundsCtrl {

    private WrkRounds $wrkRounds;

    public function __construct() {
        $this->wrkRounds = new WrkRounds();
    }

    /**
     * Read all rounds
     * @return void nothing is returned
     */
    public function read(): void {
        $this->wrkRounds->read();
    }

}

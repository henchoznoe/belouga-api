<?php

namespace Ctrl;

use Wrk\WrkRounds;

class RoundsCtrl {

    private WrkRounds $wrkRounds;

    public function __construct() {
        $this->wrkRounds = new WrkRounds();
    }

    public function read(): void {
        $this->wrkRounds->read();
    }

}

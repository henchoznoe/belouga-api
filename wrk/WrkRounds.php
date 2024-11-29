<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkRounds {

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = new WrkDatabase();
    }

    public function read(): void {
        $rounds = $this->wrkDB->select(GET_ROUNDS, [], true);
        HTTPResponses::success("Les rounds ont été récupérés avec succès.", $rounds);
    }

}

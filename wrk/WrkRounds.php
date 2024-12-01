<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkRounds {

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    public function read(): void {
        $rounds = $this->wrkDB->select(GET_ROUNDS, [], true);
        HTTPResponses::success("Les rounds ont été récupérés avec succès.", $rounds);
    }

}

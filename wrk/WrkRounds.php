<?php

namespace Wrk;

use HTTP\HTTPResponses;

/**
 * Class WrkRounds
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkRounds {

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    /**
     * Read all rounds
     * @return void nothing is returned
     */
    public function read(): void {
        $rounds = $this->wrkDB->select(GET_ROUNDS, [], true);
        HTTPResponses::success("Liste des rounds récupérée", $rounds);
    }

}

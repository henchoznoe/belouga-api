<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkMatches {

    private const REGEX_MATCHES_FK_TEAMS = "/^\d+$/";
    private const REGEX_MATCHES_FK_ROUNDS = "/^\d+$/";
    private const REGEX_MATCHES_SCORES = "/^\d+$/";
    private const REGEX_MATCHES_DATE = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = new WrkDatabase();
    }

    public function create() {
    }

    public function read(): void {
        $matches = $this->wrkDB->select(GET_MATCHES, [], true);
        HTTPResponses::success("Liste des matchs récupérée", $matches);
    }

}

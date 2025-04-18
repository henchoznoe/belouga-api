<?php

namespace Ctrl;

use Wrk\WrkRegister;

/**
 * Class RegisterCtrl
 * @package Ctrl
 * @author Noé Henchoz
 * @date 2024-12
 */
class RegisterCtrl {

    private WrkRegister $wrkRegister;

    public function __construct() {
        $this->wrkRegister = new WrkRegister();
    }

    public function getTeamsWithPlayers(): void {
        $this->wrkRegister->getTeamsWithPlayers();
    }

    public function getTeamWithPlayers(array $requestParams): void {
        $this->wrkRegister->getTeamWithPlayers($requestParams);
    }

    public function registerTeam(array $requestBody): void {
        $this->wrkRegister->registerTeam($requestBody);
    }

    public function registerPlayer(array $requestBody): void {
        $this->wrkRegister->registerPlayer($requestBody);
    }

    public function registerPlayerTrackmania(array $requestBody): void {
        $this->wrkRegister->registerPlayerTrackmania($requestBody);
    }

}

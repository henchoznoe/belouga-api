<?php

namespace HTTP;

use Ctrl\AuthCtrl;
use Ctrl\AdminsCtrl;
use Ctrl\PlayersCtrl;
use Ctrl\TeamsCtrl;
use Ctrl\RoundsCtrl;
use Ctrl\MatchesCtrl;

require_once ROOT . "/ctrl/index.php";

/**
 * Class HTTPHandlers
 * @package HTTP
 * @author Noé Henchoz
 * @date 2024-12
 */
class HTTPHandlers {

    private const ACTION = "action";

    private const UNSPECIFIED_ACTION = "The action is not specified";
    private const UNKNOWN_ACTION = "Unknown action";
    private const ERROR_REQUEST_BODY = "Error in the request body";

    private AuthCtrl $authCtrl;
    private AdminsCtrl $adminsCtrl;
    private PlayersCtrl $playersCtrl;
    private TeamsCtrl $teamsCtrl;
    private RoundsCtrl $roundsCtrl;
    private MatchesCtrl $matchesCtrl;

    public function __construct() {
        $this->authCtrl = new AuthCtrl();
        $this->adminsCtrl = new AdminsCtrl();
        $this->playersCtrl = new PlayersCtrl();
        $this->teamsCtrl = new TeamsCtrl();
        $this->roundsCtrl = new RoundsCtrl();
        $this->matchesCtrl = new MatchesCtrl();
    }

    public function GET(): void {
        if ( isset($_GET[self::ACTION]) ) {
            $requestParams = $this->checkRequestParams();
            switch ( $_GET[self::ACTION] ) {
                case "getAdmins":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->read();
                    break;
                case "getAdminTypes":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->getAdminTypes();
                    break;
                case "getAdmin":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->getAdmin($requestParams);
                    break;
                case "getPlayers":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->playersCtrl->read();
                    break;
                case "getPlayer":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->playersCtrl->getPlayer($requestParams);
                    break;
                case "getTeams":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->teamsCtrl->read();
                    break;
                case "getRounds":
                    $this->roundsCtrl->read();
                    break;
                case "getMatches":
                    $this->matchesCtrl->read();
                    break;
                default:
                    HTTPResponses::error(400, self::UNKNOWN_ACTION);
                    break;
            }
        } else {
            HTTPResponses::error(400, self::UNSPECIFIED_ACTION);
        }
    }

    public function POST(): void {
        $requestBody = $this->checkRequestBody();
        if ( isset($requestBody[self::ACTION]) ) {
            switch ( $requestBody[self::ACTION] ) {
                case "login":
                    $this->authCtrl->login($requestBody);
                    break;
                case "createAdmin":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->create($requestBody);
                    break;
                case "createPlayer":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->playersCtrl->create($requestBody);
                    break;
                case "createTeam":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->teamsCtrl->create($requestBody);
                    break;
                case "createMatch":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->matchesCtrl->create($requestBody);
                    break;
                default:
                    HTTPResponses::error(400, self::UNKNOWN_ACTION);
                    break;
            }
        } else {
            HTTPResponses::error(401, self::UNSPECIFIED_ACTION);
        }
    }

    public function PUT(): void {
        $requestBody = $this->checkRequestBody();
        if ( isset($requestBody[self::ACTION]) ) {
            switch ( $requestBody[self::ACTION] ) {
                case "updateAdmin":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->update($requestBody);
                    break;
                case "updatePlayer":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->playersCtrl->update($requestBody);
                    break;
                case "updateTeam":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->teamsCtrl->update($requestBody);
                    break;
                case "updateMatch":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->matchesCtrl->update($requestBody);
                    break;
                default:
                    HTTPResponses::error(400, self::UNKNOWN_ACTION);
                    break;
            }
        } else {
            HTTPResponses::error(400, self::UNSPECIFIED_ACTION);
        }
    }

    public function DELETE(): void {
        if ( isset($_GET[self::ACTION]) ) {
            $requestParams = $this->checkRequestParams();
            switch ( $_GET[self::ACTION] ) {
                case "deleteAdmin":
                    $this->authCtrl->authorize(ROLES::SUPER_ADMIN->value);
                    $this->adminsCtrl->delete($requestParams);
                    break;
                case "deletePlayer":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->playersCtrl->delete($requestParams);
                    break;
                case "deleteTeam":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->teamsCtrl->delete($requestParams);
                    break;
                case "deleteMatch":
                    $this->authCtrl->authorize(ROLES::ADMIN->value);
                    $this->matchesCtrl->delete($requestParams);
                    break;
                default:
                    HTTPResponses::error(400, self::UNKNOWN_ACTION);
                    break;
            }
        } else {
            HTTPResponses::error(400, self::UNSPECIFIED_ACTION);
        }
    }

    /**
     * Check the request parameters and return them as an array
     * @return array request parameters
     */
    private function checkRequestParams(): array {
        $requestParams = array();
        foreach ( $_GET as $key => $value ) {
            if ( $key !== self::ACTION ) $requestParams[$key] = $value;
        }
        return $requestParams;
    }

    /**
     * Check the request body and return it as an array
     * @return array|null request body
     */
    private function checkRequestBody(): ?array {
        $requestBody = json_decode(file_get_contents("php://input"), true);
        if ( $requestBody === null || json_last_error() !== JSON_ERROR_NONE ) {
            HTTPResponses::error(400, self::ERROR_REQUEST_BODY);
        }
        return $requestBody;
    }

}

enum ROLES: int {
    case SUPER_ADMIN = 2;
    case ADMIN = 1;
}

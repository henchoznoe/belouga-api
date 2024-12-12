<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkTeams {

    private const REGEX_TEAM_NAME = '/^[a-zA-Z0-9._\s-]{1,32}$/';
    private const REGEX_TEAM_SIZE = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    public function create(array $requestBody): void {
        if ( !isset($requestBody['name']) || !isset($requestBody['size']) ) {
            HTTPResponses::error(400, "Le nom de l'équipe et la taille de l'équipe doivent être spécifiés");
        }
        $name = $requestBody['name'];
        $size = $requestBody['size'];
        $validations = [
            'name' => [self::REGEX_TEAM_NAME, "Le nom de l'équipe ne respecte pas le bon format"],
            'size' => [self::REGEX_TEAM_SIZE, "La taille de l'équipe doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingTeamByName = $this->checkTeamExistenceByName($name);
        if ( $existingTeamByName ) HTTPResponses::error(409, "Une équipe avec ce nom existe déjà");
        $this->wrkDB->execute(INSERT_TEAM, [$name, $size]);
        $addedTeam = $this->getTeamById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Équipe créée avec succès", $addedTeam);
    }

    public function read(): void {
        $teams = $this->wrkDB->select(GET_TEAMS, [], true);
        HTTPResponses::success("Liste des équipes récupérée", $teams);
    }

    public function update(array $requestBody): void {
        if ( !isset($requestBody['pk_team']) ) {
            HTTPResponses::error(400, "La clé primaire de l'équipe doit être spécifiée");
        }
        $pkTeam = $requestBody['pk_team'];
        $team = $this->getTeamById($pkTeam);
        if ( !$team ) HTTPResponses::error(404, "L'équipe spécifiée n'existe pas");
        $size = $requestBody['size'] ?? $team['size'];
        $name = $requestBody['name'] ?? $team['name'];
        $validations = [
            'name' => [self::REGEX_TEAM_NAME, "Le nom de l'équipe ne respecte pas le bon format"],
            'size' => [self::REGEX_TEAM_SIZE, "La taille de l'équipe doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                if ( $field === 'size' && $size === $team['size'] ) continue;
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingTeamByName = $this->checkTeamExistenceByName($name);
        if ( $existingTeamByName && intval($existingTeamByName['pk_team']) !== intval($pkTeam) ) HTTPResponses::error(409, "Une équipe avec ce nom existe déjà");
        $this->wrkDB->execute(UPDATE_TEAM, [$name, $size, $pkTeam]);
        $updatedTeam = $this->getTeamById($pkTeam);
        HTTPResponses::success("Équipe modifiée avec succès", $updatedTeam);
    }

    public function delete(array $requestParams): void {
        if ( !isset($requestParams['pk_team']) ) {
            HTTPResponses::error(400, "La clé primaire de l'équipe doit être spécifiée");
        }
        $pkTeam = $requestParams['pk_team'];
        $team = $this->getTeamById($pkTeam);
        if ( !$team ) HTTPResponses::error(404, "L'équipe spécifiée n'existe pas");
        $matches = $this->wrkDB->select(GET_MATCHES_BY_TEAM, [$pkTeam, $pkTeam], true);
        if ( $matches ) HTTPResponses::error(409, "L'équipe est utilisée dans un ou plusieurs matchs");
        $this->wrkDB->execute(DELETE_TEAM, [$pkTeam]);
        HTTPResponses::success("Équipe supprimée avec succès");
    }

    private function checkTeamExistenceByName(string $name): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_NAME, [$name]);
    }

    private function getTeamById(int $pkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$pkTeam]);
    }

}

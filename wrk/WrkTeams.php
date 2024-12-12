<?php

namespace Wrk;

use HTTP\HTTPResponses;

/**
 * Class WrkTeams
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkTeams {

    private const REGEX_TEAMS_PK_TEAM = "/^\d+$/";
    private const REGEX_TEAMS_NAME = "/^[a-zA-Z0-9._-]{1,32}$/";
    private const REGEX_TEAMS_CAPACITY = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    /**
     * Read all teams
     * @return void nothing is returned
     */
    public function read(): void {
        $teams = $this->wrkDB->select(GET_TEAMS, [], true);
        HTTPResponses::success("Liste des équipes récupérée", $teams);
    }

    /**
     * Get a team
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function getTeam(array $requestParams): void {
        // Check if the required parameter is set
        if ( !isset($requestParams['pk_team']) ) {
            HTTPResponses::error(400, "La clé primaire de l'équipe doit être spécifiée");
        }
        // Validate the field
        $pkTeam = $requestParams['pk_team'];
        $validations = [
            'pk_team' => [self::REGEX_TEAMS_PK_TEAM, "La clé primaire de l'équipe doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Get the team by tis id and send it as a response or send an error if it doesn't exist
        $team = $this->getTeamById($pkTeam);
        if ( $team ) {
            HTTPResponses::success("Équipe récupérée avec succès", $team);
        } else {
            HTTPResponses::error(404, "Aucune équipe trouvée avec cet identifiant n'a été trouvée");
        }
    }

    /**
     * Create a new team
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function create(array $requestBody): void {
        // Check if the required fields are set
        if ( !isset($requestBody['name']) || !isset($requestBody['capacity']) ) {
            HTTPResponses::error(400, "Le nom de l'équipe et la capacité de l'équipe doivent être spécifiés");
        }
        // Validate the fields
        $name = $requestBody['name'];
        $size = $requestBody['capacity'];
        $validations = [
            'name' => [self::REGEX_TEAMS_NAME, "Le nom de l'équipe ne respecte pas le bon format"],
            'capacity' => [self::REGEX_TEAMS_CAPACITY, "La capacité de l'équipe doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the team already exists
        if ( $this->getTeamByName($name) ) {
            HTTPResponses::error(409, "Une équipe avec ce nom existe déjà");
        }
        // Insert the team into the database
        $this->wrkDB->execute(INSERT_TEAM, [$name, $size]);
        // Get the added team and send it as a response
        $addedTeam = $this->getTeamById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Équipe créée avec succès", $addedTeam);
    }

    /**
     * Update a team
     * @param array $requestBody The request body
     * @return void nothing is returned
     */
    public function update(array $requestBody): void {
        // Check if the required field is set
        if ( !isset($requestBody['pk_team']) ) {
            HTTPResponses::error(400, "L'identifiant de l'équipe doit être spécifié pour la mise à jour");
        }
        $pkTeam = $requestBody['pk_team'];
        // Check if the team exists
        $team = $this->getTeamById($pkTeam);
        if ( !$team ) {
            HTTPResponses::error(404, "Aucune équipe avec cet identifiant n'a été trouvée");
        }
        // Validate the fields
        $fields = [
            'name' => [self::REGEX_TEAMS_NAME, "Le nom de l'équipe ne respecte pas le bon format"],
            'capacity' => [self::REGEX_TEAMS_CAPACITY, "La capacité de l'équipe doit être un nombre entier positif"]
        ];
        // Prepare the fields to be updated
        $updates = [];
        $params = [];
        foreach ( $fields as $field => $validation ) {
            if ( isset($requestBody[$field]) ) {
                if ( !preg_match($validation[0], $requestBody[$field]) ) {
                    HTTPResponses::error(400, $validation[1]);
                }
                switch ( $field ) {
                    case 'name':
                        // Check if the team name is already taken
                        if ( $this->getTeamByName($requestBody['name']) && $requestBody['name'] !== $team['name'] ) {
                            HTTPResponses::error(409, "Une équipe avec ce nom existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'capacity':
                        // Check if the capacity is over the actual number of players in the team
                        if ( $this->getPlayersCountByTeam($pkTeam) > intval($requestBody['capacity']) ) {
                            HTTPResponses::error(409, "La capacité de l'équipe ne peut pas être inférieure au nombre de joueurs actuels");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    default:
                        // Add the field to the updates
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                }
            }
        }
        // Check if there are fields to update
        if ( empty($updates) ) {
            HTTPResponses::error(400, "Aucun champ à mettre à jour n'a été spécifié");
        }
        // Add the id of the team to update
        $params[] = $pkTeam;
        // Update the team in the database
        $query = "UPDATE Teams SET " . implode(", ", $updates) . " WHERE pk_team = ?";
        $this->wrkDB->execute($query, $params);
        // Get the updated team and send it as a response
        $updatedTeam = $this->getTeamById($pkTeam);
        HTTPResponses::success("Équipe mise à jour avec succès", $updatedTeam);
    }

    /**
     * Delete a team
     * @param array $requestParams The request parameters
     * @return void nothing is returned
     */
    public function delete(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_team']) ) {
            HTTPResponses::error(400, "L'identifiant de l'équipe doit être spécifiée");
        }
        $pkTeam = $requestParams['pk_team'];
        // Validate the field
        $validations = [
            'pk_team' => [self::REGEX_TEAMS_PK_TEAM, "L'identifiant de l'équipe doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the team exists
        $existingTeam = $this->getTeamById($pkTeam);
        if ( !$existingTeam ) {
            HTTPResponses::error(404, "Aucune équipe trouvée avec cet identifiant n'a été trouvée");
        }
        // Check if the team is used in a match
        $matches = $this->wrkDB->select(GET_MATCHES_BY_TEAM, [$pkTeam, $pkTeam], true);
        if ( !$matches ) {
            // Delete the team from the database and send the deleted team as a response
            $this->wrkDB->execute(DELETE_TEAM, [$pkTeam]);
            HTTPResponses::success("Équipe supprimée avec succès", $existingTeam);
        } else {
            // Send an error if the team is used in a match
            HTTPResponses::error(409, "L'équipe est utilisée dans un ou plusieurs matchs");
        }
    }

    /**
     * Get a team by its name
     * @param string $name The name of the team
     * @return array|bool The team or false if it doesn't exist
     */
    private function getTeamByName(string $name): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_NAME, [$name]);
    }

    /**
     * Get a team by its id
     * @param int $pkTeam The id of the team
     * @return array|bool The team or false if it doesn't exist
     */
    private function getTeamById(int $pkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$pkTeam]);
    }

    /**
     * Get the number of players in a team
     * @param int $pkTeam The id of the team
     * @return int The number of players in the team
     */
    private function getPlayersCountByTeam(int $pkTeam): int {
        return intval($this->wrkDB->select(GET_PLAYERS_COUNT_BY_TEAM, [$pkTeam])['count']);
    }

}

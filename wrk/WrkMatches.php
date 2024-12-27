<?php

namespace Wrk;

use DateTime;
use Exception;
use HTTP\HTTPResponses;

/**
 * Class WrkMatches
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkMatches {

    private const REGEX_MATCHES_PK_MATCH = "/^\d+$/";
    private const REGEX_MATCHES_FK_TEAMS = "/^\d+$/";
    private const REGEX_MATCHES_FK_ROUNDS = "/^\d+$/";
    private const REGEX_MATCHES_SCORES = "/^\d+$/";
    private const REGEX_MATCHES_DATE = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})$/';
    private const REGEX_MATCHES_FK_WINNER = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    /**
     * Read all matches
     * @return void nothing to return
     */
    public function read(): void {
        $matches = $this->wrkDB->select(GET_MATCHES, [], true);
        HTTPResponses::success("Liste des matchs récupérée", $matches);
    }

    /**
     * Get a match
     * @param array $requestParams the request parameters
     * @return void nothing to return
     */
    public function getMatch(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_match']) ) {
            HTTPResponses::error(400, "L'identifiant du match doit être spécifié");
        }
        // Validate the field
        $pkMatch = $requestParams['pk_match'];
        $validations = [
            "pk_match" => [self::REGEX_MATCHES_PK_MATCH, "L'identifiant du match doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Get the match by its id and send it as a response or send an error if it doesn't exist
        $match = $this->getMatchById($pkMatch);
        if ( $match ) {
            HTTPResponses::success("Match récupéré avec succès", $match);
        } else {
            HTTPResponses::error(404, "Aucun match avec cet identifiant n'a été trouvé");
        }

    }

    /**
     * Create a match
     * @param array $requestBody the request body
     * @return void nothing to return
     */
    public function create(array $requestBody): void {
        // Check if the required fields are set
        if ( !isset($requestBody['fk_round']) ) {
            HTTPResponses::error(400, "Le fk_round doit être spécifié");
        }
        // Validate the fields
        $fkRound = $requestBody['fk_round'];
        // Optional fields
        $fkTeamOne = $requestBody['fk_team_one'] ?? null;
        $fkTeamTwo = $requestBody['fk_team_two'] ?? null;
        $teamOneScore = $requestBody['team_one_score'] ?? null;
        $teamTwoScore = $requestBody['team_two_score'] ?? null;
        $matchDate = $requestBody['match_date'] ?? null;
        $fkWinner = $requestBody['winner_team'] ?? null;
        $validations = [
            "fk_round" => [self::REGEX_MATCHES_FK_ROUNDS, "Le fk_round doit être un nombre entier positif"],
            "fk_team_one" => [self::REGEX_MATCHES_FK_TEAMS, "Le fk_team_one doit être un nombre entier positif"],
            "fk_team_two" => [self::REGEX_MATCHES_FK_TEAMS, "Le fk_team_two doit être un nombre entier positif"],
            "team_one_score" => [self::REGEX_MATCHES_SCORES, "Le team_one_score doit être un nombre entier positif"],
            "team_two_score" => [self::REGEX_MATCHES_SCORES, "Le team_two_score doit être un nombre entier positif"],
            "match_date" => [self::REGEX_MATCHES_DATE, "Le match_date doit être une date au format YYYY-MM-DD HH:MM:SS"],
            "winner_team" => [self::REGEX_MATCHES_FK_WINNER, "Le winner_team doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            $value = $requestBody[$field] ?? null;
            if ( !preg_match($validation[0], $value) ) {
                // If the field is optional and not set, continue
                if ( $field === 'fk_team_one' && $fkTeamOne === null ) continue;
                if ( $field === 'fk_team_two' && $fkTeamTwo === null ) continue;
                if ( $field === 'team_one_score' && $teamOneScore === null ) continue;
                if ( $field === 'team_two_score' && $teamTwoScore === null ) continue;
                if ( $field === 'match_date' && $matchDate === null ) continue;
                if ( $field === 'winner_team' && $fkWinner === null ) continue;
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the round exists
        if ( !$this->getRoundById($fkRound) ) {
            HTTPResponses::error(404, "Le round n'existe pas");
        }
        // Check if the teams exist
        if ( $fkTeamOne !== null ) {
            if ( !$this->getTeamById($fkTeamOne) ) {
                HTTPResponses::error(404, "L'équipe 1 n'existe pas");
            }
        }
        if ( $fkTeamTwo !== null ) {
            if ( !$this->getTeamById($fkTeamTwo) ) {
                HTTPResponses::error(404, "L'équipe 2 n'existe pas");
            }
        }
        // Check if the teams are different
        if ( $fkTeamOne !== null && $fkTeamTwo !== null ) {
            if ( $fkTeamOne === $fkTeamTwo ) {
                HTTPResponses::error(400, "Les deux équipes doivent être différentes");
            }
        }
        // Check if the winner team exists and is one of the two teams
        if ( $fkWinner !== null ) {
            if ( !$this->getTeamById($fkWinner) ) {
                HTTPResponses::error(404, "L'équipe gagnante n'existe pas");
            }
            if ( $fkWinner !== $fkTeamOne && $fkWinner !== $fkTeamTwo ) {
                HTTPResponses::error(400, "L'équipe gagnante doit être une des deux équipes");
            }
        }
        // Check if the match date is set and convert it
        if ( $matchDate !== null ) {
            try {
                $matchDate = new DateTime($matchDate);
            } catch ( Exception $e ) {
                HTTPResponses::error(400, "Le match_date doit être une date au format ISO 8601");
            }
            $matchDate = $matchDate->format('Y-m-d H:i:s');
        }
        // Insert the match in the database
        $this->wrkDB->execute(INSERT_MATCH, [$fkTeamOne, $fkTeamTwo, $fkRound, $teamOneScore, $teamTwoScore, $matchDate, $fkWinner]);
        $addedMatch = $this->getMatchById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Match ajouté avec succès", $addedMatch);
    }

    /**
     * Update a match
     * @param array $requestBody the request body
     * @return void nothing to return
     */
    public function update(array $requestBody): void {
        // Check if the required field is set
        if ( !isset($requestBody['pk_match']) ) {
            HTTPResponses::error(400, "L'identifiant du match doit être spécifié");
        }
        $pkMatch = $requestBody['pk_match'];
        // Check if the match exists
        $existingMatch = $this->getMatchById($pkMatch);
        if ( !$existingMatch ) {
            HTTPResponses::error(404, "Aucun match avec cet identifiant n'a été trouvé");
        }
        // Validate the fields
        $fields = [
            "fk_team_one" => [self::REGEX_MATCHES_FK_TEAMS, "Le fk_team_one doit être un nombre entier positif"],
            "fk_team_two" => [self::REGEX_MATCHES_FK_TEAMS, "Le fk_team_two doit être un nombre entier positif"],
            "team_one_score" => [self::REGEX_MATCHES_SCORES, "Le team_one_score doit être un nombre entier positif"],
            "team_two_score" => [self::REGEX_MATCHES_SCORES, "Le team_two_score doit être un nombre entier positif"],
            "match_date" => [self::REGEX_MATCHES_DATE, "Le match_date doit être une date au format YYYY-MM-DD HH:MM:SS"],
            "winner_team" => [self::REGEX_MATCHES_FK_WINNER, "Le winner_team doit être un nombre entier positif"]
        ];
        // Prepare the fields to update
        $updates = [];
        $params = [];
        foreach ( $fields as $field => $validation ) {
            if ( array_key_exists($field, $requestBody) ) {
                // If the fields can be null, check if the value is null and add it to the updates
                if ( $field == 'fk_team_one' || $field == 'fk_team_two' || $field == 'winner_team' || $field == 'team_one_score' || $field == 'team_two_score' || $field == 'match_date' ) {
                    if ( $requestBody[$field] === null ) {
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        continue;
                    }
                }
                if ( !preg_match($validation[0], $requestBody[$field]) ) {
                    HTTPResponses::error(400, $validation[1]);
                }
                switch ( $field ) {
                    case 'fk_round':
                        if ( !$this->getRoundById($requestBody[$field]) ) {
                            HTTPResponses::error(404, "Le round n'existe pas");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'fk_team_one':
                        if ( !$this->getTeamById($requestBody[$field]) ) {
                            HTTPResponses::error(404, "L'équipe 1 n'existe pas");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'fk_team_two':
                        if ( !$this->getTeamById($requestBody[$field]) ) {
                            HTTPResponses::error(404, "L'équipe 2 n'existe pas");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'winner_team':
                        if ( !$this->getTeamById($requestBody[$field]) ) {
                            HTTPResponses::error(404, "L'équipe gagnante n'existe pas");
                        }
                        $teamOne = $requestBody['fk_team_one'] ?? $existingMatch['fk_team_one'];
                        $teamTwo = $requestBody['fk_team_two'] ?? $existingMatch['fk_team_two'];
                        $isTeamOneMissing = $teamOne === null;
                        $isTeamTwoMissing = $teamTwo === null;
                        if ( ($isTeamOneMissing || $isTeamTwoMissing) &&
                            !(isset($requestBody['fk_team_one']) && isset($requestBody['fk_team_two'])) ) {
                            HTTPResponses::error(400, "Impossible de définir un vainqueur si une équipe est manquante");
                        }
                        $winnerTeam = intval($requestBody[$field]);
                        if ( $winnerTeam !== intval($teamOne) && $winnerTeam !== intval($teamTwo) ) {
                            HTTPResponses::error(400, "L'équipe gagnante doit être une des deux équipes");
                        }

                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                        break;
                    case 'match_date':
                        try {
                            $matchDate = new DateTime($requestBody[$field]);
                        } catch ( Exception $e ) {
                            HTTPResponses::error(400, "Le match_date doit être une date au format ISO 8601");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $matchDate->format('Y-m-d H:i:s');
                        break;
                    default:
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
        // Add the id of the match to update
        $params[] = $pkMatch;
        // Update the match in the database
        $query = "UPDATE Matches SET " . implode(", ", $updates) . " WHERE pk_match = ?";
        $this->wrkDB->execute($query, $params);
        // Get the updated match and send it as a response
        $updatedMatch = $this->getMatchById($pkMatch);
        HTTPResponses::success("Match mis à jour avec succès", $updatedMatch);
    }

    /**
     * Delete a match
     * @param array $requestParams the request parameters
     * @return void nothing to return
     */
    public function delete(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_match']) ) {
            HTTPResponses::error(400, "L'identifiant du match doit être spécifié");
        }
        $pkMatch = $requestParams['pk_match'];
        // Validate the field
        $validations = [
            "pk_match" => [self::REGEX_MATCHES_PK_MATCH, "L'identifiant du match doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the match exists
        $existingMatch = $this->getMatchById($pkMatch);
        if ( $existingMatch ) {
            // Delete the match from the database and send the deleted match as a response
            $this->wrkDB->execute(DELETE_MATCH, [$pkMatch]);
            HTTPResponses::success("Match supprimé avec succès", $existingMatch);
        } else {
            // Send an error if the match doesn't exist
            HTTPResponses::error(404, "Aucun match avec cet identifiant n'a été trouvé");
        }
    }

    /**
     * Get a match by its id
     * @param int $pkMatch The id of the match
     * @return array|bool The match or false if it doesn't exist
     */
    private function getMatchById(int $pkMatch): array|bool {
        return $this->wrkDB->select(GET_MATCH_BY_PK, [$pkMatch]);
    }

    /**
     * Get a round by its id
     * @param int $fkRound The id of the round
     * @return array|bool The round or false if it doesn't exist
     */
    private function getRoundById(int $fkRound): array|bool {
        return $this->wrkDB->select(GET_ROUND_BY_PK, [$fkRound]);
    }

    /**
     * Get a team by its id
     * @param int $fkTeam The id of the team
     * @return array|bool The team or false if it doesn't exist
     */
    private function getTeamById(int $fkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$fkTeam]);
    }

}

<?php

namespace Wrk;

use HTTP\HTTPResponses;

/**
 * Class WrkRegister
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkRegister {

    private const REGEX_TEAMS_PK_TEAM = "/^\d+$/";
    private const REGEX_TEAMS_NAME = "/^.{1,32}$/";
    private const REGEX_PLAYERS_USERNAME = "/^.{1,32}$/";
    private const REGEX_PLAYERS_RIOT_ID = "/^.{1,32}$/";
    private const REGEX_PLAYERS_DISCORD = "/^.{1,32}$/";
    private const REGEX_PLAYERS_TWITCH = '/^https:\/\/(www\.)?twitch\.tv\/[a-zA-Z0-9_]{1,32}$/';
    private const REGEX_PLAYERS_RANK = "/^.{1,32}$/";
    private const REGEX_PLAYERS_FK_TEAM = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    /**
     * Get all the teams with their players
     * @return void Nothing is returned
     */
    public function getTeamsWithPlayers(): void {
        $teamsWithPlayers = $this->wrkDB->select(GET_TEAMS_WITH_PLAYERS, [], true);
        HTTPResponses::success("Liste des équipes avec les joueurs récupérée", $teamsWithPlayers);
    }

    /**
     * Get a team with its players
     * @param array $requestParams The request parameters
     * @return void Nothing is returned
     */
    public function getTeamWithPlayers(array $requestParams): void {
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
     * Register a new team
     * @param array $requestBody The request body
     * @return void Nothing is returned
     */
    public function registerTeam(array $requestBody): void {
        // Check if the required fields are set
        if ( !isset($requestBody['name']) ) {
            HTTPResponses::error(400, "Le nom de l'équipe doit être spécifié");
        }
        // Validate the fields
        $name = $requestBody['name'];
        $validations = [
            'name' => [self::REGEX_TEAMS_NAME, "Le nom de l'équipe ne respecte pas le bon format"]
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
        $this->wrkDB->execute(INSERT_TEAM, [$name, 5]);
        // Get the added team and send it as a response
        $addedTeam = $this->getTeamById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Équipe créée avec succès", $addedTeam);
    }

    /**
     * Register a new player
     * @param array $requestBody The request body
     * @return void Nothing is returned
     */
    public function registerPlayer(array $requestBody): void {
        // Check if the required fields are set
        if ( !isset($requestBody['username']) || !isset($requestBody['riot_username']) || !isset($requestBody['discord']) || !isset($requestBody['rank']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur, le riot_username, le discord et le rank doivent être spécifiés");
        }
        // Validate the fields
        $username = $requestBody['username'];
        $riotUsername = $requestBody['riot_username'];
        $discord = $requestBody['discord'];
        $rank = $requestBody['rank'];
        // Optional fields
        $twitchUrl = $requestBody['twitch'] ?? null;
        $fkTeam = $requestBody['fk_team'] ?? null;
        $validations = [
            'username' => [self::REGEX_PLAYERS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'riot_username' => [self::REGEX_PLAYERS_RIOT_ID, "Le riot_username ne respecte pas le bon format"],
            'discord' => [self::REGEX_PLAYERS_DISCORD, "Le discord ne respecte pas le bon format"],
            'rank' => [self::REGEX_PLAYERS_RANK, "Le rank ne respecte pas le bon format"],
            'twitch' => [self::REGEX_PLAYERS_TWITCH, "Le lien twitch ne respecte pas le bon format"],
            'fk_team' => [self::REGEX_PLAYERS_FK_TEAM, "Le pk_team ne respecte pas le bon format"]
        ];
        foreach ( $validations as $field => $validation ) {
            $value = $requestBody[$field] ?? null;
            if ( !preg_match($validation[0], $value) ) {
                // If the field is optional and not set, continue
                if ( $field === 'twitch' && $twitchUrl === null ) continue;
                if ( $field === 'fk_team' && $fkTeam === null ) continue;
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the player already exists
        if ( $this->getPlayerByUsername($username) ) {
            HTTPResponses::error(409, "Un joueur avec ce nom d'utilisateur existe déjà");
        }
        if ( $this->getPlayerByRiotUsername($riotUsername) ) {
            HTTPResponses::error(409, "Un joueur avec ce riot_username existe déjà");
        }
        if ( $this->getPlayerByDiscord($discord) ) {
            HTTPResponses::error(409, "Un joueur avec ce discord existe déjà");
        }
        if ( $twitchUrl !== null ) {
            if ( $this->getPlayerByTwitchUrl($twitchUrl) ) {
                HTTPResponses::error(409, "Un joueur avec cet URL Twitch existe déjà");
            }
        }
        if ( $fkTeam !== null ) {
            // Check if the team exists
            if ( !$this->getTeamById($fkTeam) ) {
                HTTPResponses::error(404, "Cette équipe n'existe pas");
            }
            // Check if the team is full
            if ( $this->getPlayersCountByTeam($fkTeam) >= 5 ) {
                HTTPResponses::error(409, "L'équipe est pleine");
            }
        }
        // Insert the player into the database
        $this->wrkDB->execute(INSERT_PLAYER, [$username, $riotUsername, $discord, $twitchUrl, $rank, $fkTeam]);
        $addedPlayer = $this->getPlayerById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Joueur créé avec succès", $addedPlayer);
    }

    public function registerPlayerTrackmania(array $requestBody) {
    // Check if the required fields are set
        if ( !isset($requestBody['username']) || !isset($requestBody['discord']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur et le discord doivent être spécifiés");
        }
        $username = $requestBody['username'];
        $discord = $requestBody['discord'];
        $twitchUrl = $requestBody['twitch'] ?? null;
        // Check if the player already exists
        if ( $this->getPlayerByUsername($username) ) {
            HTTPResponses::error(409, "Un joueur avec ce nom d'utilisateur existe déjà");
        }
        if ( $this->getPlayerByDiscord($discord) ) {
            HTTPResponses::error(409, "Un joueur avec ce discord existe déjà");
        }
        if ( $twitchUrl !== null ) {
            if ( $this->getPlayerByTwitchUrl($twitchUrl) ) {
                HTTPResponses::error(409, "Un joueur avec cet URL Twitch existe déjà");
            }
        }
    $this->wrkDB->execute(INSERT_PLAYER, [$username, null, $discord, $twitchUrl, null, null]);
    $addedPlayer = $this->getPlayerById($this->wrkDB->lastInsertId());
    HTTPResponses::success("Joueur créé avec succès", $addedPlayer);
    }

    /**
     * Get a team by its id
     * @param int $pkTeam The id of the team
     * @return array|bool the team if it exists, false otherwise
     */
    private function getTeamById(int $pkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$pkTeam]);
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
     * Get a player by its username
     * @param string $username The id of the player
     * @return array|bool the player if it exists, false otherwise
     */
    private function getPlayerByUsername(string $username): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_USERNAME, [$username]);
    }

    /**
     * Get a player by its riot username
     * @param string $riotUsername The riot username of the player
     * @return array|bool the player if it exists, false otherwise
     */
    private function getPlayerByRiotUsername(string $riotUsername): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_RIOT_USERNAME, [$riotUsername]);
    }

    /**
     * Get a player by its discord
     * @param string $discord The discord of the player
     * @return array|bool the player if it exists, false otherwise
     */
    private function getPlayerByDiscord(string $discord): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_DISCORD, [$discord]);
    }

    /**
     * Get a player by its twitch url
     * @param string $twitchUrl The twitch url of the player
     * @return array|bool the player if it exists, false otherwise
     */
    private function getPlayerByTwitchUrl(string $twitchUrl): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_TWITCH, [$twitchUrl]);
    }

    /**
     * Get a player by its id
     * @param int $pkPlayer The id of the player
     * @return array|bool the player if it exists, false otherwise
     */
    private function getPlayerById(int $pkPlayer): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_PK, [$pkPlayer]);
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

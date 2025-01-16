<?php

namespace Wrk;

use HTTP\HTTPResponses;

/**
 * Class WrkPlayers
 * @package Wrk
 * @author Noé Henchoz
 * @date 2024-12
 */
class WrkPlayers {

    private const REGEX_PLAYERS_PK_PLAYER = "/^\d+$/";
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
     * Read all players
     * @return void nothing to return
     */
    public function read(): void {
        $players = $this->wrkDB->select(GET_PLAYERS, [], true);
        HTTPResponses::success("Liste des joueurs récupérée", $players);
    }

    /**
     * Get a player
     * @param array $requestParams the request parameters
     * @return void nothing to return
     */
    public function getPlayer(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_player']) ) {
            HTTPResponses::error(400, "L'identifiant du joueur doit être spécifié");
        }
        // Validate the field
        $pkPlayer = $requestParams['pk_player'];
        $validations = [
            'pk_player' => [self::REGEX_PLAYERS_PK_PLAYER, "L'identifiant du joueur doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Get the player by its id and send it as a response or send an error if it doesn't exist
        $player = $this->getPlayerById($pkPlayer);
        if ( $player ) {
            HTTPResponses::success("Joueur récupéré avec succès", $player);
        } else {
            HTTPResponses::error(404, "Aucun joueur avec cet identifiant n'a été trouvé");
        }
    }

    /**
     * Create a player
     * @param array $requestBody the request body
     * @return void nothing to return
     */
    public function create(array $requestBody): void {
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
        // Check if the team exists
        if ( $fkTeam !== null ) {
            if ( !$this->getTeamById($fkTeam) ) {
                HTTPResponses::error(404, "Cette équipe n'existe pas");
            }
        }
        // Insert the player into the database
        $this->wrkDB->execute(INSERT_PLAYER, [$username, $riotUsername, $discord, $twitchUrl, $rank, $fkTeam]);
        $addedPlayer = $this->getPlayerById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Joueur créé avec succès", $addedPlayer);
    }

    /**
     * Update a player
     * @param array $requestBody the request body
     * @return void nothing to return
     */
    public function update(array $requestBody): void {
        // Check if the required field is set
        if ( !isset($requestBody['pk_player']) ) {
            HTTPResponses::error(400, "L'identifiant du joueur doit être spécifié pour la mise à jour");
        }
        $pkPlayer = $requestBody['pk_player'];
        // Check if the player exists
        $existingPlayer = $this->getPlayerById($pkPlayer);
        if ( !$existingPlayer ) {
            HTTPResponses::error(404, "Aucun joueur avec cet identifiant n'a été trouvé");
        }
        // Validate the fields
        $fields = [
            'username' => [self::REGEX_PLAYERS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'riot_username' => [self::REGEX_PLAYERS_RIOT_ID, "Le riot_username ne respecte pas le bon format"],
            'discord' => [self::REGEX_PLAYERS_DISCORD, "Le discord ne respecte pas le bon format"],
            'rank' => [self::REGEX_PLAYERS_RANK, "Le rank ne respecte pas le bon format"],
            'twitch' => [self::REGEX_PLAYERS_TWITCH, "Le lien twitch ne respecte pas le bon format"],
            'fk_team' => [self::REGEX_PLAYERS_FK_TEAM, "Le pk_team ne respecte pas le bon format"]
        ];
        // Prepare the fields to update
        $updates = [];
        $params = [];
        foreach ( $fields as $field => $validation ) {
            if ( array_key_exists($field, $requestBody) ) {
                // If the fields can be null, check if the value is null and add it to the updates
                if ( $field == 'twitch' || $field == 'fk_team' ) {
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
                    case 'username':
                        // Check if the username is already taken
                        if ( $this->getPlayerByUsername($requestBody[$field]) && $requestBody[$field] !== $existingPlayer['username'] ) {
                            HTTPResponses::error(409, "Un joueur avec ce nom d'utilisateur existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'riot_username':
                        // Check if the riot_username is already taken
                        if ( $this->getPlayerByRiotUsername($requestBody[$field]) && $requestBody[$field] !== $existingPlayer['riot_username'] ) {
                            HTTPResponses::error(409, "Un joueur avec ce riot_username existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'discord':
                        // Check if the discord is already taken
                        if ( $this->getPlayerByDiscord($requestBody[$field]) && $requestBody[$field] !== $existingPlayer['discord'] ) {
                            HTTPResponses::error(409, "Un joueur avec ce discord existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'twitch':
                        // Check if the twitch url is already taken
                        if ( $this->getPlayerByTwitchUrl($requestBody[$field]) && $requestBody[$field] !== $existingPlayer['twitch'] ) {
                            HTTPResponses::error(409, "Un joueur avec cet URL Twitch existe déjà");
                        }
                        $updates[] = "$field = ?";
                        $params[] = $requestBody[$field];
                        break;
                    case 'fk_team':
                        // Check if the team exists
                        if ( !$this->getTeamById(intval($requestBody[$field])) ) {
                            HTTPResponses::error(404, "Cette équipe n'existe pas");
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
        // Add the id of the player to update
        $params[] = $pkPlayer;
        // Update the player in the database
        $query = "UPDATE Players SET " . implode(", ", $updates) . " WHERE pk_player = ?";
        $this->wrkDB->execute($query, $params);
        // Get the updated player and send it as a response
        $updatedPlayer = $this->getPlayerById($pkPlayer);
        HTTPResponses::success("Joueur mis à jour avec succès", $updatedPlayer);
    }

    /**
     * Delete a player
     * @param array $requestParams the request parameters
     * @return void nothing to return
     */
    public function delete(array $requestParams): void {
        // Check if the required field is set
        if ( !isset($requestParams['pk_player']) ) {
            HTTPResponses::error(400, "L'identifiant du joueur doit être spécifié pour la suppression");
        }
        $pkPlayer = $requestParams['pk_player'];
        // Validate the field
        $validations = [
            'pk_player' => [self::REGEX_PLAYERS_PK_PLAYER, "L'identifiant du joueur doit être un nombre entier positif"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestParams[$field]) ) {
                HTTPResponses::error(400, $validation[1]);
            }
        }
        // Check if the player exists
        $existingPlayer = $this->getPlayerById($pkPlayer);
        if ( $existingPlayer ) {
            // Delete the player from the database and send the deleted player as a response
            $this->wrkDB->execute(DELETE_PLAYER, [$pkPlayer]);
            HTTPResponses::success("Joueur supprimé avec succès", $existingPlayer);
        } else {
            // Send an error if the player doesn't exist
            HTTPResponses::error(404, "Aucun joueur avec cet identifiant n'a été trouvé");
        }
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
     * Get a team by its id
     * @param int $pkTeam The id of the team
     * @return array|bool the team if it exists, false otherwise
     */
    private function getTeamById(int $pkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$pkTeam]);
    }

}

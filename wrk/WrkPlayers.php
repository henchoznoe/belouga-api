<?php

namespace Wrk;

use HTTP\HTTPResponses;

class WrkPlayers {

    private const REGEX_PLAYERS_USERNAME = '/^[\p{L}\p{N}\p{Pd}\p{Pc}\p{Zs}\'"?!.,;:@&()\/+-]{1,32}$/u';
    private const REGEX_PLAYERS_DISCORD = '/^[\p{L}\p{N}\p{Pd}\p{Pc}\p{Zs}\'"?!.,;:@&()\/+-]{1,32}$/u';
    private const REGEX_PLAYERS_TWITCHURL = '/^https:\/\/(www\.)?twitch\.tv\/[a-zA-Z0-9_]{1,25}$/';
    private const REGEX_PLAYERS_PK_TEAM = "/^\d+$/";

    private WrkDatabase $wrkDB;

    public function __construct() {
        $this->wrkDB = WrkDatabase::getInstance();
    }

    public function create(array $requestBody): void {
        if ( !isset($requestBody['username']) || !isset($requestBody['discord']) ) {
            HTTPResponses::error(400, "Le nom d'utilisateur et le discord doivent être spécifiés");
        }
        $username = $requestBody['username'];
        $discord = $requestBody['discord'];
        $twitchUrl = $requestBody['twitch_url'] ?? null;
        $pkTeam = $requestBody['pk_team'] ?? null;
        $validations = [
            'username' => [self::REGEX_PLAYERS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'discord' => [self::REGEX_PLAYERS_DISCORD, "Le discord ne respecte pas le bon format"],
            'twitch_url' => [self::REGEX_PLAYERS_TWITCHURL, "Le twitch_url ne respecte pas le bon format"],
            'pk_team' => [self::REGEX_PLAYERS_PK_TEAM, "Le pk_team ne respecte pas le bon format"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                if ( $field === 'twitch_url' && $twitchUrl === null ) continue;
                if ( $field === 'pk_team' && $pkTeam === null ) continue;
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingPlayerByUsername = $this->checkPlayerExistenceByUsername($username);
        if ( $existingPlayerByUsername ) HTTPResponses::error(409, "Un joueur avec ce nom d'utilisateur existe déjà");
        $existingPlayerByDiscord = $this->checkPlayerExistenceByDiscord($discord);
        if ( $existingPlayerByDiscord ) HTTPResponses::error(409, "Un joueur avec ce discord existe déjà");
        $existingPlayerByTwitch = $this->checkPlayerExistenceByTwitch($twitchUrl);
        if ( $existingPlayerByTwitch ) HTTPResponses::error(409, "Un joueur avec cet URL Twitch existe déjà");
        if ( $pkTeam !== null ) {
            $existingTeam = $this->checkTeamExistence($pkTeam);
            if ( !$existingTeam ) HTTPResponses::error(404, "L'équipe spécifiée n'existe pas");
        }
        $this->wrkDB->execute(INSERT_PLAYER, [$username, $discord, $twitchUrl, $pkTeam]);
        $addedPlayer = $this->getPlayerById($this->wrkDB->lastInsertId());
        HTTPResponses::success("Joueur créé avec succès", $addedPlayer);
    }

    public function read(): void {
        $players = $this->wrkDB->select(GET_PLAYERS, [], true);
        HTTPResponses::success("Liste des joueurs récupérée", $players);
    }

    public function update(array $requestBody): void {
        if ( !isset($requestBody['pk_player']) ) {
            HTTPResponses::error(400, "L'identifiant du joueur doit être spécifié");
        }
        $pkPlayer = $requestBody['pk_player'];
        $player = $this->getPlayerById($pkPlayer);
        if ( !$player ) HTTPResponses::error(404, "Le joueur spécifié n'existe pas");
        $username = $requestBody['username'] ?? $player['username'];
        $discord = $requestBody['discord'] ?? $player['discord'];
        $twitchUrl = $requestBody['twitch_url'] ?? $player['twitch'];
        $pkTeam = $requestBody['pk_team'] ?? $player['pk_team'];
        $validations = [
            'username' => [self::REGEX_PLAYERS_USERNAME, "Le nom d'utilisateur ne respecte pas le bon format"],
            'discord' => [self::REGEX_PLAYERS_DISCORD, "Le discord ne respecte pas le bon format"],
            'twitch_url' => [self::REGEX_PLAYERS_TWITCHURL, "Le twitch_url ne respecte pas le bon format"],
            'pk_team' => [self::REGEX_PLAYERS_PK_TEAM, "Le pk_team ne respecte pas le bon format"]
        ];
        foreach ( $validations as $field => $validation ) {
            if ( !preg_match($validation[0], $requestBody[$field]) ) {
                if ( $field === 'twitch_url' && $twitchUrl === $player['twitch'] ) continue;
                if ( $field === 'pk_team' && $pkTeam === $player['pk_team'] ) continue;
                HTTPResponses::error(400, $validation[1]);
            }
        }
        $existingPlayerByUsername = $this->checkPlayerExistenceByUsername($username);
        if ( $existingPlayerByUsername && $existingPlayerByUsername['pk_player'] !== $pkPlayer ) HTTPResponses::error(409, "Un joueur avec ce nom d'utilisateur existe déjà");
        $existingPlayerByDiscord = $this->checkPlayerExistenceByDiscord($discord);
        if ( $existingPlayerByDiscord && $existingPlayerByDiscord['pk_player'] !== $pkPlayer ) HTTPResponses::error(409, "Un joueur avec ce discord existe déjà");
        $existingPlayerByTwitch = $this->checkPlayerExistenceByTwitch($twitchUrl);
        if ( $existingPlayerByTwitch && $existingPlayerByTwitch['pk_player'] !== $pkPlayer ) HTTPResponses::error(409, "Un joueur avec cet URL Twitch existe déjà");
        if ( $pkTeam !== null ) {
            $existingTeam = $this->checkTeamExistence($pkTeam);
            if ( !$existingTeam ) HTTPResponses::error(404, "L'équipe spécifiée n'existe pas");
        }
        $this->wrkDB->execute(UPDATE_PLAYER, [$username, $discord, $twitchUrl, $pkTeam, $pkPlayer]);
        $updatedPlayer = $this->getPlayerById($pkPlayer);
        HTTPResponses::success("Joueur mis à jour avec succès", $updatedPlayer);
    }

    public function delete(array $requestParams): void {
        if ( !isset($requestParams['pk_player']) ) {
            HTTPResponses::error(400, "L'identifiant du joueur doit être spécifié");
        }
        $pkPlayer = $requestParams['pk_player'];
        $player = $this->getPlayerById($pkPlayer);
        if ( !$player ) HTTPResponses::error(404, "Le joueur spécifié n'existe pas");
        $this->wrkDB->execute(DELETE_PLAYER, [$pkPlayer]);
        HTTPResponses::success("Joueur supprimé avec succès", $player);
    }

    private function checkPlayerExistenceByUsername(string $username): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_USERNAME, [$username]);
    }

    private function checkPlayerExistenceByDiscord(string $discord): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_DISCORD, [$discord]);
    }

    private function checkPlayerExistenceByTwitch(string $twitchUrl): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_TWITCH, [$twitchUrl]);
    }

    private function checkTeamExistence(int $pkTeam): array|bool {
        return $this->wrkDB->select(GET_TEAM_BY_PK, [$pkTeam]);
    }

    private function getPlayerById(int $pkPlayer): array|bool {
        return $this->wrkDB->select(GET_PLAYER_BY_PK, [$pkPlayer]);
    }

}

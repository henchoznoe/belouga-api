<?php

const GET_PLAYERS = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team;
SQL;

const INSERT_PLAYER = <<<SQL
    INSERT INTO Players (username, riot_username, discord, twitch, `rank`, fk_team)
    VALUES (?, ?, ?, ?, ?, ?);
SQL;

const GET_PLAYER_BY_PK = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        p.pk_player = ?;
SQL;

const GET_PLAYER_BY_USERNAME = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        username = ?;
SQL;

const GET_PLAYER_BY_DISCORD = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        discord = ?;
SQL;

const GET_PLAYER_BY_TWITCH = <<<SQL
    SELECT 
       p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        twitch = ?;
SQL;

const GET_PLAYER_BY_RIOT_USERNAME = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`,
        p.fk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        riot_username = ?;
SQL;

const DELETE_PLAYER = <<<SQL
    DELETE FROM Players
    WHERE
        pk_player = ?;
SQL;



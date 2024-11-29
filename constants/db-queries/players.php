<?php

const GET_PLAYERS = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.discord,
        p.twitch,
        t.pk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team;
SQL;

const INSERT_PLAYER = <<<SQL
    INSERT INTO Players (username, discord, twitch, fk_team)
    VALUES (?, ?, ?, ?);
SQL;

const GET_PLAYER_BY_USERNAME = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.discord,
        p.twitch,
        t.pk_team,
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
        p.discord,
        p.twitch,
        t.pk_team,
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
        p.discord,
        p.twitch,
        t.pk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        twitch = ?;
SQL;

const UPDATE_PLAYER = <<<SQL
    UPDATE Players
    SET 
        username = ?,
        discord = ?,
        twitch = ?,
        fk_team = ?
    WHERE 
        pk_player = ?;
SQL;

const DELETE_PLAYER = <<<SQL
    DELETE FROM Players
    WHERE
        pk_player = ?;
SQL;

const GET_TEAM_BY_PK = <<<SQL
    SELECT 
        pk_team,
        name
    FROM
        Teams
    WHERE
        pk_team = ?;
SQL;

const GET_PLAYER_BY_PK = <<<SQL
    SELECT 
        p.pk_player, 
        p.username, 
        p.discord,
        p.twitch,
        t.pk_team,
        t.name AS team_name
    FROM
        Players p
    LEFT JOIN
        Teams t ON p.fk_team = t.pk_team
    WHERE
        p.pk_player = ?;
SQL;

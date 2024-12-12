<?php

const GET_TEAMS = <<<SQL
    SELECT
        pk_team,
        name,
        capacity,
        (SELECT COUNT(*) FROM Players WHERE fk_team = pk_team) AS player_count
    FROM
        Teams;
SQL;

const GET_TEAM_BY_PK = <<<SQL
    SELECT 
        pk_team,
        name,
        capacity,
        (SELECT COUNT(*) FROM Players WHERE fk_team = pk_team) AS player_count
    FROM
        Teams
    WHERE
        pk_team = ?;
SQL;

const GET_TEAM_BY_NAME = <<<SQL
    SELECT
        pk_team,
        name,
        capacity,
        (SELECT COUNT(*) FROM Players WHERE fk_team = pk_team) AS player_count
    FROM
        Teams
    WHERE
        name = ?;
SQL;

const INSERT_TEAM = <<<SQL
    INSERT INTO
        Teams (name, capacity)
    VALUES
        (?, ?);
SQL;

const DELETE_TEAM = <<<SQL
    DELETE FROM
        Teams
    WHERE
        pk_team = ?;
SQL;

const GET_PLAYERS_COUNT_BY_TEAM = <<<SQL
    SELECT
        COUNT(*) AS count
    FROM
        Players
    WHERE
        fk_team = ?;
SQL;

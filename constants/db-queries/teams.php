<?php

const GET_TEAMS = <<<SQL
    SELECT
        pk_team,
        name,
        size,
        (SELECT COUNT(*) FROM Players WHERE fk_team = pk_team) AS player_count
    FROM
        Teams;
SQL;

const GET_TEAM_BY_NAME = <<<SQL
    SELECT
        pk_team,
        name,
        size,
        (SELECT COUNT(*) FROM Players WHERE fk_team = pk_team) AS player_count
    FROM
        Teams
    WHERE
        name = ?;
SQL;

const INSERT_TEAM = <<<SQL
    INSERT INTO
        Teams (name, size)
    VALUES
        (?, ?);
SQL;

const UPDATE_TEAM = <<<SQL
    UPDATE
        Teams
    SET
        name = ?,
        size = ?
    WHERE
        pk_team = ?;
SQL;

const DELETE_TEAM = <<<SQL
    DELETE FROM
        Teams
    WHERE
        pk_team = ?;
SQL;

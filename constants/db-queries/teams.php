<?php

const GET_TEAMS = <<<SQL
    SELECT
        pk_team,
        name
    FROM
        Teams;
SQL;

const GET_TEAM_BY_NAME = <<<SQL
    SELECT
        pk_team,
        name
    FROM
        Teams
    WHERE
        name = ?;
SQL;

const INSERT_TEAM = <<<SQL
    INSERT INTO
        Teams (name)
    VALUES
        (?);
SQL;

const UPDATE_TEAM = <<<SQL
    UPDATE
        Teams
    SET
        name = ?
    WHERE
        pk_team = ?;
SQL;

const DELETE_TEAM = <<<SQL
    DELETE FROM
        Teams
    WHERE
        pk_team = ?;
SQL;

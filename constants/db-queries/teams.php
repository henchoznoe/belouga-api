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

const GET_MATCHES_BY_TEAM = <<<SQL
    SELECT 
        m.pk_match,
        m.fk_team_one,
        t1.name AS team_one_name,
        m.team_one_score AS score_team_one,
        m.fk_team_two,
        t2.name AS team_two_name,
        m.team_two_score AS score_team_two,
        m.fk_round,
        r.label AS round_label,
        m.match_date,
        m.winner_team
    FROM
        Matches m
    LEFT JOIN
        Teams t1 ON m.fk_team_one = t1.pk_team
    LEFT JOIN
        Teams t2 ON m.fk_team_two = t2.pk_team
    LEFT JOIN
        Rounds r ON m.fk_round = r.pk_round
    WHERE
        m.fk_team_one = ? OR m.fk_team_two = ?;
SQL;

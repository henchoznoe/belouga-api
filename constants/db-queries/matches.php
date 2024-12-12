<?php

const GET_MATCHES = <<<SQL
    SELECT 
        m.pk_match,
        m.fk_team_one AS pk_team_one,
        t1.name AS team_one_name,
        m.team_one_score AS score_team_one,
        m.fk_team_two AS pk_team_two,
        t2.name AS team_two_name,
        m.team_two_score AS score_team_two,
        m.fk_round AS pk_round,
        r.label AS round_label,
        m.match_date,
        m.winner_team AS pk_winner
    FROM
        Matches m
    LEFT JOIN
        Teams t1 ON m.fk_team_one = t1.pk_team
    LEFT JOIN
        Teams t2 ON m.fk_team_two = t2.pk_team
    LEFT JOIN
        Rounds r ON m.fk_round = r.pk_round;
SQL;

const GET_MATCHES_BY_TEAM = <<<SQL
    SELECT 
        m.pk_match,
        m.fk_team_one AS pk_team_one,
        t1.name AS team_one_name,
        m.team_one_score AS score_team_one,
        m.fk_team_two AS pk_team_two,
        t2.name AS team_two_name,
        m.team_two_score AS score_team_two,
        m.fk_round AS pk_round,
        r.label AS round_label,
        m.match_date,
        m.winner_team AS pk_winner
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

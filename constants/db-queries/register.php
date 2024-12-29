<?php

const GET_TEAMS_WITH_PLAYERS = <<<SQL
    (
    SELECT 
        t.pk_team AS fk_team,
        t.name,
        p.pk_player,
        p.username,
        p.riot_username,
        p.discord,
        p.twitch,
        p.rank
    FROM 
        Teams t
    LEFT JOIN 
        Players p ON t.pk_team = p.fk_team
)
UNION
(
    SELECT 
        NULL AS fk_team,
        NULL AS name,
        p.pk_player,
        p.username,
        p.riot_username,
        p.discord,
        p.twitch,
        p.rank
    FROM 
        Players p
    WHERE 
        p.fk_team IS NULL
)
ORDER BY 
    name, username;
SQL;

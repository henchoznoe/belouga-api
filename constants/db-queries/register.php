<?php

const GET_TEAMS_WITH_PLAYERS = <<<SQL
    SELECT 
        t.pk_team,
        t.name,
        p.pk_player,
        p.username,
        p.riot_username,
        p.discord,
        p.twitch,
        p.`rank`
    FROM 
        Teams t
    RIGHT JOIN 
        Players p ON t.pk_team = p.fk_team
    ORDER BY 
        t.name, p.username;
SQL;

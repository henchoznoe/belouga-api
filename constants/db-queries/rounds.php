<?php

const GET_ROUNDS = <<<SQL
    SELECT
        pk_round,
        label
    FROM
        Rounds;
SQL;

const GET_ROUND_BY_PK = <<<SQL
    SELECT
        pk_round,
        label
    FROM
        Rounds
    WHERE
        pk_round = ?;
SQL;

<?php

const GET_ROUNDS = <<<SQL
    SELECT
        pk_round,
        label
    FROM
        Rounds;
SQL;

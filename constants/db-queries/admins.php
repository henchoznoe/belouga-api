<?php

const GET_ADMINS = <<<SQL
    SELECT 
        a.pk_admin, 
        a.username, 
        a.fk_admin_type,
        at.label
    FROM 
        Admins a
    JOIN 
        AdminTypes at ON a.fk_admin_type = at.pk_admin_type;
SQL;

const INSERT_ADMIN = <<<SQL
    INSERT INTO Admins (username, password, fk_admin_type)
    VALUES (?, ?, ?);
SQL;

const GET_ADMIN_BY_PK = <<<SQL
    SELECT 
        a.pk_admin, 
        a.username, 
        a.fk_admin_type,
        at.label
    FROM 
        Admins a
    JOIN 
        AdminTypes at ON a.fk_admin_type = at.pk_admin_type
    WHERE 
        a.pk_admin = ?;
SQL;

const GET_ADMIN_BY_USERNAME = <<<SQL
    SELECT 
        a.pk_admin, 
        a.username, 
        a.password, 
        at.permission
    FROM 
        Admins a
    JOIN 
        AdminTypes at ON a.fk_admin_type = at.pk_admin_type
    WHERE 
        a.username = ?;
SQL;

const GET_ADMIN_TYPES = <<<SQL
    SELECT 
        pk_admin_type, 
        label
    FROM 
        AdminTypes;
SQL;

const GET_ADMIN_TYPE_BY_PK = <<<SQL
    SELECT 
        pk_admin_type, 
        label
    FROM 
        AdminTypes
    WHERE 
        pk_admin_type = ?;
SQL;

const DELETE_ADMIN = <<<SQL
    DELETE FROM Admins
    WHERE 
        pk_admin = ?;
SQL;

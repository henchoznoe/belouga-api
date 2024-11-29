<?php

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

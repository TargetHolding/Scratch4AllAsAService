<?php

// When there is no previous synchronisation, only completly new users (and items when availible) will be added.
if ($firstSync) {
    foreach ($db->query('SELECT MU.* FROM MONK_USERS MU WHERE MU.MONK_ID NOT IN (SELECT MONK_ID FROM USERS)') as $monkUserRec) {
        $db->stmnt('INSERT INTO USERS (MONK_ID, PASSWORD, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DISABLED, DELETED) VALUES (\'' . $monkUserRec['MONK_ID'] . '\', \'' . $monkUserRec['PASSWORD'] . '\', ' . $monkUserRec['PERMISSIONS'] . ', 1, NOW(), NOW(), \'' . $monkUserRec['DISABLED'] . '\', \'no\')');
        $iId = mysql_insert_id();
        foreach ($db->query('SELECT * FROM MONK_USERBOOK WHERE MONK_ID = \'' . $monkUserRec['MONK_ID'] . '\'') as $b)
            $db->stmnt('INSERT INTO USERBOOK(USER_ID, BOOK_ID, PERMISSIONS, PAGE_FROM, PAGE_TO, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $iId . ', \'' . $b['BOOK_ID'] . '\', ' . $b['PERMISSIONS'] . ', ' . $b['PAGE_FROM'] . ', ' . $b['PAGE_TO'] . ', 1, NOW(), NOW(), \'no\')');
        foreach ($db->query('SELECT * FROM MONK_USERCOL WHERE MONK_ID = \'' . $monkUserRec['MONK_ID'] . '\'') as $c)
            $db->stmnt('INSERT INTO USERCOL(USER_ID, COLLECTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $iId . ', \'' . $c['COLLECTION_ID'] . '\', ' . $c['PERMISSIONS'] . ', 1, NOW(), NOW(), \'no\')');
        foreach ($db->query('SELECT * FROM MONK_USERINST WHERE MONK_ID = \'' . $monkUserRec['MONK_ID'] . '\'') as $i)
            $db->stmnt('INSERT INTO USERINST(USER_ID, INSTITUTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $iId . ', \'' . $i['INSTITUTION_ID'] . '\', ' . $i['PERMISSIONS'] . ', 1, NOW(), NOW(), \'no\')');
    }

// Normal synchronisation.
} else {

    // Get results for new and chanced user records including items. (record into $mcui).
    foreach ($db->query('
        SELECT
          CASE WHEN A.MU_MONK_ID = \'\' THEN NULL ELSE A.MU_MONK_ID END AS MU_MONK_ID 
          ,CASE WHEN A.MU_PASSWORD = \'\' THEN NULL ELSE A.MU_PASSWORD END AS MU_PASSWORD 
          ,CASE WHEN A.MU_PERMISSIONS = \'\' THEN NULL ELSE A.MU_PERMISSIONS END AS MU_PERMISSIONS 
          ,CASE WHEN A.MU_DISABLED = \'\' THEN NULL ELSE A.MU_DISABLED END AS MU_DISABLED 
          ,CASE WHEN A.MUB_MONK_ID = \'\' THEN NULL ELSE A.MUB_MONK_ID END AS MUB_MONK_ID 
          ,CASE WHEN A.MUB_BOOK_ID = \'\' THEN NULL ELSE A.MUB_BOOK_ID END AS MUB_BOOK_ID 
          ,CASE WHEN A.MUB_PERMISSIONS = \'\' THEN NULL ELSE A.MUB_PERMISSIONS END AS MUB_PERMISSIONS 
          ,CASE WHEN A.MUB_PAGE_FROM = \'\' THEN NULL ELSE A.MUB_PAGE_FROM END AS MUB_PAGE_FROM 
          ,CASE WHEN A.MUB_PAGE_TO = \'\' THEN NULL ELSE A.MUB_PAGE_TO END AS MUB_PAGE_TO 
          ,CASE WHEN A.MUC_MONK_ID = \'\' THEN NULL ELSE A.MUC_MONK_ID END AS MUC_MONK_ID 
          ,CASE WHEN A.MUC_COLLECTION_ID = \'\' THEN NULL ELSE A.MUC_COLLECTION_ID END AS MUC_COLLECTION_ID 
          ,CASE WHEN A.MUC_PERMISSIONS = \'\' THEN NULL ELSE A.MUC_PERMISSIONS END AS MUC_PERMISSIONS 
          ,CASE WHEN A.MUI_MONK_ID = \'\' THEN NULL ELSE A.MUI_MONK_ID END AS MUI_MONK_ID 
          ,CASE WHEN A.MUI_INSTITUTION_ID = \'\' THEN NULL ELSE A.MUI_INSTITUTION_ID END AS MUI_INSTITUTION_ID 
          ,CASE WHEN A.MUI_PERMISSIONS = \'\' THEN NULL ELSE A.MUI_PERMISSIONS END AS MUI_PERMISSIONS 
        FROM 
          (
          SELECT 
            CASE WHEN MU.MONK_ID IS NULL THEN \'\' ELSE MU.MONK_ID END AS MU_MONK_ID 
            ,CASE WHEN MU.PASSWORD IS NULL THEN \'\' ELSE MU.PASSWORD END AS MU_PASSWORD 
            ,CASE WHEN MU.PERMISSIONS IS NULL THEN \'\' ELSE MU.PERMISSIONS END AS MU_PERMISSIONS 
            ,CASE WHEN MU.DISABLED IS NULL THEN \'\' ELSE MU.DISABLED END AS MU_DISABLED 
            ,CASE WHEN MUB.MONK_ID IS NULL THEN \'\' ELSE MUB.MONK_ID END AS MUB_MONK_ID 
            ,CASE WHEN MUB.BOOK_ID IS NULL THEN \'\' ELSE MUB.BOOK_ID END AS MUB_BOOK_ID 
            ,CASE WHEN MUB.PERMISSIONS IS NULL THEN \'\' ELSE MUB.PERMISSIONS END AS MUB_PERMISSIONS 
            ,CASE WHEN MUB.PAGE_FROM IS NULL THEN \'\' ELSE MUB.PAGE_FROM END AS MUB_PAGE_FROM 
            ,CASE WHEN MUB.PAGE_TO IS NULL THEN \'\' ELSE MUB.PAGE_TO END AS MUB_PAGE_TO 
            ,CASE WHEN MUC.MONK_ID IS NULL THEN \'\' ELSE MUC.MONK_ID END AS MUC_MONK_ID 
            ,CASE WHEN MUC.COLLECTION_ID IS NULL THEN \'\' ELSE MUC.COLLECTION_ID END AS MUC_COLLECTION_ID 
            ,CASE WHEN MUC.PERMISSIONS IS NULL THEN \'\' ELSE MUC.PERMISSIONS END AS MUC_PERMISSIONS 
            ,CASE WHEN MUI.MONK_ID IS NULL THEN \'\' ELSE MUI.MONK_ID END AS MUI_MONK_ID 
            ,CASE WHEN MUI.INSTITUTION_ID IS NULL THEN \'\' ELSE MUI.INSTITUTION_ID END AS MUI_INSTITUTION_ID 
            ,CASE WHEN MUI.PERMISSIONS IS NULL THEN \'\' ELSE MUI.PERMISSIONS END AS MUI_PERMISSIONS 
          FROM 
            MONK_USERS MU 
            LEFT JOIN MONK_USERBOOK MUB ON MU.MONK_ID = MUB.MONK_ID AND MUB.TIMESTAMP_ID = MU.TIMESTAMP_ID 
            LEFT JOIN MONK_USERCOL MUC ON MU.MONK_ID = MUC.MONK_ID AND MUC.TIMESTAMP_ID = MU.TIMESTAMP_ID 
            LEFT JOIN MONK_USERINST MUI ON MU.MONK_ID = MUI.MONK_ID AND MUI.TIMESTAMP_ID = MU.TIMESTAMP_ID
          WHERE
            MU.TIMESTAMP_ID = ' . $monkActualTsId . '
          )A
          LEFT JOIN 
          (
          SELECT
            CASE WHEN MU.MONK_ID IS NULL THEN \'\' ELSE MU.MONK_ID END AS MU_MONK_ID 
            ,CASE WHEN MU.PASSWORD IS NULL THEN \'\' ELSE MU.PASSWORD END AS MU_PASSWORD 
            ,CASE WHEN MU.PERMISSIONS IS NULL THEN \'\' ELSE MU.PERMISSIONS END AS MU_PERMISSIONS 
            ,CASE WHEN MU.DISABLED IS NULL THEN \'\' ELSE MU.DISABLED END AS MU_DISABLED 
            ,CASE WHEN MUB.MONK_ID IS NULL THEN \'\' ELSE MUB.MONK_ID END AS MUB_MONK_ID 
            ,CASE WHEN MUB.BOOK_ID IS NULL THEN \'\' ELSE MUB.BOOK_ID END AS MUB_BOOK_ID 
            ,CASE WHEN MUB.PERMISSIONS IS NULL THEN \'\' ELSE MUB.PERMISSIONS END AS MUB_PERMISSIONS 
            ,CASE WHEN MUB.PAGE_FROM IS NULL THEN \'\' ELSE MUB.PAGE_FROM END AS MUB_PAGE_FROM 
            ,CASE WHEN MUB.PAGE_TO IS NULL THEN \'\' ELSE MUB.PAGE_TO END AS MUB_PAGE_TO 
            ,CASE WHEN MUC.MONK_ID IS NULL THEN \'\' ELSE MUC.MONK_ID END AS MUC_MONK_ID 
            ,CASE WHEN MUC.COLLECTION_ID IS NULL THEN \'\' ELSE MUC.COLLECTION_ID END AS MUC_COLLECTION_ID 
            ,CASE WHEN MUC.PERMISSIONS IS NULL THEN \'\' ELSE MUC.PERMISSIONS END AS MUC_PERMISSIONS 
            ,CASE WHEN MUI.MONK_ID IS NULL THEN \'\' ELSE MUI.MONK_ID END AS MUI_MONK_ID 
            ,CASE WHEN MUI.INSTITUTION_ID IS NULL THEN \'\' ELSE MUI.INSTITUTION_ID END AS MUI_INSTITUTION_ID 
            ,CASE WHEN MUI.PERMISSIONS IS NULL THEN \'\' ELSE MUI.PERMISSIONS END AS MUI_PERMISSIONS 
          FROM
            MONK_USERS MU 
            LEFT JOIN MONK_USERBOOK MUB ON MU.MONK_ID = MUB.MONK_ID AND MUB.TIMESTAMP_ID = MU.TIMESTAMP_ID 
            LEFT JOIN MONK_USERCOL MUC ON MU.MONK_ID = MUC.MONK_ID AND MUC.TIMESTAMP_ID = MU.TIMESTAMP_ID 
            LEFT JOIN MONK_USERINST MUI ON MU.MONK_ID = MUI.MONK_ID AND MUI.TIMESTAMP_ID = MU.TIMESTAMP_ID 
          WHERE 
            MU.TIMESTAMP_ID = ' . $monkPreviousTsId . '
          )P
          ON A.MU_MONK_ID = P.MU_MONK_ID 
          AND A.MU_PASSWORD = P.MU_PASSWORD 
          AND A.MU_PERMISSIONS = P.MU_PERMISSIONS 
          AND A.MU_DISABLED = P.MU_DISABLED 
          AND A.MUB_MONK_ID = P.MUB_MONK_ID 
          AND A.MUB_BOOK_ID = P.MUB_BOOK_ID 
          AND A.MUB_PERMISSIONS = P.MUB_PERMISSIONS 
          AND A.MUB_PAGE_FROM = P.MUB_PAGE_FROM 
          AND A.MUB_PAGE_TO = P.MUB_PAGE_TO 
          AND A.MUC_MONK_ID = P.MUC_MONK_ID 
          AND A.MUC_COLLECTION_ID = P.MUC_COLLECTION_ID 
          AND A.MUC_PERMISSIONS = P.MUC_PERMISSIONS 
          AND A.MUI_MONK_ID = P.MUI_MONK_ID 
          AND A.MUI_INSTITUTION_ID = P.MUI_INSTITUTION_ID 
          AND A.MUI_PERMISSIONS = P.MUI_PERMISSIONS 
        WHERE
          P.MU_MONK_ID IS NULL
          ') as $mcui) {
        // Insert or update USERS table.
        $db->stmnt('INSERT INTO USERS (MONK_ID, PASSWORD, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DISABLED, DELETED) VALUES (\'' . $mcui['MU_MONK_ID'] . '\', \'' . $mcui['MU_PASSWORD'] . '\', ' . $mcui['MU_PERMISSIONS'] . ', 1, NOW(), NOW(), \'' . $mcui['MU_DISABLED'] . '\', \'no\') ON DUPLICATE KEY UPDATE PASSWORD = \'' . $mcui['MU_PASSWORD'] . '\', PERMISSIONS = ' . $mcui['MU_PERMISSIONS'] . ', BYUSER_ID = 1, TIMESTAMP_CHANGE = NOW(), DISABLED = \'' . $mcui['MU_DISABLED'] . '\', DELETED = \'no\'');
        // Insert or update USERBOOK table.
        if ($mcui['MUB_MONK_ID'] != NULL)
            $db->stmnt('INSERT INTO USERBOOK (USER_ID, BOOK_ID, PERMISSIONS, PAGE_FROM, PAGE_TO, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES ((SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MU_MONK_ID'] . '\'), ' . $mcui['MUB_BOOK_ID'] . ', ' . $mcui['MUB_PERMISSIONS'] . ', ' . $mcui['MUB_PAGE_FROM'] . ', ' . $mcui['MUB_PAGE_TO'] . ', 1, NOW(), NOW(), \'no\') ON DUPLICATE KEY UPDATE USER_ID = (SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MUB_MONK_ID'] . '\'), BOOK_ID =' . $mcui['MUB_BOOK_ID'] . ', PERMISSIONS =' . $mcui['MUB_PERMISSIONS'] . ', PAGE_FROM = ' . $mcui['MUB_PAGE_FROM'] . ', PAGE_TO = ' . $mcui['MUB_PAGE_TO'] . ', BYUSER_ID = 1, TIMESTAMP_CHANGE = NOW(), DELETED = \'no\'');
        // Insert or update USERCOL table.
        if ($mcui['MUC_MONK_ID'] != NULL)
            $db->stmnt('INSERT INTO USERCOL (USER_ID, COLLECTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES ((SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MU_MONK_ID'] . '\'), ' . $mcui['MUC_COLLECTION_ID'] . ', ' . $mcui['MUC_PERMISSIONS'] . ', 1, NOW(), NOW(), \'no\') ON DUPLICATE KEY UPDATE USER_ID = (SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MUC_MONK_ID'] . '\'), COLLECTION_ID =' . $mcui['MUC_COLLECTION_ID'] . ', PERMISSIONS =' . $mcui['MUC_PERMISSIONS'] . ', BYUSER_ID = 1, TIMESTAMP_CHANGE = NOW(), DELETED = \'no\'');
        // Insert or update USERINST table.
        if ($mcui['MUI_MONK_ID'] != NULL)
            $db->stmnt('INSERT INTO USERINST (USER_ID, INSTITUTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES ((SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MU_MONK_ID'] . '\'), ' . $mcui['MUI_INSTITUTION_ID'] . ', ' . $mcui['MUI_PERMISSIONS'] . ', 1, NOW(), NOW(), \'no\') ON DUPLICATE KEY UPDATE USER_ID = (SELECT ID FROM USERS WHERE MONK_ID = \'' . $mcui['MUI_MONK_ID'] . '\'), INSTITUTION_ID =' . $mcui['MUI_INSTITUTION_ID'] . ', PERMISSIONS =' . $mcui['MUI_PERMISSIONS'] . ', BYUSER_ID = 1, TIMESTAMP_CHANGE = NOW(), DELETED = \'no\'');
    }

    // Deleted user(s)
    foreach ($db->query('SELECT P.MONK_ID FROM (SELECT MONK_ID FROM MONK_USERS WHERE TIMESTAMP_ID = ' . $monkPreviousTsId . ')P LEFT JOIN (SELECT MONK_ID FROM MONK_USERS WHERE TIMESTAMP_ID = ' . $monkActualTsId . ')A ON P.MONK_ID = A.MONK_ID WHERE A.MONK_ID IS NULL GROUP BY P.MONK_ID') as $md)
        @$db->stmnt('UPDATE USERS SET DELETED = \'yes\' WHERE MONK_ID = \'' . $md['MONK_ID'] . '\'');
    // Deleted book(s)
    foreach ($db->query('SELECT P.BOOK_ID FROM (SELECT BOOK_ID FROM MONK_USERBOOK WHERE TIMESTAMP_ID = ' . $monkPreviousTsId . ')P LEFT JOIN (SELECT BOOK_ID FROM MONK_USERBOOK WHERE TIMESTAMP_ID = ' . $monkActualTsId . ')A ON P.BOOK_ID = A.BOOK_ID WHERE A.BOOK_ID IS NULL GROUP BY P.BOOK_ID') as $md)
        @$db->stmnt('DELETE FROM USERBOOK WHERE BOOK_ID = ' . $md['BOOK_ID']);
    // Deleted collection(s)
    foreach ($db->query('SELECT P.COLLECTION_ID FROM (SELECT COLLECTION_ID FROM MONK_USERCOL WHERE TIMESTAMP_ID = ' . $monkPreviousTsId . ')P LEFT JOIN (SELECT COLLECTION_ID FROM MONK_USERCOL WHERE TIMESTAMP_ID = ' . $monkActualTsId . ')A ON P.COLLECTION_ID = A.COLLECTION_ID WHERE A.COLLECTION_ID IS NULL GROUP BY P.COLLECTION_ID') as $md)
        @$db->stmnt('DELETE FROM USERCOL WHERE COLLECTION_ID = ' . $md['COLLECTION_ID']);
    // Deleted institution(s)
    foreach ($db->query('SELECT P.INSTITUTION_ID FROM (SELECT INSTITUTION_ID FROM MONK_USERINST WHERE TIMESTAMP_ID = ' . $monkPreviousTsId . ')P LEFT JOIN (SELECT INSTITUTION_ID FROM MONK_USERINST WHERE TIMESTAMP_ID = ' . $monkActualTsId . ')A ON P.INSTITUTION_ID = A.INSTITUTION_ID WHERE A.INSTITUTION_ID IS NULL GROUP BY P.INSTITUTION_ID') as $md)
        @$db->stmnt('DELETE FROM USERINST WHERE INSTITUTION_ID = ' . $md['INSTITUTION_ID']);
}
?>
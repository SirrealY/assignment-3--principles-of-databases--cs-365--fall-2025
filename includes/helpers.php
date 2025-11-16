<?php

/**
 * This file will contain helper functions that are used in the project
 * to update, delete, insert, and search the database.
 */

function dbTestConnection() {
    try {
        include_once 'config.php';

        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4",
            DBUSER,
            DBPASS
        );
        //If we reach this point connection was successful
        return true;

    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo "<p class='highlight'>Oh no! The Function <code>dbTestConnection</code> has failed to execute.</p?";
        echo "<pre>$error</pre>";
        echo "<p> class= 'highlight'> Exiting...</p>";
        exit;

    }
}

function searchCredentials() {
    try {
        include_once 'config.php';

        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4",
            DBUSER,
            DBPASS
        );

        $like = '%' . $term . '%';

        $sql = '
            SELECT
                c.credential_id,
                u.username,
                u.email,
                w.name AS site_name,
                c.site_username,
                c.url,
                c.comment,
                c.created_at
            FROM credentials AS c
            JOIN users u     ON c.user_id = u.user_id
            JOIN websites w  ON c.website_id = w.website_id
            WHERE u.username LIKE :term
                OR u.email LIKE :term
                OR w.name LIKE :term
                OR c.url LIKE :term
                OR c.site_username LIKE :term
                OR c.comment LIKE :term
            ORDER BY c.created_at DESC
        ';

        $statement = $db->prepare($sql);
        $statement->execute([':term' => $like]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = null; 

        return $rows;

    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo "<p class='highlight'>Oh no! The Function <code>searchCredentials</code> has failed to execute.</p?";
        echo "<pre>$error</pre>";
        echo "<p> class= 'highlight'> Exiting...</p>";
        exit;

    }
}

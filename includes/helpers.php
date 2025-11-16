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

function searchCredentials($term) {
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
        $statement->execute(['term' => $like]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = null;

        return $rows;

    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo "<p class='highlight'>Oh no! The Function <code>searchCredentials</code> has failed to execute.</p?";
        echo "<pre>$error</pre>";
        echo "<p> class='highlight'> Exiting...</p>";
        exit;

    }
}

function insertEntry($siteName, $url, $email, $username, $password, $comment) {
    try {
        include_once 'config.php';

        $db = new PDO (
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4",
            DBUSER,
            DBPASS
        );

        $userId = null;

        $sqlUser = 'SELECT user_id FROM users WHERE email = :email';
        $statement = $db->prepare($sqlUser);
        $statement->execute(['email' => $email]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement = null;

        if ($row) {
            // User exists, get user_id and reuse it
            $userId = (int)$row['user_id'];
        } else {
            $insertUser = "
                INSERT INTO users (username, first_name, last_name, email)
                VALUES (:username, '', '', :email)
            ";
            $statement = $db->prepare($insertUser);
            $statement->execute([
                'username' => $username,
                'email' => $email
            ]);
            $userId = (int)$db->lastInsertId();
            $statement = null;
        }

        //
        $websiteId = null;
        $sqlWebsite = 'SELECT website_id FROM websites WHERE url = :url';
        $statement = $db->prepare($sqlWebsite);
        $statement->execute([':url' => $url]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement = null;

        if($row) {
            $websiteId = (int)$row['website_id'];
        } else {
            $insertSite = "
                INSERT INTO websites (url, name)
                VALUES (:url, :name)
            ";
            $statement = $db->prepare($insertSite);
            $statement->execute([
                ':url' => $url,
                ':name' => $siteName,
            ]);
            $websiteId = (int)$db->lastInsertId();
            $statement = null;
        }

        $insertCred = "
            INSERT INTO credentials (
                user_id, website_id, site_username, url, passwords_enc, comment
            )
            VALUES (
                :user_id, :website_id, :site_username, :url, AES_ENCRYPT(:password, UNHEX(SHA2('SEUZ', 512))), :comment
            )
        ";

        $statement = $db->prepare($insertCred);
        $statement->execute([
            'user_id' => $userId,
            'website_id' => $websiteId,
            'site_username' => $username,
            'url' => $url,
            'password' => $password,
            'comment' => $comment
         ]);
         $statement = null;
         return true;

    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo "<p class='highlight'>Oh no! The Function <code>insertEntry</code> has failed to execute.</p?";
        echo "<pre>$error</pre>";
        echo "<p> class='highlight'> Exiting...</p>";
        exit;
    }
}

function updateEntry($siteNamePattern, $newUrl) {
    try {
        include_once 'config.php';

        $db = new PDO (
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4",
            DBUSER,
            DBPASS
        );
        $like = '%' . $siteNamePattern . '%';

        $updateWebsites = "
            UPDATE websites
            SET url = :new_url
            WHERE name LIKE :site_name_pattern
        ";

        $statement = $db->prepare($updateWebsites);
        $statement->execute([
            ':new_url' => $newUrl,
            ':site_name_pattern' => $like
        ]);
        $rowsAffected = $statement->rowCount();
        $statement = null;

        if ($rowsAffected === 0) {
            return false;
        }

        $updateCredentials = "
            UPDATE credentials AS c
            JOIN websites AS w ON c.website_id = w.website_id
            SET c.url = :new_url
            WHERE w.name LIKE :site_name
        ";

        $statement = $db->prepare($updateCredentials);
        $statement->execute([
            'site_name' => $like,

        ]);
        $statement = null;

        return true;

    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo "<p class='highlight'>Oh no! The Function <code>updateEntry</code> has failed to execute.</p?";
        echo "<pre>$error</pre>";
        echo "<p> class= 'highlight'> Exiting...</p>";
        exit;
    }
}

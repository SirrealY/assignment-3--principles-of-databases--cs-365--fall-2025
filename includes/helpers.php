<?php

\**
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

        //If we reach this point connection was succesful
        return true;
    } catch (PDOException $error) {
        //If we reach this point connection was unsuccessful
        echo ""

    }
}

<?php

/** ***************************************
 *  Driver Specific script
 * ****************************************
 * @file   : ajax/driver.class.php
 * @author : Vineeth N K(me@vineethkrisknan.in)
 * 
 * Description:
 *  DO NOT CHANGE ANYTHING HERE
 */

class Driver {

    public static function loadDriver($dbtype, $dbhost, $dbname, $dbpath) {
        switch ($dbtype) {
            case "mysql":
                $dsn = "mysql:host=$dbhost;dbname=$dbname";
                break;

            case "sqlite":
                $dsn = "sqlite:$dbpath";
                break;

            case "postgresql":
                $db = "pgsql:host=$dbhost dbname=$dbname";
                break;
        }
        return $dsn;
    }

}

?>

<?php
/**
 * (c) Marcus Schwarz
 * Date: 15.03.14
 * Time: 14:45
 * Id:   $Id$
 *
 * Powered by Alpen Yod'l Koffein
 */


/**
 * @return \PDO
 */
function getDatabase() {
    if (isset($GLOBALS['netmrg']['__pdoconn'])) {
        return $GLOBALS['netmrg']['__pdoconn'];
    }
    return null;
}

/**
 *
 */
function initDatabaseConnection() {
    if ($GLOBALS["netmrg"]["dbsock"] != "") {
        $dbhost = $GLOBALS["netmrg"]["dbhost"].":".$GLOBALS["netmrg"]["dbsock"];
    }
    elseif ($GLOBALS["netmrg"]["dbport"] > 0) {
        $dbhost = $GLOBALS["netmrg"]["dbhost"].":".$GLOBALS["netmrg"]["dbport"];
    }
    else {
        $dbhost = $GLOBALS["netmrg"]["dbhost"];
    }

    $dsn      = 'mysql:dbname='.$GLOBALS["netmrg"]["dbname"].';host='.$dbhost;
    $user     = $GLOBALS["netmrg"]["dbuser"];
    $password = $GLOBALS["netmrg"]["dbpass"];

    $GLOBALS['netmrg']['__pdoconn'] = new PDO($dsn, $user, $password);
}